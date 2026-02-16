<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'tbl_pago';
    protected $primaryKey = 'id_pago';

    public $timestamps = false;

    protected $fillable = [
        'id_tramite',
        'id_banco',
        'referencia_banco',
        'monto',
        'fecha_pago',
        'estado_pago',
        'observaciones_pago',
    ];
}
