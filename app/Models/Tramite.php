<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tramite extends Model
{
    protected $table = 'tbl_tramite';
    protected $primaryKey = 'id_tramite';
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'id_calendario_academico',
        'tipo_tramite_academico',
        'fecha_solicitud',
        'resolucion_de_tramite_academico',
        'prioridad',
        'valor_boleta_pago',
        'direccion',
        'estado',
        'ultima_actualizacion'
    ];
}
