<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tbl_usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'contraseña',
        'estado_cuenta',
    ];

    protected $hidden = [
        'contraseña',
        'remember_token',
    ];

    // Relación con la tabla empleados.
    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'user_id', 'id_usuario');
    }

    // Indica a Laravel que use 'contraseña' para el login
    public function getAuthPassword()
    {
        return $this->contraseña;
    }
}
