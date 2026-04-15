<?php

namespace App\Models\Modulos;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'tbl_bitacora';
    // ⚠️ Cambia esto si tu tabla tiene otro nombre

    /**
     * Si tu tabla NO tiene created_at y updated_at
     */
    public $timestamps = false;

    /**
     * Campos que se pueden usar
     */
    protected $fillable = [
        'id',
        'id_estudiante',
        'tramite',
        'accion',
        'descripcion',
        'fecha'
    ];

    /**
     * Laravel tratará 'fecha' como fecha
     */
    protected $casts = [
        'fecha' => 'datetime',
    ];
}
