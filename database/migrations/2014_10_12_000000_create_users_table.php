<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('grado');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('email')->unique();
            $table->string('dni')->unique();
            $table->foreignId('unit_id')->constrained('units');
            $table->enum('status', ['superadmin', 'admin', 'usuario'])->default('usuario');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
