<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';

    protected $fillable = [
        'id_usuario',
        'id_unidad',
        'tipo_usuario',
        'estado_empleado'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'id_unidad', 'id_unidad');
    }
}
