<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Ficha do Clube</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { margin-bottom: 4px; }
    .muted { color:#666; }
    .grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin:16px 0; }
    .card { background:#f9f9f9; border:1px solid #ddd; padding:12px; border-radius:6px; }
    table { width:100%; border-collapse:collapse; margin-top:6px; }
    th, td { border:1px solid #ddd; padding:8px; text-align:left; }
    th { background:#f2f2f2; }
  </style>
</head>
<body>
  <a href="{{ url()->previous() }}">← Voltar</a>

  <h1>{{ $clube->nome }}</h1>
  <div class="muted">
    Federação:
    {{ optional($clube->federacao)->sigla ?? optional($clube->federacao)->nome ?? '-' }}
  </div>

  <div class="grid">
    <div class="card">
      <h3>Dados do Clube</h3>
      <div><strong>CNPJ:</strong> {{ $clube->cnpj_formatado ?? $clube->cnpj }}</div>
      <div><strong>Cidade/UF:</strong> {{ $clube->cidade }} / {{ strtoupper($clube->estado) }}</div>
      @role('admin')
        <div><strong>WhatsApp (Responsável):</strong>
          {{ $clube->whatsapp_admin_formatado ?? ($clube->whatsapp_admin ?: '-') }}
        </div>
      @endrole
    </div>

    <div class="card">
      <h3>Ações</h3>
      @role('admin')
        <p>
          <a href="{{ route('club.edit', $clube->id) }}">Editar Clube</a>
        </p>
      @endrole
      <p>
        <a href="{{ route('athlete.index', ['clube_id' => $clube->id]) }}">Ver Atletas deste Clube</a>
      </p>
    </div>
  </div>

  <div class="card">
    <h3>Atletas do Clube</h3>

    @if ($atletas->count() === 0)
      <p>Nenhum atleta encontrado.</p>
    @else
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Ficha</th>
            @role('admin')
              <th>Editar</th>
            @endrole
          </tr>
        </thead>
        <tbody>
          @foreach ($atletas as $a)
            <tr>
              <td>{{ $a->nome }}</td>
              <td><a href="{{ route('athlete.show', $a->id) }}">Abrir ficha</a></td>
              @role('admin')
                <td><a href="{{ route('athlete.edit', $a->id) }}">Editar</a></td>
              @endrole
            </tr>
          @endforeach
        </tbody>
      </table>

      <div style="margin-top:10px;">
        {{-- agora $atletas é LengthAwarePaginator, então links() existe --}}
        {{ $atletas->links() }}
      </div>
    @endif
  </div>

  <p style="margin-top:16px;">
    <a href="{{ route('club.index') }}">← Voltar à lista de clubes</a>
  </p>
</body>
</html>
