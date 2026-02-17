<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emitir_Resolucion extends Model
{
    protected $table = 'tbl_resolucion';
    protected $primaryKey = 'id_resolucion';

    protected $fillable = [
        'id_tramite',
        'id_coordinador',
        'estado_validacion',
        'observaciones',
        'fecha_resolucion',
        'fecha_anulacion',
        'documento_resolucion',
        'activo'

    ];

    public $timestamps = false;

    // Relación con Tramite
    public function tramite()
    {
        return $this->belongsTo(Tramite::class, 'id_tramite', 'id_tramite');
    }

    // Relación con Coordinador
    public function coordinador()
    {
        return $this->belongsTo(Coordinador::class, 'id_coordinador', 'id_coordinador');
    }
}
