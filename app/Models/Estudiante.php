<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'tbl_estudiante';
    protected $primaryKey = 'id_estudiante';
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'numero_cuenta',
        'id_carrera',
        'indice_periodo',
        'indice_global',
        'cantidad_clases_aprobadas',
    ];

    /**
     * RELACIÓN CON PERSONA
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }
}