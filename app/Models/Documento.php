<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{

    protected $table = 'tbl_documento';

    // Definimos la llave primaria
    protected $primaryKey = 'id_documento';


    protected $fillable = [
        'id_tramite',
        'tipo_documento',
        'nombre_documento',
        'hash_contenido',
        'ruta_archivo',
        'fecha_carga',
        'autenticidad_documento', // Usado por ValidarDocumentoController
        'numero_folio',           // Usado por el procedimiento VAL_TRAMITE_ANTIFRAUDE
        'estado'                  // Controla si el documento está activo (1) o eliminado (0)
    ];

    public $timestamps = false;

    // Relación con el Trámite (Un documento pertenece a un trámite)
    public function tramite()
    {
        return $this->belongsTo(Tramite::class, 'id_tramite', 'id_tramite');
    }
}
