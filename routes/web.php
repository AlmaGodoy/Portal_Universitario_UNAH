<?php

use Illuminate\Support\Facades\Route;

// 1. PORTAL DE INICIO
Route::get('/', function () {
    return view('auth.choose_portal');
})->name('portal');

// 2. EL "CEREBRO" DE CARGA DE MÓDULOS
$path = __DIR__ . '/Modulos';

if (is_dir($path)) {
    // Escaneamos todos los archivos .php dentro de /Modulos
    foreach (glob($path . "/*.php") as $file) {
        $filename = basename($file);

        if (str_contains($filename, 'auth') || str_contains($filename, 'login')) {
            require $file;
        }
        else {
            Route::middleware(['auth', 'roleid:1,2'])->group(function () use ($file) {
                require $file;
            });
        }
    }
}

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

