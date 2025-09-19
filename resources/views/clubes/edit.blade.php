<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Editar Clube</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    label { font-weight: bold; }
    .row { margin-top: 10px; }
  </style>
</head>
<body>
  <h1>Editar Clube</h1>

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

  <form method="POST" action="{{ route('club.update', $clube->id) }}">
    @csrf
    @method('PUT')

    <div class="row">
      <label>Nome do clube:</label><br>
      <input type="text" name="nome" value="{{ old('nome', $clube->nome) }}" required>
    </div>

    <div class="row">
      <label>Federação:</label><br>
      <select name="federacao_id" required>
        @foreach ($federacoes as $f)
          <option value="{{ $f->id }}" @selected(old('federacao_id',$clube->federacao_id)==$f->id)>
            {{ $f->sigla }} — {{ $f->nome }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="row">
      <label>CNPJ:</label><br>
      <input type="text" name="cnpj" id="cnpj" maxlength="18"
             value="{{ old('cnpj', $clube->cnpj_formatado ?? $clube->cnpj) }}" required>
      <small>Ex.: 12.345.678/0001-99</small>
    </div>

    <div class="row">
      <label>Cidade:</label><br>
      <input type="text" name="cidade" value="{{ old('cidade', $clube->cidade) }}" required>
    </div>

    <div class="row">
      <label>Estado (UF):</label><br>
      <input type="text" name="estado" maxlength="2" style="text-transform:uppercase"
             value="{{ old('estado', $clube->estado) }}" required>
    </div>

    <div class="row">
      <label>WhatsApp do responsável (apenas admin visualiza na ficha):</label><br>
      <input type="text" name="whatsapp_admin" id="whatsapp_admin" maxlength="16"
             value="{{ old('whatsapp_admin', $clube->whatsapp_admin) }}" placeholder="(11) 91234-5678">
    </div>

    <div class="row" style="margin-top:12px;">
      <button type="submit">Salvar</button>
      <a href="{{ route('club.index') }}">Cancelar</a>
    </div>
  </form>

  <script>
    function maskCNPJ(v) {
      v = v.replace(/\D/g,"");
      v = v.replace(/^(\d{2})(\d)/,"$1.$2");
      v = v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3");
      v = v.replace(/\.(\d{3})(\d)/,".$1/$2");
      v = v.replace(/(\d{4})(\d)/,"$1-$2");
      return v;
    }
    function maskPhoneBR(v){
      v = v.replace(/\D/g,'');
      if (v.length > 11) v = v.slice(0,11);
      if (v.length > 10) {
        return v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
      }
      return v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3').trim();
    }
    document.addEventListener('DOMContentLoaded', function () {
      const cnpj = document.getElementById('cnpj');
      if (cnpj) cnpj.addEventListener('input', function(){ this.value = maskCNPJ(this.value); });

      const wa = document.getElementById('whatsapp_admin');
      if (wa) wa.addEventListener('input', function(){ this.value = maskPhoneBR(this.value); });
    });
  </script>
</body>
</html>
