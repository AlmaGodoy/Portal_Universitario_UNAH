<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\BackupController;

Route::get('/', function () {
    return view('auth.login');
})->name('portal');

// 2. MÓDULOS PARA TODOS (Alumnos, Admins, etc.)
// Aquí cargamos solo lo que los alumnos NECESITAN ver
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// MÓDULOS RESTRINGIDOS (Solo Roles 1 y 2: Admin y Pro Manager)
Route::middleware(['auth', 'roleid:1,2'])->group(function () {

    $path = __DIR__ . '/Modulos';

    if (is_dir($path)) {
        foreach (glob($path . "/*.php") as $file) {
            require $file;
        }
    }
});

// NADIE DEBE TOCAR ESTE ARCHIVO WEB.PHP, SI CREEN QUE ES NECESARIO DEBEN CONSULTAR A SU
// PRO MANAGER PRIMERO

