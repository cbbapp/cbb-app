<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Acesso • Usuários</h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <form method="GET" class="mb-4 flex gap-2">
          <input type="text" name="q" value="{{ $q }}" placeholder="Buscar nome ou e-mail" class="border rounded p-2 w-full">
          <button class="px-4 py-2 bg-gray-800 text-white rounded">Buscar</button>
        </form>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b">
                <th class="text-left py-2">Nome</th>
                <th class="text-left py-2">E-mail</th>
                <th class="text-left py-2">Papéis</th>
                <th class="text-left py-2">Permissões diretas</th>
                <th class="py-2"></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($users as $u)
              <tr class="border-b">
                <td class="py-2">{{ $u->name }}</td>
                <td class="py-2">{{ $u->email }}</td>
                <td class="py-2">
                  @foreach($u->roles as $r)
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded mr-1">{{ $r->name }}</span>
                  @endforeach
                </td>
                <td class="py-2">
                  @foreach($u->getDirectPermissions() as $p)
                    <span class="inline-block px-2 py-1 bg-amber-100 text-amber-800 rounded mr-1">{{ $p->name }}</span>
                  @endforeach
                </td>
                <td class="py-2 text-right">
                  <a href="{{ route('admin.access.users.edit', $u) }}" class="px-3 py-1 bg-indigo-600 text-white rounded">Editar</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
      </div>

      <div class="mt-4">
        <a href="{{ route('admin.access.roles.index') }}" class="text-indigo-700 underline">Configurar Papéis × Permissões</a>
      </div>
    </div>
  </div>
</x-app-layout>
