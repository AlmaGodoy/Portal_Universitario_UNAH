<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

// ---------------------------------------------------------
// RUTA PRINCIPAL (LOGIN / SELECCIÓN DE PORTAL)
// ---------------------------------------------------------
Route::get('/', function () {
    return view('auth.choose_portal');
})->name('portal');


// ---------------------------------------------------------
// AUTOCARGA DE MÓDULOS
// ---------------------------------------------------------

$path = __DIR__ . '/Modulos';

if (is_dir($path)) {
    foreach (glob($path . "/*.php") as $file) {
        require $file;
    }
}


// ---------------------------------------------------------
// ADVERTENCIA DE SEGURIDAD
// ---------------------------------------------------------
// NADIE DEBE TOCAR ESTE ARCHIVO WEB.PHP.
// SI CREEN QUE ES NECESARIO, DEBEN CONSULTAR A SU
// PRO MANAGER (ALMA PATRICIA) PRIMERO.
// ---------------------------------------------------------
