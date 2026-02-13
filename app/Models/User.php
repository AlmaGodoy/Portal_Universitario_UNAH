<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'tbl_usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'contraseña',
        'estado_cuenta',
        'id_rol',
    ];

    protected $hidden = [
        'contraseña',
    ];

    // Laravel espera "password", pero como tú validas con SP,
    // esto solo evita problemas si algo lo pide:
    public function getAuthPassword()
    {
        return $this->contraseña;
    }
}
