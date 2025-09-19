<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Ficha da Federação</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; margin:20px; }
    h1 { margin-bottom: 10px; }
    .grid { display:grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .card { background:#f9f9f9; border:1px solid #ddd; border-radius:6px; padding:12px; }
    table { width:100%; border-collapse:collapse; margin-top: 8px; }
    th, td { border:1px solid #ddd; padding:8px; text-align:left; }
    th { background:#f2f2f2; }
  </style>
</head>
<body>
  <h1>Ficha da Federação</h1>

  @if (session('ok'))
    <p style="color:green">{{ session('ok') }}</p>
  @endif

  <div class="grid">
    <div class="card">
      <h3>Dados da Entidade</h3>
      <p><strong>Nome:</strong> {{ $federacao->nome }}</p>
      <p><strong>Sigla:</strong> {{ $federacao->sigla }}</p>
      <p><strong>CNPJ:</strong> {{ $federacao->cnpj_formatado ?? $federacao->cnpj }}</p>
      <p><strong>Presidente:</strong> {{ $federacao->presidente }}</p>
      <p><strong>Site:</strong>
        @if($federacao->site)
          <a href="{{ str_starts_with($federacao->site,'http') ? $federacao->site : 'https://'.$federacao->site }}" target="_blank" rel="noopener">
            {{ $federacao->site }}
          </a>
        @else
          —
        @endif
      </p>
      <p><strong>E-mail:</strong> {{ $federacao->email ?: '—' }}</p>
      <p><strong>Telefone:</strong> {{ $federacao->telefone ?: '—' }}</p>

      <p style="margin-top:10px;">
        <a href="{{ route('federation.edit', $federacao->id) }}">Editar federação</a> |
        <a href="{{ route('federation.index') }}">Voltar para lista</a>
      </p>
    </div>

    <div class="card">
      <h3>Clubes Vinculados ({{ $federacao->clubes->count() }})</h3>

      <table>
        <thead>
          <tr>
            <th>Clube</th>
            <th>Cidade/UF</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
        @forelse($federacao->clubes as $c)
          <tr>
            <td>{{ $c->nome }}</td>
            <td>{{ $c->cidade ?: '-' }}/{{ $c->estado ?: '-' }}</td>
            <td>
              <a href="{{ route('club.show', $c->id) }}">Abrir clube</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="3">Nenhum clube vinculado.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <p style="margin-top:16px;">
    <a href="{{ route('dashboard') }}">← Voltar ao painel</a>
  </p>
</body>
</html>
