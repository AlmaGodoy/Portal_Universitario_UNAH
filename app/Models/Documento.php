<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'tbl_documento';
    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'id_tramite',
        'tipo_documento',
        'nombre_documento',
        'hash_contenido',
        'ruta_archivo',
        'fecha_carga',
        'autenticidad_documento'
    ];

    public $timestamps = false;

    // Relación con Tramite
    public function tramite()
    {
        return $this->belongsTo(Tramite::class, 'id_tramite', 'id_tramite');
    }
}
