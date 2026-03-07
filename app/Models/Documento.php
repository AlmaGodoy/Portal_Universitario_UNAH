<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'tbl_documento';
    protected $primaryKey = 'id_documento';
    public $timestamps = false;

    const AUT_NO_VERIFICADO = 'no_verificado';
    const AUT_VERIFICADO = 'verificado';
    const AUT_FRAUDULENTO = 'fraudulento';

    protected $fillable = [
        'id_tramite',
        'tipo_documento',
        'nombre_documento',
        'hash_contenido',
        'ruta_archivo',
        'fecha_carga',
        'autenticidad_documento',
        'numero_folio',
        'estado'
    ];

    protected static function booted()
    {
        static::creating(function ($documento) {
            if (empty($documento->fecha_carga)) {
                $documento->fecha_carga = now();
            }

            if (empty($documento->autenticidad_documento)) {
                $documento->autenticidad_documento = self::AUT_NO_VERIFICADO;
            }

            if (!isset($documento->estado)) {
                $documento->estado = 1;
            }
        });
    }

    public function tramite()
    {
        return $this->belongsTo(Tramite::class, 'id_tramite', 'id_tramite');
    }
}
