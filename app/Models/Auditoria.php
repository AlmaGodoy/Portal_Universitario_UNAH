<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'tbl_auditoria';
    protected $primaryKey = 'id_auditoria';

    protected $fillable = [
        'id_auditoria',
        'id_usuario',
        'id_objeto',
        'accion',
        'descripcion',
        'fecha'
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
