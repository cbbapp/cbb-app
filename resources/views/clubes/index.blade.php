<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Clubes - Listagem</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Clubes</h1>

  @if (session('ok'))
    <p style="color:green">{{ session('ok') }}</p>
  @endif

  @can('club.create')
    <p>
      <a href="{{ route('club.create') }}">+ Cadastrar clube</a>
    </p>
  @endcan

  @if ($itens->count() === 0)
    <p>Nenhum clube encontrado.</p>
  @else
    <table border="1" cellpadding="6" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Federação</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($itens as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->nome }}</td>
            <td>{{ $c->federacao->sigla ?? '-' }}</td>
            <td>
              @role('admin')
                <form method="POST" action="{{ route('club.destroy', $c->id) }}" onsubmit="return confirm('Excluir este clube?');">
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
      {{ $itens->links() }}
    </div>
  @endif

  <p style="margin-top:16px;">
    <a href="{{ route('dashboard') }}">← voltar ao painel</a>
  </p>
</body>
</html>
