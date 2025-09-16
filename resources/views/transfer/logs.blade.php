<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Logs de Auditoria — Transferências</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 8px; font-size: 14px; }
    th { background: #f4f4f4; text-align: left; }
    .badge { padding: 2px 6px; border-radius: 4px; font-size: 12px; }
    .b-pendente { background: #ffe9a3; }
    .b-aprovada { background: #c7f5d9; }
    .b-rejeitada { background: #f8c7c7; }
    .filters { margin: 12px 0; }
    .filters label { margin-right: 6px; }
  </style>
</head>
<body>
  <h1>Logs de Auditoria — Transferências</h1>
  <p>Exibindo as últimas 200 operações (aprovadas, rejeitadas e pendentes).</p>

  {{-- Filtros simples (opcionais) --}}
  <form method="GET" class="filters">
    <label>Status:
      <select name="status">
        <option value="">(todos)</option>
        <option value="pendente"   {{ request('status')==='pendente' ? 'selected' : '' }}>pendente</option>
        <option value="aprovada"   {{ request('status')==='aprovada' ? 'selected' : '' }}>aprovada</option>
        <option value="rejeitada"  {{ request('status')==='rejeitada' ? 'selected' : '' }}>rejeitada</option>
      </select>
    </label>

    <label>Tipo:
      <select name="tipo">
        <option value="">(todos)</option>
        <option value="local"          {{ request('tipo')==='local' ? 'selected' : '' }}>local</option>
        <option value="interstate"     {{ request('tipo')==='interstate' ? 'selected' : '' }}>interstate</option>
        <option value="international"  {{ request('tipo')==='international' ? 'selected' : '' }}>international</option>
      </select>
    </label>

    <button type="submit">Filtrar</button>
    @if(request('status') || request('tipo'))
      <a href="{{ route('transfer.logs') }}" style="margin-left:8px;">Limpar</a>
    @endif
  </form>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Atleta</th>
        <th>Origem (UF)</th>
        <th>Destino (UF)</th>
        <th>Tipo</th>
        <th>Status</th>
        <th>Decidido por</th>
        <th>Data da decisão</th>
        <th>Motivo (se rejeitada)</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($itens as $t)
        @php
          $statusClass = match($t->status) {
            'aprovada'  => 'b-aprovada',
            'rejeitada' => 'b-rejeitada',
            default     => 'b-pendente'
          };
          $decisor = optional(\App\Models\User::find($t->approved_by))->email ?? '-';
        @endphp
        <tr>
          <td>{{ $t->id }}</td>
          <td>{{ $t->atleta->nome ?? '-' }}</td>
          <td>{{ $t->clubOrigem->nome ?? '-' }} ({{ $t->clubOrigem->federacao->sigla ?? '-' }})</td>
          <td>{{ $t->clubDestino->nome ?? '-' }} ({{ $t->clubDestino->federacao->sigla ?? '-' }})</td>
          <td>{{ $t->tipo }}</td>
          <td><span class="badge {{ $statusClass }}">{{ $t->status }}</span></td>
          <td>{{ $decisor }}</td>
          <td>{{ $t->approved_at ?? '-' }}</td>
          <td>{{ $t->rejection_reason ?? '-' }}</td>
          <td>
            {{-- Só mostra "ver ações" para pendentes --}}
            @if ($t->status === 'pendente')
              <a href="{{ route('transfer.acoes', $t->id) }}">ver ações</a>
            @else
              &mdash;
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="10">Nenhum registro encontrado.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <p style="margin-top:16px;">
    <a href="{{ route('transfer.index.pendentes') }}">← voltar para pendentes</a>
  </p>
</body>
</html>
