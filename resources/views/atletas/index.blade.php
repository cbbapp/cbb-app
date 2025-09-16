<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Atletas - Listagem</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Atletas</h1>

  @if (session('ok'))
    <p style="color:green">{{ session('ok') }}</p>
  @endif

  @can('athlete.create')
    <p>
      <a href="{{ route('athlete.create') }}">+ Cadastrar atleta</a>
    </p>
  @endcan

  @if ($itens->count() === 0)
    <p>Nenhum atleta encontrado.</p>
  @else
    <table border="1" cellpadding="6" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Clube</th>
          <th>Federação</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($itens as $a)
          <tr>
            <td>{{ $a->id }}</td>
            <td>{{ $a->nome }}</td>
            <td>{{ $a->clube->nome ?? '-' }}</td>
            <td>{{ $a->clube->federacao->sigla ?? '-' }}</td>
            <td>
              @role('admin')
                <form method="POST" action="{{ route('athlete.destroy', $a->id) }}" onsubmit="return confirm('Excluir este atleta?');">
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
