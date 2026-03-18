<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoExcepcionalController;

/*
|--------------------------------------------------------------------------
| Rutas de PumaGestión - FCEAC
|--------------------------------------------------------------------------
*/

// ── API (las que ya tenías, sin tocar) ──────────────────────────────────
Route::prefix('api/cancelaciones')->group(function () {
    Route::post('crear',               [DocumentoExcepcionalController::class, 'subir']);
    Route::get('todas',                [DocumentoExcepcionalController::class, 'obtenerTodos']);
    Route::get('detalle/{id}',         [DocumentoExcepcionalController::class, 'obtenerCancelacion']);
    Route::delete('eliminar/{id}',     [DocumentoExcepcionalController::class, 'eliminar']);
    Route::post('guardar-documento',   [DocumentoExcepcionalController::class, 'guardarDocumento']);
    Route::put('actualizar/{id}',      [DocumentoExcepcionalController::class, 'actualizar']);
});

// ── Cancelación Excepcional — ────────────────

// Paso 1: mostrar formulario
Route::get('/cancelacion-excepcional', [DocumentoExcepcionalController::class, 'index'])
    ->name('cancelacion.index');

// Paso 1: procesar formulario → redirige al paso 2
Route::post('/cancelacion-excepcional/subir', [DocumentoExcepcionalController::class, 'subir'])
    ->name('cancelacion.subir');

// Paso 2: mostrar formulario de documentos
Route::get('/cancelacion-excepcional/paso2', [DocumentoExcepcionalController::class, 'paso2'])
    ->name('cancelacion.paso2');

// Paso 2: guardar documento PDF
Route::post('/cancelacion-excepcional/guardar', [DocumentoExcepcionalController::class, 'guardarDocumento'])
    ->name('cancelacion.guardar');

// Paso 3: pantalla de éxito
Route::get('/cancelacion-excepcional/exito', [DocumentoExcepcionalController::class, 'exito'])
    ->name('cancelacion.exito');
