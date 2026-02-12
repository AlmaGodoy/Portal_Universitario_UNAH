<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'tbl_bitacora';
    protected $primaryKey = 'id_bitacora';

    protected $fillable = [
        'id_bitacora',
        'id_usuario',
        'id_objeto',
        'accion',
        'fecha_accion',
        'descripcion'
    ];

    public $timestamps = false;

    // Relación con Usuario
    public function Usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    // Relacion con el objeto
    public function Objeto()
    {
        return $this->belongsTo(Objeto::class, 'id_objeto', 'id_objeto');
    }
}
