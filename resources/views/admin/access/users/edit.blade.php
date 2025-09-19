<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Acesso • {{ $user->name }}</h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
      @if(session('ok'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('ok') }}</div>
      @endif

      <form method="POST" action="{{ route('admin.access.users.update', $user) }}" class="bg-white shadow-sm sm:rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          {{-- Papéis --}}
          <div>
            <h3 class="font-bold mb-2">Papéis</h3>
            @foreach($roles as $role)
              <label class="flex items-center gap-2 mb-2">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ in_array($role->name, $userRoleNames) ? 'checked' : '' }}>
                <span>{{ $role->name }}</span>
              </label>
            @endforeach
          </div>

          {{-- Permissões diretas --}}
          <div>
            <h3 class="font-bold mb-2">Permissões diretas</h3>
            <p class="text-xs text-gray-500 mb-2">Use permissões diretas só para exceções; prefira conceder via Papéis.</p>
            <div class="max-h-80 overflow-y-auto border rounded p-3">
              @foreach($perms as $perm)
                <label class="flex items-center gap-2 mb-2">
                  <input type="checkbox" name="perms[]" value="{{ $perm->name }}" {{ in_array($perm->name, $userPermNames) ? 'checked' : '' }}>
                  <span>{{ $perm->name }}</span>
                </label>
              @endforeach
            </div>
          </div>
        </div>

        <div class="mt-6 flex gap-3">
          <button class="px-4 py-2 bg-blue-600 text-white rounded">Salvar</button>
          <a href="{{ route('admin.access.users.index') }}" class="px-4 py-2 border rounded">Voltar</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
