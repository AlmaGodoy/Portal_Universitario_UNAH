<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialAcademico extends Model
{
    protected $table = 'tbl_historial_academico';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'clases_aprobadas',
        'fecha_cambio',
        'estado',
        'ultima_actualizacion'
    ];
}
