<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoExcepcionalController;

/*
|--------------------------------------------------------------------------
| MODULO: CANCELACION EXCEPCIONAL (API)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->prefix('api/cancelacion')->group(function () {

    Route::middleware('roleid:2')->group(function () {
        Route::post('crear', [DocumentoExcepcionalController::class, 'crear'])
            ->name('cancelacion.crear');

        Route::get('mis-solicitudes', [DocumentoExcepcionalController::class, 'misSolicitudes'])
            ->name('cancelacion.mis-solicitudes');

        Route::get('detalle/{id_tramite}', [DocumentoExcepcionalController::class, 'detalle'])
            ->whereNumber('id_tramite')
            ->name('cancelacion.detalle');

        Route::get('documento/{id_documento}', [DocumentoExcepcionalController::class, 'verDocumento'])
            ->whereNumber('id_documento')
            ->name('cancelacion.documento');
    });
});

/*
|--------------------------------------------------------------------------
| FRONTEND - CANCELACION EXCEPCIONAL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::middleware('roleid:2')->group(function () {
        Route::get('/cancelacion', function () {
            return view('cancelacion');
        })->name('cancelacion.index');

        Route::get('/cancelacion/mis-solicitudes', function () {
            return view('cancelacion_tramites');
        })->name('cancelacion.mis-tramites');

        Route::get('/cancelacion/estado', function () {
            return view('cancelacion_estado');
        })->name('cancelacion.estado');
    });
});