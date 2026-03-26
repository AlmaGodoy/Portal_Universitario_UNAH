<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    // Nombre de la tabla (ajústalo si se llama diferente)
    protected $table = 'empleados';

    // CAMPOS SEGUROS
    protected $fillable = [
        'user_id',
        'identidad',
        'tipo_empleado', // 'docente', 'administrativo', 'mantenimiento'
        'departamento',
        'estado'
    ];

    /**
     * Relación: Un empleado pertenece a un Usuario (User)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
