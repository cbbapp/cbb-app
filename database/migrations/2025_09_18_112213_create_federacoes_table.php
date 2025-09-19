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
    Schema::create('federacoes', function (Blueprint $table) {
        $table->id();
        $table->string('nome');                 // Nome da entidade
        $table->string('sigla', 10)->nullable();// Sigla, se usar
        $table->string('cpf', 11)->nullable()->unique();   // CPF do representante/entidade (pode ser null)
        $table->string('presidente')->nullable();          // Presidente
        $table->string('site')->nullable();                // Site (opcional)
        $table->string('email')->nullable();               // E-mail (opcional)
        $table->string('telefone', 30)->nullable();        // Telefone (opcional)
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('federacoes');
}
};
