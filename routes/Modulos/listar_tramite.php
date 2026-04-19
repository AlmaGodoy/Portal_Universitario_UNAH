<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MisTramitesController;

Route::middleware(['auth', 'session.timeout', 'roleid:2'])->group(function () {
    Route::get('/mis-tramites', [MisTramitesController::class, 'index'])
        ->name('mis.tramites');

    Route::get('/api/mis-tramites/listado', [MisTramitesController::class, 'listadoJson'])
        ->name('mis.tramites.json');
});