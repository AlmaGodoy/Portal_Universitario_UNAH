<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackupController;

Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::get('/respaldos', [BackupController::class, 'mostrarPanel'])
        ->name('backup.index');

    Route::post('/respaldos/probar', [BackupController::class, 'probarConexion'])
        ->name('backup.probar');

    Route::post('/respaldos/generar', [BackupController::class, 'crearBackup'])
        ->name('backup.generar');
});
