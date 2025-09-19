<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Ficha do Atleta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { margin-bottom: 10px; }
    .info { margin-bottom: 12px; }
    .label { font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #f2f2f2; }
  </style>
</head>
<body>
  <h1>Ficha do Atleta</h1>

  <div class="info">
    <span class="label">Nome Completo:</span>
    {{ $atleta->nome }}
  </div>

  <div class="info">
    <span class="label">Idade:</span>
    {{ \Carbon\Carbon::parse($atleta->data_nascimento)->age }} anos
  </div>

  <div class="info">
    <span class="label">Clube Atual:</span>
    {{ $atleta->clube->nome ?? '-' }}
  </div>

  <div class="info">
    <span class="label">Situação:</span>
    @if($atleta->situacao === 'irregular')
      <span style="color:red">Irregular</span>
    @else
      <span style="color:green">Apto</span>
    @endif
  </div>

  <h2>Histórico de Transferências</h2>
  @if($atleta->transferencias->isEmpty())
    <p>Nenhuma transferência registrada.</p>
  @else
    <table>
      <thead>
        <tr>
          <th>Clube de Destino</th>
          <th>Data da Transferência</th>
        </tr>
      </thead>
      <tbody>
        @foreach($atleta->transferencias as $t)
          <tr>
            <td>{{ $t->clubDestino->nome ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <p style="margin-top:20px;">
    <a href="{{ route('athlete.index') }}">← Voltar para lista de atletas</a>
  </p>
</body>
</html>
