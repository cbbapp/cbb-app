<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            // Adiciona as colunas, se não existirem
            if (!Schema::hasColumn('transferencias', 'atleta_id')) {
                $table->unsignedBigInteger('atleta_id')->after('id');
                $table->foreign('atleta_id')->references('id')->on('atletas')->onDelete('cascade');
            }

            if (!Schema::hasColumn('transferencias', 'club_origem_id')) {
                $table->unsignedBigInteger('club_origem_id')->after('atleta_id');
                $table->foreign('club_origem_id')->references('id')->on('clubes')->onDelete('cascade');
            }

            if (!Schema::hasColumn('transferencias', 'club_destino_id')) {
                $table->unsignedBigInteger('club_destino_id')->after('club_origem_id');
                $table->foreign('club_destino_id')->references('id')->on('clubes')->onDelete('cascade');
            }

            if (!Schema::hasColumn('transferencias', 'tipo')) {
                $table->enum('tipo', ['local','interstate','international'])->after('club_destino_id');
            }

            if (!Schema::hasColumn('transferencias', 'status')) {
                $table->enum('status', ['pendente','aprovada','rejeitada'])->default('pendente')->after('tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            // Remover FKs primeiro (se existirem), depois as colunas
            if (Schema::hasColumn('transferencias', 'atleta_id')) {
                $table->dropForeign(['atleta_id']);
                $table->dropColumn('atleta_id');
            }
            if (Schema::hasColumn('transferencias', 'club_origem_id')) {
                $table->dropForeign(['club_origem_id']);
                $table->dropColumn('club_origem_id');
            }
            if (Schema::hasColumn('transferencias', 'club_destino_id')) {
                $table->dropForeign(['club_destino_id']);
                $table->dropColumn('club_destino_id');
            }
            if (Schema::hasColumn('transferencias', 'tipo')) {
                $table->dropColumn('tipo');
            }
            if (Schema::hasColumn('transferencias', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
