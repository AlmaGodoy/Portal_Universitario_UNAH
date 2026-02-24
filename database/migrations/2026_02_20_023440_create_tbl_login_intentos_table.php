<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_login_intentos', function (Blueprint $table) {
            $table->bigIncrements('id_intento');
            $table->string('email', 150)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('resultado', 50)->nullable(); // ejemplo: CREDENCIALES_INVALIDAS / USUARIO_NO_EXISTE / SP_ERROR
            $table->text('detalle')->nullable();         // texto adicional
            $table->timestamp('fecha')->useCurrent();

            $table->index(['email']);
            $table->index(['ip']);
            $table->index(['fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_login_intentos');
    }
};
