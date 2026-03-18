<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoExcepcionalController;

/*
|--------------------------------------------------------------------------
| API de Cancelaciones
|--------------------------------------------------------------------------
*/

Route::prefix('api/cancelaciones')->group(function () {
    Route::post('crear', [DocumentoExcepcionalController::class, 'subir']);
    Route::get('todas', [DocumentoExcepcionalController::class, 'obtenerTodos']);
    Route::get('detalle/{id}', [DocumentoExcepcionalController::class, 'obtenerCancelacion']);
    Route::delete('eliminar/{id}', [DocumentoExcepcionalController::class, 'eliminar']);
    Route::post('guardar-documento', [DocumentoExcepcionalController::class, 'guardarDocumento']);
    Route::put('actualizar/{id}', [DocumentoExcepcionalController::class, 'actualizar']);
});

Route::get('/cancelacion-excepcional', function () {
    return view('cancelacion');
})->name('cancelacion.index');
