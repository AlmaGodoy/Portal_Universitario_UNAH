<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MisTramitesController;

Route::middleware('auth')->group(function () {
    Route::get('/mis-tramites', [MisTramitesController::class, 'index'])
        ->name('mis.tramites');
});