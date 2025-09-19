<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Clubes - Listagem</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    form.filters { margin-bottom: 12px; padding: 8px; background: #f6f6f6; border: 1px solid #ddd; }
    form.filters select { margin-right: 8px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
  </style>
</head>
<body>
  <h1>Clubes</h1>

  @if (session('ok'))
    <p style="color:green">{{ session('ok') }}</p>
  @endif

  {{-- Filtro por Federação --}}
  <form method="GET" class="filters" action="{{ route('club.index') }}">
    <label for="federacao_id">Federação:</label>
    <select name="federacao_id" id="federacao_id" onchange="this.form.submit()">
      <option value="">Todos</option>
      @foreach ($federacoes as $f)
        <option value="{{ $f->id }}" @selected((string)$federacaoId === (string)$f->id)>
          {{ $f->sigla }} — {{ $f->nome }}
        </option>
      @endforeach
    </select>
    <noscript><button type="submit">Filtrar</button></noscript>
    <a href="{{ route('club.index') }}">Limpar</a>
  </form>

  @can('club.create')
    <p><a href="{{ route('club.create') }}">+ Cadastrar clube</a></p>
  @endcan

  @if ($itens->count() === 0)
    <p>Nenhum clube encontrado.</p>
  @else
    <table>
      <thead>
        <tr>
          <th>Nome</th>
          <th>Federação</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($itens as $c)
          <tr>
            <td>
              <a href="{{ route('athlete.index', ['clube_id' => $c->id]) }}">
                {{ $c->nome }}
              </a>
            </td>
            <td>
              @if ($c->federacao)
                <a href="{{ route('club.index', ['federacao_id' => $c->federacao->id]) }}">
                  {{ $c->federacao->sigla }}
                </a>
              @else
                -
              @endif
            </td>
            <td>
              @role('admin')
                <a href="{{ route('club.edit', $c->id) }}">Editar</a>
                |
                <form method="POST" action="{{ route('club.destroy', $c->id) }}" onsubmit="return confirm('Excluir este clube?');" style="display:inline;">
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
