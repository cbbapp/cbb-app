<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Atletas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { margin-bottom: 16px; }
    .filtros { display: flex; gap: 12px; margin-bottom: 16px; align-items: end; }
    .card { background:#f9f9f9; border:1px solid #ddd; padding:12px; border-radius:6px; }
    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #ddd; padding:8px; text-align:left; }
    th { background:#f2f2f2; }
    .actions a, .actions button { margin-right:6px; }
    .muted { color:#666; font-size: 12px; }
  </style>
</head>
<body>
  <h1>Atletas</h1>

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

  {{-- Filtros --}}
  <form method="GET" class="filtros card">
    <div>
      <label>Federação</label><br>
      <select name="federacao_id" onchange="this.form.submit()">
        <option value="">Todas</option>
        @foreach($federacoes as $f)
          <option value="{{ $f->id }}" @selected((string)$federacaoId===(string)$f->id)>
            {{ $f->sigla ?? $f->nome }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label>Clube</label><br>
      <select name="clube_id" onchange="this.form.submit()">
        <option value="">Todos</option>
        @foreach($clubes as $c)
          <option value="{{ $c->id }}" @selected((string)$clubeId===(string)$c->id)>
            {{ $c->nome }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <button type="submit">Filtrar</button>
      <a href="{{ route('athlete.index') }}">Limpar</a>
    </div>

    @can('athlete.create')
      <div style="margin-left:auto">
        <a href="{{ route('athlete.create') }}">+ Cadastrar Atleta</a>
      </div>
    @endcan
  </form>

  {{-- >>> NOVO: Ficha do clube selecionado (aparece automaticamente quando há clube filtrado) --}}
  @if(!empty($clubeSelecionado))
    <div class="card" style="margin-bottom:16px;">
      <strong>Ficha do Clube</strong>
      <div class="muted">Estas informações referem-se ao clube atualmente filtrado.</div>
      <div style="margin-top:8px; display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:10px;">
        <div><b>Nome:</b> {{ $clubeSelecionado->nome }}</div>
        <div><b>Entidade (Federação):</b> {{ $clubeSelecionado->federacao->sigla ?? $clubeSelecionado->federacao->nome ?? '-' }}</div>
        <div><b>CNPJ:</b> {{ $clubeSelecionado->cnpj_formatado ?? $clubeSelecionado->cnpj ?? '-' }}</div>
        <div><b>Cidade/UF:</b> {{ $clubeSelecionado->cidade }} / {{ $clubeSelecionado->estado }}</div>
        @role('admin')
          <div><b>WhatsApp (responsável):</b> {{ $clubeSelecionado->whatsapp_admin ?: '-' }}</div>
        @endrole
      </div>
      <div style="margin-top:8px;">
        <a href="{{ route('club.show', $clubeSelecionado->id) }}">Abrir página do clube</a>
        @role('admin') · <a href="{{ route('club.edit', $clubeSelecionado->id) }}">Editar clube</a> @endrole
      </div>
    </div>
  @endif

  {{-- Tabela --}}
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Nome</th>
          <th>Clube</th>
          <th>Federação</th>
          <th style="width:220px;">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($itens as $a)
          <tr>
            <td>
              {{-- NOME CLICÁVEL → FICHA --}}
              <a href="{{ route('athlete.show', $a->id) }}">
                {{ $a->nome }}
              </a>
            </td>
            <td>{{ $a->clube->nome ?? '-' }}</td>
            <td>{{ $a->clube->federacao->sigla ?? $a->clube->federacao->nome ?? '-' }}</td>
            <td class="actions">
              <a href="{{ route('athlete.show', $a->id) }}">Ficha</a>

              @role('admin')
                <a href="{{ route('athlete.edit', $a->id) }}">Editar</a>

                <form method="POST" action="{{ route('athlete.destroy', $a->id) }}" style="display:inline" onsubmit="return confirm('Excluir este atleta?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit">Excluir</button>
                </form>
              @endrole
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4">Nenhum atleta encontrado.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div style="margin-top:12px;">
      {{ $itens->links() }}
    </div>
  </div>

  <p style="margin-top:16px;">
    <a href="{{ route('dashboard') }}">← Voltar ao painel</a>
  </p>
</body>
</html>
