<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tramite extends Model
{
    protected $table = 'tbl_tramite';
    protected $primaryKey = 'id_tramite';
    public $timestamps = false;

    const ESTADO_ACTIVO = 1;
    const ESTADO_INACTIVO = 0;
    const ESTADO_PENDIENTE = 2;

    protected $fillable = [
        'id_persona',
        'id_calendario_academico',
        'id_carrera_destino',
        'tipo_tramite_academico',
        'fecha_solicitud',
        'resolucion_de_tramite_academico',
        'prioridad',
        'valor_boleta_pago',   // aplica solo a trámites con pago
        'direccion',
        'estado',
        'ultima_actualizacion'
    ];

    protected static function booted()
    {
        static::creating(function ($tramite) {
            if (!isset($tramite->estado)) {
                $tramite->estado = self::ESTADO_ACTIVO;
            }
            if (empty($tramite->fecha_solicitud)) {
                $tramite->fecha_solicitud = now();
            }
        });
    }

    /**
     * un trámite puede tener muchos documentos asociados
     */
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'id_tramite', 'id_tramite');
    }

    /**
     * un trámite puede tener una cancelación asociada
     */
    public function cancelacion()
    {
        return $this->hasOne(Cancelacion::class, 'id_tramite', 'id_tramite');
    }

    /**
     * un trámite pertenece a una persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * un trámite pertenece a un calendario académico
     */
    public function calendarioAcademico()
    {
        return $this->belongsTo(CalendarioAcademico::class, 'id_calendario_academico', 'id_calendario_academico');
    }

    /**
     * un trámite puede tener carrera destino (en caso de cambio de carrera)
     */
    public function carreraDestino()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera_destino', 'id_carrera');
    }
}
