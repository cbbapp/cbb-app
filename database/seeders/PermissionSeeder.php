<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Este seeder cria TODAS as permissões do sistema
 * e atribui essas permissões aos PAPÉIS (roles):
 *
 * PAPÉIS:
 * - admin      (Administração CBB)
 * - federacao  (Usuário vinculado a uma Federação estadual)
 * - clube      (Usuário de clube)
 *
 * REGRAS DE NEGÓCIO (resumo):
 * - QUALQUER usuário autenticado com as permissões "transfer.request.*" pode SOLICITAR transferência.
 * - APROVAR:
 *     * admin: aprova LOCAL, INTERESTADUAL e INTERNACIONAL
 *     * federacao: aprova APENAS LOCAL dentro da sua própria federação (checagem no Controller)
 *     * clube: NÃO aprova nada
 * - REJEITAR:
 *     * admin: pode rejeitar QUALQUER
 *     * federacao: pode rejeitar APENAS LOCAL dentro da sua própria federação (checagem no Controller)
 * - CRUD por papel:
 *     * clube: pode CADASTRAR atletas (create) e visualizar/atualizar (view/update) seus atletas
 *     * federacao: pode CADASTRAR clubes e atletas
 *     * admin: pode CADASTRAR federações, clubes e atletas, e pode EXCLUIR (delete) qualquer cadastro
 *
 * Observação: a limitação de escopo da federação (só aprovar/rejeitar local da sua federação)
 * é feita no CONTROLLER (lógica de negócio), não aqui.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Lista de permissões (granular)
         * - Atletas: criar, atualizar, visualizar, excluir
         * - Clubes: criar, excluir
         * - Federações: criar, excluir
         * - Transferências:
         *    - Solicitar: local / interestadual / internacional
         *    - Aprovar: local / interestadual / internacional
         *    - Rejeitar
         * - Relatórios: visualizar
         */
        $permissions = [
            // ===== ATLETAS =====
            'athlete.create',
            'athlete.update',
            'athlete.view',
            'athlete.delete',           // (somente admin)

            // ===== CLUBES =====
            'club.create',
            'club.delete',              // (somente admin)

            // ===== FEDERAÇÕES =====
            'federation.create',
            'federation.delete',        // (somente admin)

            // ===== TRANSFERÊNCIAS - SOLICITAÇÕES =====
            'transfer.request.local',
            'transfer.request.interstate',
            'transfer.request.international',

            // ===== TRANSFERÊNCIAS - APROVAÇÕES =====
            'transfer.approve.local',          // federação (com escopo) e admin
            'transfer.approve.interstate',     // apenas admin
            'transfer.approve.international',  // apenas admin

            // ===== TRANSFERÊNCIAS - REJEIÇÃO =====
            'transfer.reject',

            // ===== RELATÓRIOS =====
            'report.view',
        ];

        // Cria permissões se ainda não existirem (guard web)
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ===== PAPÉIS =====
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);       // Administração CBB
        $fed   = Role::firstOrCreate(['name' => 'federacao', 'guard_name' => 'web']);   // Usuário de Federação
        $clube = Role::firstOrCreate(['name' => 'clube', 'guard_name' => 'web']);       // Usuário de Clube

        /**
         * ===== ADMIN (CBB) =====
         * Pode tudo: CRUD completo de atletas/clubes/federações,
         * solicitar QUALQUER transferência,
         * aprovar (local/interestadual/internacional),
         * rejeitar qualquer,
         * e visualizar relatórios.
         */
        $admin->syncPermissions($permissions);

        /**
         * ===== FEDERAÇÃO =====
         * Pode criar/atualizar/visualizar atletas,
         * criar clubes,
         * solicitar QUALQUER transferência,
         * aprova APENAS LOCAL (checagem de escopo no Controller),
         * rejeita APENAS LOCAL (checagem no Controller),
         * e pode ver relatórios.
         * (Sem permissões de delete.)
         */
        $fed->syncPermissions([
            // atletas
            'athlete.create', 'athlete.update', 'athlete.view',
            // clubes
            'club.create',
            // transferências
            'transfer.request.local', 'transfer.request.interstate', 'transfer.request.international',
            'transfer.approve.local',
            'transfer.reject',
            // relatórios
            'report.view',
        ]);

        /**
         * ===== CLUBE =====
         * Pode criar/atualizar/visualizar atletas,
         * solicitar QUALQUER transferência,
         * mas NÃO aprova nem rejeita,
         * e não cria clubes/federações.
         * (Sem permissões de delete.)
         */
        $clube->syncPermissions([
            // atletas
            'athlete.create', 'athlete.update', 'athlete.view',
            // transferências
            'transfer.request.local', 'transfer.request.interstate', 'transfer.request.international',
        ]);
    }
}
