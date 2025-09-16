<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Solicitar Transferência</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Solicitar Transferência</h1>

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

  <form method="POST" id="solicitarForm">
    @csrf

    <div>
      <label>Tipo:</label><br>
      <select id="tipo" name="tipo" required>
        <option value="">Selecione...</option>
        @can('transfer.request.local')
          <option value="local">Local</option>
        @endcan
        @can('transfer.request.interstate')
          <option value="interstate">Interestadual</option>
        @endcan
        @can('transfer.request.international')
          <option value="international">Internacional</option>
        @endcan
      </select>
    </div>

    <div style="margin-top:8px;">
      <label>Atleta:</label><br>
      <select name="atleta_id" required>
        <option value="">Selecione...</option>
        @foreach ($atletas as $a)
          <option value="{{ $a->id }}">{{ $a->nome }}</option>
        @endforeach
      </select>
    </div>

    <div style="margin-top:8px;">
      <label>Clube de Origem:</label><br>
      <select name="club_origem_id" required>
        <option value="">Selecione...</option>
        @foreach ($clubes as $c)
          <option value="{{ $c->id }}">{{ $c->nome }} ({{ $c->federacao->sigla ?? '—' }})</option>
        @endforeach
      </select>
    </div>

    <div style="margin-top:8px;">
      <label>Clube de Destino:</label><br>
      <select name="club_destino_id" required>
        <option value="">Selecione...</option>
        @foreach ($clubes as $c)
          <option value="{{ $c->id }}">{{ $c->nome }} ({{ $c->federacao->sigla ?? '—' }})</option>
        @endforeach
      </select>
    </div>

    <div style="margin-top:12px;">
      <button type="submit">Solicitar</button>
    </div>
  </form>

  <script>
    // Direciona o form para o endpoint correto conforme o tipo
    const form = document.getElementById('solicitarForm');
    const tipo = document.getElementById('tipo');

    function updateAction() {
      const v = tipo.value;
      if (v === 'local') {
        form.action = "{{ route('transfer.request.local') }}";
      } else if (v === 'interstate') {
        form.action = "{{ route('transfer.request.interstate') }}";
      } else if (v === 'international') {
        form.action = "{{ route('transfer.request.international') }}";
      } else {
        form.action = '';
      }
    }

    tipo.addEventListener('change', updateAction);
    // Inicializa se o usuário voltar com erro e campo já selecionado
    updateAction();
  </script>

  <p style="margin-top:16px;">
    <a href="{{ route('transfer.index.pendentes') }}">← voltar para pendentes</a>
  </p>
</body>
</html>
