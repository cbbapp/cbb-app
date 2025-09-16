<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cadastrar Atleta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Cadastrar Atleta</h1>

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

  <form method="POST" action="{{ route('athlete.store') }}">
    @csrf
    <div>
      <label>Nome do atleta:</label><br>
      <input type="text" name="nome" value="{{ old('nome') }}" required>
    </div>

    <div style="margin-top:8px;">
      <label>Clube:</label><br>
      <select name="clube_id" required @if(auth()->user()->hasRole('clube')) disabled @endif>
        <option value="">Selecione...</option>
        @foreach ($clubes as $c)
          <option value="{{ $c->id }}" @selected(old('clube_id')==$c->id)>
            {{ $c->nome }} ({{ $c->federacao->sigla ?? '-' }})
          </option>
        @endforeach
      </select>

      {{-- Se for clube, envia hidden --}}
      @if(auth()->user()->hasRole('clube') && $clubes->count() === 1)
        <input type="hidden" name="clube_id" value="{{ $clubes->first()->id }}">
      @endif
    </div>

    <div style="margin-top:12px;">
      <button type="submit">Salvar</button>
      <a href="{{ route('athlete.index') }}">Cancelar</a>
    </div>
  </form>
</body>
</html>
