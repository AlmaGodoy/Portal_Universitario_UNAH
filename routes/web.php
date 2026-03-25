<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

//Ruta principal (Login)
Route::get('/', function () {
    return view('auth.choose_portal');
})->name('portal');

//Autocarga de módulos

$path = __DIR__ . '/Modulos';

if (is_dir($path)) {
    foreach (glob($path . "/*.php") as $file) {
        require $file;
    }
}

//NADIE DEBE TOCAR ESTE ARCHIVO WEB.PHP, SI CREEN QUE ES NECESARIO DEBEN CONSULTAR A SU
//PRO MANAGER PRIMERO

