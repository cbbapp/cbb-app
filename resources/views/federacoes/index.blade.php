<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Federações - Listagem</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; margin:20px; }
    .filtros { margin-bottom: 12px; }
    .filtros input[type="text"] { padding:6px; width:260px; max-width:100%; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    th { background:#f2f2f2; }
  </style>
</head>
<body>
  <h1>Federações</h1>

  @if (session('ok'))
    <p style="color:green">{{ session('ok') }}</p>
  @endif

  <form method="GET" class="filtros">
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Buscar por nome, sigla ou CNPJ">
    <button type="submit">Buscar</button>
    <a href="{{ route('federation.index') }}">Limpar</a>
  </form>

  @role('admin')
    <p>
      <a href="{{ route('federation.create') }}">+ Cadastrar federação</a>
    </p>
  @endrole

  @if ($federacoes->count() === 0)
    <p>Nenhuma federação encontrada.</p>
  @else
    <table>
      <thead>
        <tr>
          <th>Sigla</th>
          <th>Nome</th>
          <th style="width:260px;">Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($federacoes as $f)
          <tr>
            <td>{{ $f->sigla }}</td>
            <td>
              <a href="{{ route('federation.show', $f->id) }}">
                {{ $f->nome }}
              </a>
            </td>
            <td>
              <a href="{{ route('federation.show', $f->id) }}">Abrir ficha</a>
              @role('admin')
                | <a href="{{ route('federation.edit', $f->id) }}">Editar</a>
                | <form method="POST" action="{{ route('federation.destroy', $f->id) }}" onsubmit="return confirm('Excluir esta federação?');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Excluir</button>
                  </form>
              @endrole
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div style="margin-top:12px;">
      {{ $federacoes->links() }}
    </div>
  @endif

  <p style="margin-top:16px;">
    <a href="{{ route('dashboard') }}">← voltar ao painel</a>
  </p>
</body>
</html>
