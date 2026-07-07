<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilSecretariaAcademicaController;

Route::middleware(['auth', 'session.timeout'])
    ->prefix('secretaria-academica')
    ->name('secretaria-academica.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PERFIL DE SECRETARÍA ACADÉMICA
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/mi-perfil',
            [PerfilSecretariaAcademicaController::class, 'index']
        )->name('mi-perfil');

    });
