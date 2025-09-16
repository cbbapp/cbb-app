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
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('atleta_id');
            $table->unsignedBigInteger('club_origem_id');
            $table->unsignedBigInteger('club_destino_id');
            $table->enum('tipo', ['local', 'interstate', 'international']);
            $table->enum('status', ['pendente', 'aprovada', 'rejeitada'])->default('pendente');
            $table->timestamps();

            $table->foreign('atleta_id')->references('id')->on('atletas')->onDelete('cascade');
            $table->foreign('club_origem_id')->references('id')->on('clubes')->onDelete('cascade');
            $table->foreign('club_destino_id')->references('id')->on('clubes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};
