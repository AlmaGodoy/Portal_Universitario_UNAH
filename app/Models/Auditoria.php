<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'tbl_auditoria';
    protected $primaryKey = 'id_auditoria';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_rol',
        'id_carrera_actor',
        'id_carrera',
        'id_objeto',
        'id_tramite',
        'tabla_afectada',
        'id_registro',
        'operacion',
        'accion',
        'descripcion',
        'valor_anterior',
        'valor_nuevo',
        'nivel',
        'ip_address',
        'user_agent',
        'fecha',
    ];

    protected $casts = [
        'id_usuario' => 'integer',
        'id_rol' => 'integer',
        'id_carrera_actor' => 'integer',
        'id_carrera' => 'integer',
        'id_objeto' => 'integer',
        'id_tramite' => 'integer',
        'id_registro' => 'integer',
        'fecha' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function objeto()
    {
        return $this->belongsTo(Objeto::class, 'id_objeto', 'id_objeto');
    }

    public function carreraActor()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera_actor', 'id_carrera');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function tramite()
    {
        return $this->belongsTo(Tramite::class, 'id_tramite', 'id_tramite');
    }
}