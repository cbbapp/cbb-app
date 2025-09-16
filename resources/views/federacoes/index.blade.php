<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Federações - Listagem</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Federações</h1>

  @if (session('ok'))
    <p style="color:green">{{ session('ok') }}</p>
  @endif

  {{-- Apenas admin enxerga este bloco; a rota no web.php é "federation.create" --}}
  @role('admin')
    <p>
      <a href="{{ route('federation.create') }}">+ Cadastrar federação</a>
    </p>
  @endrole

  @if ($itens->count() === 0)
    <p>Nenhuma federação encontrada.</p>
  @else
    <table border="1" cellpadding="6" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Sigla</th>
          <th>Nome</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($itens as $f)
          <tr>
            <td>{{ $f->id }}</td>
            <td>{{ $f->sigla }}</td>
            <td>{{ $f->nome }}</td>
            <td>
              {{-- Exclusão somente admin; rota no web.php é "federation.destroy" --}}
              <form method="POST" action="{{ route('federation.destroy', $f->id) }}" onsubmit="return confirm('Excluir esta federação?');" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit">Excluir</button>
              </form>
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
