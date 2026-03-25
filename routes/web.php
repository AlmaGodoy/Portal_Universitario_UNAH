<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackupController;

// 1. EL PORTAL (Ruta raíz - SIEMPRE LIBRE)
Route::get('/', function () {
    return view('auth.choose_portal');
})->name('portal');

// 2. CARGA INTELIGENTE DE MÓDULOS
$path = __DIR__ . '/Modulos';

if (is_dir($path)) {
    foreach (glob($path . "/*.php") as $file) {
        $filename = basename($file);

        // Si el archivo es de LOGIN o AUTH, se carga LIBRE para que funcionen los botones
        if (str_contains($filename, 'auth') || str_contains($filename, 'login')) {
            require $file;
        }
        else {
            // TODO LO DEMÁS (Seguridad, Usuarios, Planillas) solo para Admin y PM (Roles 1 y 2)
            Route::middleware(['auth', 'roleid:1,2'])->group(function () use ($file) {
                require $file;
            });
        }
    }
}

// 3. DASHBOARD Y RUTAS GENERALES
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// FIRMADO Y ASEGURADO POR: PRO MANAGER ALMA PATRICIA GODOY
