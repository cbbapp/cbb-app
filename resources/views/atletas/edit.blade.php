<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Editar Atleta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { margin-bottom: 16px; }
    .row { margin-bottom: 12px; }
    label { display:block; font-weight: bold; margin-bottom: 4px; }
    input[type="text"], input[type="date"] { width: 320px; max-width: 100%; padding: 6px; }
    .hint { font-size: 12px; color: #555; }

    /* Autocomplete */
    .ac-wrapper { position: relative; width: 420px; max-width: 100%; }
    .ac-list {
      position: absolute; top: 100%; left: 0; right: 0;
      background: #fff; border: 1px solid #ccc; border-radius: 4px;
      max-height: 260px; overflow-y: auto; z-index: 20; margin-top: 2px;
      display: none;
    }
    .ac-item { padding: 8px 10px; cursor: pointer; }
    .ac-item:hover { background: #f3f4f6; }
    .pill {
      display: inline-block; background: #eef2ff; color: #3730a3;
      padding: 4px 8px; border-radius: 9999px; font-size: 12px; margin-top: 4px;
    }
    .actions { margin-top: 12px; }
  </style>
</head>
<body>
  <h1>Editar Atleta</h1>

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

  <form id="form-atleta-edit" method="POST" action="{{ route('athlete.update', $atleta->id) }}">
    @csrf
    @method('PUT')

    <div class="row">
      <label>Nome</label>
      <input type="text" name="nome" value="{{ old('nome', $atleta->nome) }}" required>
    </div>

    <div class="row">
      <label>CPF</label>
      <input
        type="text"
        id="cpf"
        name="cpf"
        inputmode="numeric"
        maxlength="14" {{-- 000.000.000-00 --}}
        value="{{ old('cpf', $atleta->cpf) }}"
        required
      >
      <div class="hint">Digite os números; a máscara será aplicada automaticamente.</div>
    </div>

    <div class="row">
      <label>Data de Nascimento</label>
      <input
        type="date"
        name="data_nascimento"
        value="{{ old('data_nascimento', optional($atleta->data_nascimento)->format('Y-m-d')) }}"
        required
      >
    </div>

    <div class="row">
      <label>Aptidão</label>
      <select name="situacao">
        <option value="apto" @selected(old('situacao', $atleta->situacao) === 'apto')>Apto</option>
        <option value="irregular" @selected(old('situacao', $atleta->situacao) === 'irregular')>Irregular</option>
      </select>
    </div>

    {{-- ======================== BUSCA DE CLUBE (AJAX) ======================== --}}
    <div class="row">
      <label>Clube</label>

      {{-- Valor atual (visual) --}}
      <div id="clubeAtual" class="pill">
        Selecionado: {{ $atleta->clube->nome ?? '-' }}
        @php
          $sigla = $atleta->clube->federacao->sigla ?? ($atleta->clube->federacao->nome ?? null);
        @endphp
        @if($sigla)
          ({{ $sigla }})
        @endif
      </div>

      {{-- Hidden real enviado no POST --}}
      <input type="hidden" id="clube_id" name="clube_id" value="{{ old('clube_id', $atleta->clube_id) }}">

      {{-- Campo de busca/autocomplete --}}
      <div class="ac-wrapper" style="margin-top:6px;">
        <input
          type="text"
          id="clube_busca"
          placeholder="Digite o nome do clube para buscar..."
          autocomplete="off"
          aria-autocomplete="list"
          aria-expanded="false"
          aria-controls="ac-list"
          value=""
        >
        <div id="ac-list" class="ac-list" role="listbox"></div>
      </div>

      <div class="hint">
        Comece a digitar para buscar. Clique em um resultado para selecionar o clube.
      </div>
    </div>
    {{-- ====================== FIM BUSCA DE CLUBE (AJAX) ====================== --}}

    <div class="actions">
      <button type="submit">Salvar</button>
      <a href="{{ route('athlete.index') }}">Cancelar</a>
    </div>
  </form>

  <p style="margin-top:16px;">
    <a href="{{ route('athlete.index') }}">← Voltar à lista</a>
  </p>

  <script>
    // Config do usuário atual (para filtrar por federação quando NÃO for admin)
    const IS_ADMIN = {!! auth()->user()->hasRole('admin') ? 'true' : 'false' !!};
    const FEDERACAO_ID = {!! auth()->user()->federacao_id ? (int)auth()->user()->federacao_id : 'null' !!};

    const input = document.getElementById('clube_busca');
    const list  = document.getElementById('ac-list');
    const hiddenId = document.getElementById('clube_id');
    const lblAtual = document.getElementById('clubeAtual');

    let debounceTimer = null;

    function hideList() {
      list.style.display = 'none';
      input && input.setAttribute('aria-expanded', 'false');
    }

    function showList() {
      list.style.display = 'block';
      input && input.setAttribute('aria-expanded', 'true');
    }

    function renderResults(items) {
      list.innerHTML = '';
      if (!items || !items.length) {
        hideList();
        return;
      }
      items.forEach(it => {
        const div = document.createElement('div');
        div.className = 'ac-item';
        const fed = it.federacao_nome ? ` (${it.federacao_nome})` : '';
        div.textContent = `${it.nome}${fed}`;
        div.setAttribute('role', 'option');
        div.onclick = () => {
          hiddenId.value = it.id;
          input.value = `${it.nome}${fed}`;
          lblAtual.textContent = `Selecionado: ${it.nome}${fed}`;
          hideList();
        };
        list.appendChild(div);
      });
      showList();
    }

    async function buscarClubes(q) {
      if (!q || q.trim().length < 2) {
        hideList();
        return;
      }
      const params = new URLSearchParams({ q: q.trim() });
      if (!IS_ADMIN && FEDERACAO_ID) {
        params.append('federacao_id', String(FEDERACAO_ID));
      }
      try {
        const resp = await fetch(`{{ route('transfer.ajax.buscar.clubes') }}?` + params.toString());
        if (!resp.ok) throw new Error('Falha ao buscar clubes');
        const data = await resp.json();
        renderResults(data);
      } catch (e) {
        console.error(e);
        hideList();
      }
    }

    if (input) {
      input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => buscarClubes(input.value), 220);
      });

      document.addEventListener('click', (ev) => {
        if (!ev.target.closest('.ac-wrapper')) {
          hideList();
        }
      });

      input.addEventListener('keydown', (ev) => {
        if (ev.key === 'Escape') hideList();
      });
    }
  </script>

  {{-- Máscara de CPF + limpeza no submit --}}
  <script>
    function onlyDigits(str) {
      return (str || '').replace(/\D+/g, '');
    }
    function formatCPF(digits) {
      const v = onlyDigits(digits).slice(0, 11);
      const p1 = v.slice(0,3);
      const p2 = v.slice(3,6);
      const p3 = v.slice(6,9);
      const p4 = v.slice(9,11);
      let out = '';
      if (p1) out += p1;
      if (p2) out += (out ? '.' : '') + p2;
      if (p3) out += (out ? '.' : '') + p3;
      if (p4) out += (out ? '-' : '') + p4;
      return out;
    }

    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
      // aplica máscara enquanto digita
      cpfInput.addEventListener('input', (e) => {
        const start = e.target.selectionStart;
        const before = e.target.value;
        e.target.value = formatCPF(e.target.value);

        // manter cursor razoavelmente na posição correta
        const diff = e.target.value.length - before.length;
        e.target.setSelectionRange(start + (diff > 0 ? 1 : 0), start + (diff > 0 ? 1 : 0));
      });

      // ao carregar com valor (sem máscara), aplicamos formato
      cpfInput.value = formatCPF(cpfInput.value);
    }

    // no submit, envia apenas dígitos para passar no size:11 do backend
    const formEdit = document.getElementById('form-atleta-edit');
    if (formEdit) {
      formEdit.addEventListener('submit', () => {
        if (cpfInput) cpfInput.value = onlyDigits(cpfInput.value);
      });
    }
  </script>
</body>
</html>
