<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clubes', function (Blueprint $table) {
            if (!Schema::hasColumn('clubes', 'cnpj')) {
                $table->string('cnpj', 14)->unique()->after('nome');
            }
            if (!Schema::hasColumn('clubes', 'cidade')) {
                $table->string('cidade', 120)->after('cnpj');
            }
            if (!Schema::hasColumn('clubes', 'estado')) {
                $table->char('estado', 2)->after('cidade');
            }
            if (!Schema::hasColumn('clubes', 'whatsapp_admin')) {
                $table->string('whatsapp_admin', 20)->nullable()->after('estado');
            }
        });
    }

    public function down(): void
    {
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
            if (Schema::hasColumn('clubes', 'cnpj')) {
                $table->dropUnique('clubes_cnpj_unique');
                $table->dropColumn('cnpj');
            }
        });
    }
};
