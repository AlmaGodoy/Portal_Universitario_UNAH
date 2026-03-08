<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objeto extends Model
{
    protected $table = 'tbl_objeto';
    protected $primaryKey = 'id_objeto';
    public $timestamps = false;
}
