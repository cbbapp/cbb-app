<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('federacoes', function (Blueprint $table) {
            if (Schema::hasColumn('federacoes', 'cpf') && !Schema::hasColumn('federacoes', 'cnpj')) {
                $table->renameColumn('cpf', 'cnpj');
            }
        });
    }

    public function down(): void
    {
        Schema::table('federacoes', function (Blueprint $table) {
            if (Schema::hasColumn('federacoes', 'cnpj') && !Schema::hasColumn('federacoes', 'cpf')) {
                $table->renameColumn('cnpj', 'cpf');
            }
        });
    }
};
