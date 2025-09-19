<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Papéis × Permissões</h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      @if(session('ok'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('ok') }}</div>
      @endif

      <form method="POST" action="{{ route('admin.access.roles.update') }}" class="bg-white shadow-sm sm:rounded-lg p-4 overflow-x-auto">
        @csrf
        @method('PUT')

        <table class="min-w-full text-sm">
          <thead>
            <tr>
              <th class="text-left py-2 pr-4">Permissão</th>
              @foreach($roles as $role)
                <th class="text-center py-2 px-3">{{ $role->name }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach($perms as $perm)
              <tr class="border-t">
                <td class="py-2 pr-4">{{ $perm->name }}</td>
                @foreach($roles as $role)
                  <td class="text-center py-2">
                    <input type="checkbox"
                      name="perms[{{ $role->name }}][]"
                      value="{{ $perm->name }}"
                      {{ !empty($matrix[$role->name][$perm->name]) ? 'checked' : '' }}>
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>

        <div class="mt-4">
          <button class="px-4 py-2 bg-blue-600 text-white rounded">Salvar matriz</button>
          <a href="{{ route('admin.access.users.index') }}" class="ml-2 text-indigo-700 underline">Gerenciar usuários</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
