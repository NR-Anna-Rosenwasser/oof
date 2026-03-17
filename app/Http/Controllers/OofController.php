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
        $tempFilePath = storage_path("app/private/" . uniqid() . ".csv");
        file_put_contents($tempFilePath, $outputContent);
        // Create temporary url to the file
        $tempUrl = Storage::temporaryUrl(basename($tempFilePath), now()->addMinutes(1));

        // Return a valid inertia response with the temporary url
        return Inertia::render("Oofs/Selector", [
            "downloadUrl" => $tempUrl,
            "expiresAt" => now()->addMinutes(1)->toDateTimeString(),
        ]);
    }
}
