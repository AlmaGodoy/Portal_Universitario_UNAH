<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditoriaController;

Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria');
});
