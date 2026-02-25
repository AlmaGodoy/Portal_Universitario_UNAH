<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingRegistrationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('documento', 13);
            $table->string('correo')->unique();
            $table->string('contrasena_hash');

            $table->string('tipo_usuario');
            $table->integer('id_rol');

            $table->string('numero_cuenta')->nullable();
            $table->integer('id_carrera')->nullable();
            $table->integer('id_departamento')->nullable();

            $table->string('cod_empleado')->nullable();
            $table->string('tipo_empleado')->nullable();

            $table->string('token')->unique();
            $table->timestamp('expires_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
}
