<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpleadoController;

/*
|--------------------------------------------------------------------------
| Rutas del módulo Empleados
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/panel-empleado', [EmpleadoController::class, 'index'])->name('empleado.panel');
});
