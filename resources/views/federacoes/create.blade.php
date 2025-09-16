<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cadastrar Federação</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Cadastrar Federação</h1>

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

  <form method="POST" action="{{ route('federation.store') }}">
    @csrf
    <div>
      <label>Sigla (ex.: MG, RS):</label><br>
      <input type="text" name="sigla" value="{{ old('sigla') }}" maxlength="10" required>
    </div>

    <div style="margin-top:8px;">
      <label>Nome:</label><br>
      <input type="text" name="nome" value="{{ old('nome') }}" maxlength="255" required>
    </div>

    <div style="margin-top:12px;">
      <button type="submit">Salvar</button>
      <a href="{{ route('federation.index') }}">Cancelar</a>
    </div>
  </form>
</body>
</html>
