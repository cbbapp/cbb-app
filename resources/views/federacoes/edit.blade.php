<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Editar Federação</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; margin:20px; }
    .row { margin-bottom: 10px; }
    label { display:block; font-weight:600; margin-bottom:4px; }
    input[type="text"], input[type="email"], input[type="url"] { width:360px; max-width:100%; padding:6px; }
    .hint { font-size:12px; color:#555; }
  </style>
</head>
<body>
  <h1>Editar Federação</h1>

  @if ($errors->any())
    <div style="color:red">
      @foreach ($errors->all() as $e)
        <div>{{ $e }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('federation.update', $federacao->id) }}">
    @csrf
    @method('PUT')

    <div class="row">
      <label>Nome da Entidade</label>
      <input type="text" name="nome" value="{{ old('nome', $federacao->nome) }}" required>
    </div>

    <div class="row">
      <label>Sigla</label>
      <input type="text" name="sigla" value="{{ old('sigla', $federacao->sigla) }}" required>
    </div>

    <div class="row">
      <label>CNPJ</label>
      <input type="text" name="cnpj" id="cnpj" maxlength="18"
             value="{{ old('cnpj', $federacao->cnpj_formatado ?? $federacao->cnpj) }}"
             required placeholder="00.000.000/0000-00" autocomplete="off" inputmode="numeric">
      <div class="hint">Será salvo apenas com dígitos. Formatação automática no campo.</div>
    </div>

    <div class="row">
      <label>Presidente</label>
      <input type="text" name="presidente" value="{{ old('presidente', $federacao->presidente) }}" required>
    </div>

    <div class="row">
      <label>Site (opcional)</label>
      <input type="text" name="site" value="{{ old('site', $federacao->site) }}">
    </div>

    <div class="row">
      <label>E-mail (opcional)</label>
      <input type="email" name="email" value="{{ old('email', $federacao->email) }}">
    </div>

    <div class="row">
      <label>Telefone (opcional)</label>
      <input type="text" name="telefone" value="{{ old('telefone', $federacao->telefone) }}">
    </div>

    <div class="row">
      <button type="submit">Salvar</button>
      <a href="{{ route('federation.show', $federacao->id) }}">Cancelar</a>
    </div>
  </form>

  <script>
    // Máscara simples do CNPJ (##.###.###/####-##)
    const cnpj = document.getElementById('cnpj');
    if (cnpj) {
      const mask = (val) => {
        let v = (val || '').replace(/\D+/g,'').slice(0,14);
        if (v.length >= 3 && v.length <= 5) v = v.replace(/^(\d{2})(\d{0,3})/, '$1.$2');
        if (v.length >= 6 && v.length <= 8) v = v.replace(/^(\d{2})(\d{3})(\d{0,3})/, '$1.$2.$3');
        if (v.length >= 9 && v.length <= 12) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4})/, '$1.$2.$3/$4');
        if (v.length >= 13) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2}).*/, '$1.$2.$3/$4-$5');
        return v;
      };
      cnpj.addEventListener('input', () => {
        const pos = cnpj.selectionStart;
        const before = cnpj.value;
        cnpj.value = mask(cnpj.value);
        const delta = cnpj.value.length - before.length;
        cnpj.setSelectionRange(pos + delta, pos + delta);
      });
      cnpj.value = mask(cnpj.value);
    }
  </script>
</body>
</html>
