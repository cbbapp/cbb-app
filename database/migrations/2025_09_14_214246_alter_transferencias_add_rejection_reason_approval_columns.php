<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            // Motivo da rejeição (texto livre), opcional
            if (!Schema::hasColumn('transferencias', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }

            // Auditoria: quem decidiu (aprovar/rejeitar)
            if (!Schema::hasColumn('transferencias', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('rejection_reason');
                $table->foreign('approved_by')
                    ->references('id')->on('users')   // tabela users confirmada
                    ->nullOnDelete();                 // se user for apagado, fica null
            }

            // Auditoria: quando decidiu
            if (!Schema::hasColumn('transferencias', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transferencias', function (Blueprint $table) {
            if (Schema::hasColumn('transferencias', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }

            if (Schema::hasColumn('transferencias', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('transferencias', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
