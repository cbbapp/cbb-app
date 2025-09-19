<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AccessController extends Controller
{
    // ==== Usuários ====
    public function usersIndex(Request $request)
    {
        $q = trim($request->get('q', ''));
        $users = User::query()
            ->when($q, fn($qr) =>
                $qr->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
            )
            ->with(['roles', 'permissions'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.access.users.index', compact('users', 'q'));
    }

    public function usersEdit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $perms = Permission::orderBy('name')->get();

        $userRoleNames = $user->roles->pluck('name')->toArray();
        $userPermNames = $user->getDirectPermissions()->pluck('name')->toArray(); // só diretas

        return view('admin.access.users.edit', compact('user', 'roles', 'perms', 'userRoleNames', 'userPermNames'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        $roles = $request->input('roles', []);        // array de nomes de roles
        $perms = $request->input('perms', []);        // array de nomes de perms

        // sincroniza papéis e permissões diretas
        $user->syncRoles($roles);
        $user->syncPermissions($perms);

        // importante: limpar cache da Spatie
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.access.users.edit', $user)->with('ok', 'Acesso do usuário atualizado.');
    }

    // ==== Papéis x Permissões (matriz) ====
    public function rolesIndex()
    {
        $roles = Role::orderBy('name')->get();
        $perms = Permission::orderBy('name')->get();

        // matriz: [roleName => [permName => bool]]
        $matrix = [];
        foreach ($roles as $role) {
            $rp = $role->permissions->pluck('name')->all();
            foreach ($perms as $p) {
                $matrix[$role->name][$p->name] = in_array($p->name, $rp, true);
            }
        }

        return view('admin.access.roles.index', compact('roles', 'perms', 'matrix'));
    }

    public function rolesUpdate(Request $request)
    {
        $roles = Role::orderBy('name')->get();
        $perms = Permission::orderBy('name')->get();

        // payload: perms[roleName][] = permName
        $incoming = $request->input('perms', []);

        foreach ($roles as $role) {
            $selected = $incoming[$role->name] ?? [];
            // garante que são permissões válidas
            $selected = array_values(array_intersect($selected, $perms->pluck('name')->all()));
            $role->syncPermissions($selected);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('ok', 'Permissões dos papéis atualizadas.');
    }
}
