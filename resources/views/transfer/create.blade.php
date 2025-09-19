@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Solicitar Transferência</h1>

  <form method="POST" action="{{ route('transferencias.store') }}" id="form-transfer">
    @csrf

    <div class="row g-4">
      {{-- ORIGEM --}}
      <div class="col-md-6">
        <div class="card">
          <div class="card-header fw-bold">Origem</div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Buscar atleta (nome)</label>
              <input type="text" class="form-control" id="busca_atleta" placeholder="Digite para buscar...">
              <div class="list-group mt-1 d-none" id="lista_atletas"></div>
            </div>

            <div class="mb-3">
              <label class="form-label">Federação de origem</label>
              <select class="form-select" id="origem_federacao" name="origem_federacao_id"></select>
            </div>

            <div class="mb-3">
              <label class="form-label">Clube de origem</label>
              <select class="form-select" id="origem_clube" name="origem_clube_id"></select>
            </div>

            <div class="mb-3">
              <label class="form-label">Atleta</label>
              <select class="form-select" id="origem_atleta" name="atleta_id"></select>
            </div>
          </div>
        </div>
      </div>

      {{-- DESTINO --}}
      <div class="col-md-6">
        <div class="card">
          <div class="card-header fw-bold">Destino</div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Buscar clube (nome)</label>
              <input type="text" class="form-control" id="busca_clube" placeholder="Digite para buscar...">
              <div class="list-group mt-1 d-none" id="lista_clubes"></div>
            </div>

            <div class="mb-3">
              <label class="form-label">Federação de destino</label>
              <select class="form-select" id="destino_federacao" name="destino_federacao_id"></select>
            </div>

            <div class="mb-3">
              <label class="form-label">Clube de destino</label>
              <select class="form-select" id="destino_clube" name="destino_clube_id"></select>
            </div>

            <div class="mb-3">
              <label class="form-label">Tipo de transferência</label>
              <input type="text" class="form-control" id="tipo_transferencia" name="tipo_transferencia" readonly>
              <div class="form-text">Calculado automaticamente (local / interestadual / internacional).</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4">
      <button type="submit" class="btn btn-primary">Protocolar Solicitação</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
const R = {
  fed:  "{{ route('transfer.ajax.federacoes') }}",
  clubes: "{{ route('transfer.ajax.clubes') }}",         // ?federacao_id=
  atletas: "{{ route('transfer.ajax.atletas') }}",       // ?clube_id=
  buscaAtletas: "{{ route('transfer.ajax.buscar.atletas') }}", // ?q=
  buscaClubes:  "{{ route('transfer.ajax.buscar.clubes') }}",  // ?q=&federacao_id=
};

async function getJSON(url) {
  const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
  if (!res.ok) throw new Error('Erro ao carregar');
  return res.json();
}

function fillSelect(el, items, placeholder='Selecione...') {
  el.innerHTML = '';
  const opt = document.createElement('option');
  opt.value = ''; opt.textContent = placeholder;
  el.appendChild(opt);
  items.forEach(i => {
    const o = document.createElement('option');
    o.value = i.id; o.textContent = i.nome;
    el.appendChild(o);
  });
}

function debounce(fn, wait=300){
  let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), wait); };
}

// ELEMENTOS
const origem_fed   = document.getElementById('origem_federacao');
const origem_clube = document.getElementById('origem_clube');
const origem_atleta= document.getElementById('origem_atleta');
const destino_fed  = document.getElementById('destino_federacao');
const destino_clube= document.getElementById('destino_clube');
const tipo_input   = document.getElementById('tipo_transferencia');

const busca_atleta = document.getElementById('busca_atleta');
const lista_atletas= document.getElementById('lista_atletas');
const busca_clube  = document.getElementById('busca_clube');
const lista_clubes = document.getElementById('lista_clubes');

// CARREGAR FEDERAÇÕES INICIAIS (origem e destino)
(async () => {
  const feds = await getJSON(R.fed);
  fillSelect(origem_fed, feds, 'Selecione a federação');
  fillSelect(destino_fed, feds, 'Selecione a federação');
})();

