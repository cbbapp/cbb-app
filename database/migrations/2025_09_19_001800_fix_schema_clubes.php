<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Garantir nullable nos campos opcionais (se existirem)
        try {
            Schema::table('clubes', function (Blueprint $table) {
                if (Schema::hasColumn('clubes', 'cidade')) {
                    $table->string('cidade', 120)->nullable()->change();
                }
                if (Schema::hasColumn('clubes', 'estado')) {
                    $table->char('estado', 2)->nullable()->change();
                }
                if (Schema::hasColumn('clubes', 'whatsapp_admin')) {
                    $table->string('whatsapp_admin', 20)->nullable()->change();
                }
            });
        } catch (\Throwable $e) {
            // Se não tiver doctrine/dbal, apenas segue; não é crítico para funcionar.
        }

        // 2) Garantir unique(cnpj) se não existir
        if (Schema::hasColumn('clubes', 'cnpj')) {
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map('strtolower', array_keys($sm->listTableIndexes('clubes')));
                if (!in_array('clubes_cnpj_unique', $indexes) && !in_array('cnpj_unique', $indexes)) {
                    Schema::table('clubes', function (Blueprint $table) {
                        $table->unique('cnpj');
                    });
                }
            } catch (\Throwable $e) {
                // Sem doctrine: tenta criar; se já existir, ignora.
                try {
                    Schema::table('clubes', function (Blueprint $table) {
                        $table->unique('cnpj');
                    });
                } catch (\Throwable $e2) {
                    // índice já existe
                }
            }
        }
    }

    public function down(): void
    {
        // Remover unique(cnpj) se existir
        if (Schema::hasColumn('clubes', 'cnpj')) {
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map('strtolower', array_keys($sm->listTableIndexes('clubes')));
                Schema::table('clubes', function (Blueprint $table) use ($indexes) {
                    if (in_array('clubes_cnpj_unique', $indexes)) {
                        $table->dropUnique('clubes_cnpj_unique');
                    } elseif (in_array('cnpj_unique', $indexes)) {
                        $table->dropUnique('cnpj_unique');
                    }
                });
            } catch (\Throwable $e) {
                try { Schema::table('clubes', fn(Blueprint $t) => $t->dropUnique('clubes_cnpj_unique')); } catch (\Throwable $e2) {}
                try { Schema::table('clubes', fn(Blueprint $t) => $t->dropUnique('cnpj_unique')); } catch (\Throwable $e3) {}
            }
        }

        // (Opcional) voltar NOT NULL — não necessário para funcionar, então deixei sem revert.
    }
};
