<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    use HasFactory;

    // Guardar datos sin bloqueos
    protected $fillable = [
        'nombre_archivo',
        'tamano',
        'usuario'
    ];
}