// ENCADEAMENTO ORIGEM
origem_fed.addEventListener('change', async (e)=>{
  const id = e.target.value;
  fillSelect(origem_clube, [], 'Selecione o clube');
  fillSelect(origem_atleta, [], 'Selecione o atleta');
  if(!id) return;
  const clubes = await getJSON(`${R.clubes}?federacao_id=${id}`);
  fillSelect(origem_clube, clubes, 'Selecione o clube');
});

origem_clube.addEventListener('change', async (e)=>{
  const id = e.target.value;
  fillSelect(origem_atleta, [], 'Selecione o atleta');
  if(!id) return;
  const atletas = await getJSON(`${R.atletas}?clube_id=${id}`);
  fillSelect(origem_atleta, atletas, 'Selecione o atleta');
});

// ENCADEAMENTO DESTINO
destino_fed.addEventListener('change', async (e)=>{
  const id = e.target.value;
  fillSelect(destino_clube, [], 'Selecione o clube');
  if(!id) return;
  const clubes = await getJSON(`${R.clubes}?federacao_id=${id}`);
  fillSelect(destino_clube, clubes, 'Selecione o clube');
});

// BUSCA ATLETA (preenche fed/clube/atleta de ORIGEM automaticamente)
busca_atleta.addEventListener('input', debounce(async (e)=>{
  const q = e.target.value.trim();
  if(q.length < 2){ lista_atletas.classList.add('d-none'); lista_atletas.innerHTML=''; return; }
  const data = await getJSON(`${R.buscaAtletas}?q=${encodeURIComponent(q)}`);
  lista_atletas.innerHTML = '';
  data.forEach(a=>{
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'list-group-item list-group-item-action';
    item.textContent = `${a.nome} — ${a.clube_nome} / ${a.federacao_nome}`;
    item.addEventListener('click', async ()=>{
      // set federacao
      origem_fed.value = a.federacao_id;
      // carregar clubes dessa fed
      const clubes = await getJSON(`${R.clubes}?federacao_id=${a.federacao_id}`);
      fillSelect(origem_clube, clubes, 'Selecione o clube');
      origem_clube.value = a.clube_id;
      // carregar atletas do clube
      const atletas = await getJSON(`${R.atletas}?clube_id=${a.clube_id}`);
      fillSelect(origem_atleta, atletas, 'Selecione o atleta');
      origem_atleta.value = a.id;

      lista_atletas.classList.add('d-none');
      lista_atletas.innerHTML = '';
      busca_atleta.value = a.nome;
      calcularTipo();
    });
    lista_atletas.appendChild(item);
  });
  lista_atletas.classList.toggle('d-none', data.length===0);
}, 300));

// BUSCA CLUBE DESTINO (preenche fed/clube de DESTINO)
busca_clube.addEventListener('input', debounce(async (e)=>{
  const q = e.target.value.trim();
  if(q.length < 2){ lista_clubes.classList.add('d-none'); lista_clubes.innerHTML=''; return; }
  const data = await getJSON(`${R.buscaClubes}?q=${encodeURIComponent(q)}${destino_fed.value?('&federacao_id='+destino_fed.value):''}`);
  lista_clubes.innerHTML = '';
  data.forEach(c=>{
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'list-group-item list-group-item-action';
    item.textContent = `${c.nome} — ${c.federacao_nome}`;
    item.addEventListener('click', async ()=>{
      destino_fed.value = c.federacao_id;
      const clubes = await getJSON(`${R.clubes}?federacao_id=${c.federacao_id}`);
      fillSelect(destino_clube, clubes, 'Selecione o clube');
      destino_clube.value = c.id;

      lista_clubes.classList.add('d-none');
      lista_clubes.innerHTML = '';
      busca_clube.value = c.nome;
      calcularTipo();
    });
    lista_clubes.appendChild(item);
  });
  lista_clubes.classList.toggle('d-none', data.length===0);
}, 300));

// Calcula tipo local/interestadual/internacional (ajuste se tiver país em Federacao)
function calcularTipo(){
  const fedOrig = origem_fed.value;
  const fedDest = destino_fed.value;
  if(!fedOrig || !fedDest){ tipo_input.value = ''; return; }
  tipo_input.value = (fedOrig === fedDest) ? 'local' : 'interestadual'; // se futuramente tiver país, trate 'internacional' aqui
}

// Atualiza tipo quando o usuário muda selects
[origem_fed, destino_fed].forEach(el => el.addEventListener('change', calcularTipo));
</script>
@endpush
