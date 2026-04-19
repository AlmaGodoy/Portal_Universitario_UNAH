<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackupController;

// Solo empleados (secretario, secretaria_general, coordinador, administrador)
Route::middleware(['auth', 'session.timeout', 'roleid:1,3,4,5'])->group(function () {

    Route::get('/respaldos', [BackupController::class, 'mostrarPanel'])
        ->name('backup.index');

    Route::post('/respaldos/probar', [BackupController::class, 'probarConexion'])
        ->name('backup.probar');

    Route::post('/respaldos/generar', [BackupController::class, 'crearBackup'])
        ->name('backup.generar');
});