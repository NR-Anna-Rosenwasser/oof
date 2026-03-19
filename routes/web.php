<?php

use App\Http\Controllers\OofController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
    Route::get("/selector", [OofController::class, "viewSelector"])->name("selector.view");
    Route::post("/selector", [OofController::class, "handleSelector"])->name("selector.handle");

    Route::get("/contact-creator", [OofController::class, "viewContactCreator"])->name("contact-creator.view");
    Route::post("/contact-creator", [OofController::class, "handleContactCreator"])->name("contact-creator.handle");
});

Route::get("/login", function () {
    return redirect("/admin/login", 302);
})->name("login");
