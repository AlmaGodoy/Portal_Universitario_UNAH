<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilSecretariaController;

Route::middleware(['auth', 'session.timeout'])
    ->prefix('secretaria-carrera')
    ->name('secretaria-carrera.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PERFIL DE SECRETARÍA DE CARRERA
        |--------------------------------------------------------------------------
        */

        Route::get('/mi-perfil', [PerfilSecretariaController::class, 'index'])
            ->name('mi-perfil');

    });