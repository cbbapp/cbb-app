<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) CNPJ: cria se não existir; se existir, garante length=14 e unique
        Schema::table('clubes', function (Blueprint $table) {
            if (!Schema::hasColumn('clubes', 'cnpj')) {
                // cria a coluna (nullable por segurança; o Request valida required)
                $table->string('cnpj', 14)->nullable()->after('federacao_id');
            }
        });

        // Se já existia, tenta ajustar para 14 e garantir unique
        // (requer doctrine/dbal para ->change(); se não tiver, pode pular que continua funcionando)
        if (Schema::hasColumn('clubes', 'cnpj')) {
            try {
                Schema::table('clubes', function (Blueprint $table) {
                    $table->string('cnpj', 14)->nullable()->change();
                });
            } catch (\Throwable $e) {
                // sem DBAL ou já está no formato desejado; segue o fluxo
            }

            // garante índice unique se não existir
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map('strtolower', array_keys($sm->listTableIndexes('clubes')));
                if (!in_array('clubes_cnpj_unique', $indexes) && !in_array('cnpj_unique', $indexes)) {
                    Schema::table('clubes', function (Blueprint $table) {
                        $table->unique('cnpj');
                    });
                }
            } catch (\Throwable $e) {
                // se doctrine não estiver disponível, tenta criar "no escuro"
                try {
                    Schema::table('clubes', function (Blueprint $table) {
                        $table->unique('cnpj');
                    });
                } catch (\Throwable $e2) {
                    // índice já existe – tudo bem
                }
            }
        }

        // 2) Demais campos: só cria se não existirem
        Schema::table('clubes', function (Blueprint $table) {
            if (!Schema::hasColumn('clubes', 'cidade')) {
                $table->string('cidade', 120)->nullable()->after('cnpj');
            }
            if (!Schema::hasColumn('clubes', 'estado')) {
                $table->string('estado', 2)->nullable()->after('cidade');
            }
            if (!Schema::hasColumn('clubes', 'whatsapp_admin')) {
                $table->string('whatsapp_admin', 20)->nullable()->after('estado');
            }
        });
    }

    public function down(): void
    {
        // remove na ordem inversa, checando existência
        Schema::table('clubes', function (Blueprint $table) {
            if (Schema::hasColumn('clubes', 'whatsapp_admin')) {
                $table->dropColumn('whatsapp_admin');
            }
            if (Schema::hasColumn('clubes', 'estado')) {
                $table->dropColumn('estado');
            }
            if (Schema::hasColumn('clubes', 'cidade')) {
                $table->dropColumn('cidade');
            }
        });

        // para cnpj, remove unique e (se quiser) a coluna
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
                // se não conseguir inspecionar, tenta dropar pelo nome padrão
                try {
                    Schema::table('clubes', function (Blueprint $table) {
                        $table->dropUnique('clubes_cnpj_unique');
                    });
                } catch (\Throwable $e2) {}
                try {
                    Schema::table('clubes', function (Blueprint $table) {
                        $table->dropUnique('cnpj_unique');
                    });
                } catch (\Throwable $e3) {}
            }

            // Se você quiser realmente remover a coluna no down (opcional):
            // Schema::table('clubes', function (Blueprint $table) {
            //     $table->dropColumn('cnpj');
            // });
        }
    }
};
