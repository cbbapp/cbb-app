<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cadastrar Clube</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Cadastrar Clube</h1>

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

  <form method="POST" action="{{ route('club.store') }}">
    @csrf
    <div>
      <label>Nome do clube:</label><br>
      <input type="text" name="nome" value="{{ old('nome') }}" required>
    </div>

    <div style="margin-top:8px;">
      <label>Federação:</label><br>
      {{-- Para admin: select com todas; para federação: select com apenas 1 opção (a própria) --}}
      <select name="federacao_id" required @if(auth()->user()->hasRole('federacao')) readonly disabled @endif>
        <option value="">Selecione...</option>
        @foreach ($federacoes as $f)
          <option value="{{ $f->id }}" @selected(old('federacao_id')==$f->id)>
            {{ $f->sigla }} — {{ $f->nome }}
          </option>
        @endforeach
      </select>

      {{-- Se o select foi desabilitado (usuário federacao), garanta envio via hidden --}}
      @if(auth()->user()->hasRole('federacao') && $federacoes->count() === 1)
        <input type="hidden" name="federacao_id" value="{{ $federacoes->first()->id }}">
      @endif
    </div>

    <div style="margin-top:12px;">
      <button type="submit">Salvar</button>
      <a href="{{ route('club.index') }}">Cancelar</a>
    </div>
  </form>
</body>
</html>
