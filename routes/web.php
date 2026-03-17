<?php

use App\Http\Controllers\OofController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
    Route::get("/selector", [OofController::class, "viewSelector"])->name("selector.view");
    Route::post("/selector", [OofController::class, "handleSelector"])->name("selector.handle");
});

Route::get("/login", function () {
    return redirect("/admin/login", 302);
})->name("login");
