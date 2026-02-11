<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoExcepcionalController;

// Rutas de API agrupadas
Route::prefix('api')->group(function () {
    Route::post('cancelaciones/crear', [DocumentoExcepcionalController::class, 'subir']);
    Route::get('cancelaciones/todas', [DocumentoExcepcionalController::class, 'obtenerTodos']);
    Route::get('cancelaciones/detalle/{id}', [DocumentoExcepcionalController::class, 'obtenerCancelacion']);
    Route::delete('cancelaciones/eliminar/{id}', [DocumentoExcepcionalController::class, 'eliminar']);
});

// Rutas web
Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
