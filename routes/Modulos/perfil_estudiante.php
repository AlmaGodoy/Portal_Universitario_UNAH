<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilEstudianteController;

Route::middleware(['auth', 'session.timeout'])
    ->prefix('estudiante')
    ->name('estudiante.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PERFIL DEL ESTUDIANTE
        |--------------------------------------------------------------------------
        */

        Route::get('/mi-perfil', [PerfilEstudianteController::class, 'index'])
            ->name('mi-perfil');

    });