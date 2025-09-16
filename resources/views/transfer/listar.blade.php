<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Transferências pendentes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Transferências pendentes</h1>

  @if (session('ok'))
    <p style="color:green">{{ session('ok') }}</p>
  @endif

  @if ($errors->any())
    <div style="color:red">
      @foreach ($errors->all() as $e)
        <div>{{ $e }}</div>
      @endforeach
    </div>
  @endif

  @if ($pendentes->isEmpty())
    <p>Nenhuma transferência pendente.</p>
  @else
    <table border="1" cellpadding="6" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Atleta</th>
          <th>Origem</th>
          <th>Destino</th>
          <th>Tipo</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($pendentes as $t)
          <tr>
            <td>{{ $t->id }}</td>
            <td>{{ $t->atleta->nome ?? '-' }}</td>
            <td>
              {{ $t->clubOrigem->nome ?? '-' }}
              ({{ $t->clubOrigem->federacao->sigla ?? '-' }})
            </td>
            <td>
              {{ $t->clubDestino->nome ?? '-' }}
              ({{ $t->clubDestino->federacao->sigla ?? '-' }})
            </td>
            <td>{{ $t->tipo }}</td>
            <td>{{ $t->status }}</td>
            <td>
              <a href="{{ route('transfer.acoes', $t->id) }}">ver ações</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div style="margin-top:10px;">
      {{ $pendentes->links() }}
    </div>
  @endif
</body>
</html>
