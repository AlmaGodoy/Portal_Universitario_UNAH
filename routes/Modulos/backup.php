<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackupController;

Route::middleware('auth')->group(function () {

    // Cambiamos 'index' por 'mostrarPanel' que es como lo tienes en el controlador
    Route::get('/respaldos', [BackupController::class, 'mostrarPanel'])
        ->name('backup.index');

    // Cambiamos 'probarConexion' (verifica que el nombre coincida exactamente)
    Route::post('/respaldos/probar', [BackupController::class, 'probarConexion'])
        ->name('backup.probar');

    // Cambiamos 'generarRespaldo' por 'crearBackup' que es el nombre en tu código
    Route::post('/respaldos/generar', [BackupController::class, 'crearBackup'])
        ->name('backup.generar');
});
