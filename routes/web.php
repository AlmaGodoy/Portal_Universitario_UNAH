<?php

use Illuminate\Support\Facades\Route;

//Ruta principal (Login)
Route::get('/', function () {
    return view('portal_login');
});

//Autocarga de módulos

$path = __DIR__ . '/Modulos';

if (is_dir($path)) {
    foreach (glob($path . "/*.php") as $file) {
        require $file;
    }
}

//NADIE DEBE TOCAR ESTE ARCHIVO WEB.PHP, SI CREEN QUE ES NECESARIO DEBEN CONSULTAR A SU
//PRO MANAGER PRIMERO
