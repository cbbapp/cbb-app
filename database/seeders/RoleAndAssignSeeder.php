<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleAndAssignSeeder extends Seeder
{
    public function run(): void
    {
        // Cria os papéis se ainda não existirem
        foreach (['admin','federacao','clube'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // Mapeia cada e-mail para um papel
        $map = [
            'admin@cbbocha.com.br'     => 'admin',
            'federacao@cbbocha.com.br' => 'federacao',
            'clube@cbbocha.com.br'     => 'clube',
        ];

        // Atribui os papéis
        foreach ($map as $email => $role) {
            if ($u = User::where('email', $email)->first()) {
                $u->assignRole($role);
            }
        }
    }
}
