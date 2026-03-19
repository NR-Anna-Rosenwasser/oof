<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class OofController extends Controller
{
    /**
     * Selector OOF.
     */
    public function viewSelector()
    {
        return Inertia::render("Oofs/Selector");
    }

    /**
     * Handle the OOF selector form submission.
     */
    public function handleSelector(Request $request)
    {
        $validated = $request->validate([
            "file" => "required|file|mimes:csv",
            "numberOfRows" => "required|integer|min:1",
        ]);
        // get the file from the request and open it for reading
        $file = fopen($validated["file"], "r");

        // read the header row
        $header = fgetcsv($file);
        // Read the rest of the rows and store them in an array
        $rows = [];
        while (($row = fgetcsv($file)) !== false) {
            $rows[] = array_combine($header, $row);
        }

        // Close the file
        fclose($file);

        // Shuffle the rows and take the specified number of rows
        shuffle($rows);
        $selectedRows = array_slice($rows, 0, $validated["numberOfRows"]);

        // Append the selected selected=1 to the selected rows and selected=0 to the unselected rows
        $output = [];
        foreach ($rows as $row) {
            $row["selected"] = in_array($row, $selectedRows) ? 1 : 0;
            $output[] = $row;
        }
        // Return a CSV file with the same header and the selected rows with selected=1 and the unselected rows with selected=0
        $outputFile = fopen("php://temp", "r+");
        fputcsv($outputFile, array_keys($output[0]));
        foreach ($output as $row) {
            fputcsv($outputFile, $row);
        }
        rewind($outputFile);
        $outputContent = stream_get_contents($outputFile);
        fclose($outputFile);
        // Add a temporary file to the storage and return it as a download
        $tempFilePath = storage_path("app/private/selectors/" . uniqid() . ".csv");
        file_put_contents($tempFilePath, $outputContent);
        // Create temporary url to the file
        $tempUrl = Storage::temporaryUrl(basename($tempFilePath), now()->addMinutes(1));

        // Return a valid inertia response with the temporary url
        return Inertia::render("Oofs/Selector", [
            "downloadUrl" => $tempUrl,
            "expiresAt" => now()->addMinutes(1)->toDateTimeString(),
        ]);
    }

    public function viewContactCreator()
    {
        return Inertia::render("Oofs/ContactCreator");
    }

    public function handleContactCreator(Request $request)
    {
        $step = $request->validate([
            "step" => "required|integer|min:1|max:2",
            ])["step"];

        switch ($step) {
            case 1:
                $result = $this->handleContactCreatorStep1($request);
                break;
            case 2:
                $result = $this->handleContactCreatorStep2($request);
                break;
        }
        return $result;
    }

    private function handleContactCreatorStep1(Request $request)
    {
        $validated = $request->validate([
            "file" => "required|file|mimes:csv",
        ]);

        $return = [
            "headers" => null,
            "filePath" => null,
        ];

        // Store the file to storage and return its path
        $return["filePath"] = Storage::putFile("contact-creators", $validated["file"]);

        // Get header row and first data row
        $file = fopen($validated["file"], "r");
        $return["headers"] = fgetcsv($file);
        fclose($file);
        return Inertia::render("Oofs/ContactCreator", [
            "filePath" => $return["filePath"],
            "headers" => $return["headers"],
            "step" => 2,
        ]);
    }

    private function handleContactCreatorStep2(Request $request)
    {
        $validated = $request->validate([
            "filePath" => "required|string",
            "mapping" => "required|array",
            "note" => "nullable|string",
        ]);

        // Get the file from storage
        $filePath = Storage::path($validated["filePath"]);
        $file = fopen($filePath, "r");

        // Read the header row
        $header = fgetcsv($file);
        // Read the rest of the rows and store them in an array
        $rows = [];
        while (($row = fgetcsv($file)) !== false) {
            $rows[] = array_combine($header, $row);
        }

        // Check if mapping keys are unique
        if (count($validated["mapping"]) !== count(array_unique($validated["mapping"]))) {
            return Inertia::render("Oofs/ContactCreator", [
                "filePath" => $validated["filePath"],
                "headers" => $header,
                "step" => 2,
                "errors" => ["mapping" => "You can't map multiple columns to the same field."],
            ]);
        }

        // Create contacts for each row using the specified name and email columns
        $vcard = "";
        foreach ($rows as $row) {
            // Generate vcard content
            $vcard .= "BEGIN:VCARD\nVERSION:3.0\n";
            $fnameMapping = array_search("fname", $validated["mapping"]);
            $lnameMapping = array_search("lname", $validated["mapping"]);
            $vcard .= "N:" . ($row[$lnameMapping] ?? "") . ";" . ($row[$fnameMapping] ?? "") . ";;;\n";
            $vcardFieldTypes = [
                "email" => "EMAIL",
                "phone" => "TEL",
                "pronouns" => "PRONOUNS"
            ];
            foreach ($validated["mapping"] as $field => $column) {
                if (isset($vcardFieldTypes[$column]) && isset($row[$field])) {
                    $vcard .= $vcardFieldTypes[$column] . ":" . $row[$field] . "\n";
                }
            }
            $vcard .= "END:VCARD \n";
        }

        // Write the vcard content to a file and return it as a download
        $tempFilePath = storage_path("app/private/contact-creators/" . uniqid() . ".vcf");
        file_put_contents($tempFilePath, $vcard);
        $tempUrl = Storage::temporaryUrl("contact-creators/" . basename($tempFilePath), now()->addMinutes(1));

        return Inertia::render("Oofs/ContactCreator", [
            "step" => 3,
            "tempUrl" => $tempUrl,
            "expiresAt" => now()->addMinutes(1)->toDateTimeString(),
        ]);
    }
}
