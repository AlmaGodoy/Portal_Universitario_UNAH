<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'tbl_persona';
    protected $primaryKey = 'id_persona';

    protected $fillable = [
        'nombre_persona',
        'numero_documento',
        'correo_institucional',
        'tipo_usuario',
        'estado'
    ];

    public $timestamps = false;

}
