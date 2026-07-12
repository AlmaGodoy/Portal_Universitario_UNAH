<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilCoordinadorController;

Route::middleware(['auth', 'session.timeout'])
    ->prefix('coordinador')
    ->name('coordinador.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PERFIL DEL COORDINADOR DE CARRERA
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/mi-perfil',
            [PerfilCoordinadorController::class, 'index']
        )->name('mi-perfil');

    });