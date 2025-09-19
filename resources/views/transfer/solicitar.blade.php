<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Solicitar Transferência
        </h2>
    </x-slot>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

          {{-- O action é definido via JS de acordo com o tipo calculado --}}
          <form method="POST" id="form-transfer">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- ORIGEM --}}
              <div class="border rounded-lg p-4">
                <h3 class="font-bold mb-3">Origem</h3>

                <label class="block text-sm font-medium mb-1">Buscar atleta (nome)</label>
                <input type="text" id="busca_atleta" class="w-full border rounded p-2" placeholder="Digite para buscar...">
                <div id="lista_atletas" class="mt-1 hidden"></div>

                <label class="block text-sm font-medium mt-4 mb-1">Federação de origem</label>
                <select id="origem_federacao" class="w-full border rounded p-2" required></select>

                <label class="block text-sm font-medium mt-4 mb-1">Clube de origem</label>
                <select id="origem_clube" name="club_origem_id" class="w-full border rounded p-2" required></select>

                <label class="block text-sm font-medium mt-4 mb-1">Atleta</label>
                <select id="origem_atleta" name="atleta_id" class="w-full border rounded p-2" required></select>
              </div>

              {{-- DESTINO --}}
              <div class="border rounded-lg p-4">
                <h3 class="font-bold mb-3">Destino</h3>

                <label class="block text-sm font-medium mb-1">Buscar clube (nome)</label>
                <input type="text" id="busca_clube" class="w-full border rounded p-2" placeholder="Digite para buscar...">
                <div id="lista_clubes" class="mt-1 hidden"></div>

                <label class="block text-sm font-medium mt-4 mb-1">Federação de destino</label>
                <select id="destino_federacao" class="w-full border rounded p-2" required></select>

                <label class="block text-sm font-medium mt-4 mb-1">Clube de destino</label>
                <select id="destino_clube" name="club_destino_id" class="w-full border rounded p-2" required></select>

                <label class="block text-sm font-medium mt-4 mb-1">Tipo de transferência</label>
                <input type="text" id="tipo_transferencia" class="w-full border rounded p-2 bg-gray-100" readonly>
                <p class="text-xs text-gray-500">Calculado automaticamente (local / interestadual).</p>
              </div>
            </div>

            <div id="msg" class="mt-4 hidden"></div>

            <div class="mt-6 flex gap-3">
              <button type="button" id="btn-enviar" class="px-4 py-2 bg-blue-600 text-white rounded">Protocolar Solicitação</button>
              <button type="reset" class="px-4 py-2 border rounded">Limpar</button>
            </div>
          </form>

        </div>
      </div>
    </div>

    {{-- Scripts (inline para não depender de @stack) --}}
    <script>
    const R = {
      fed:           "{{ route('transfer.ajax.federacoes') }}",
      clubes:        "{{ route('transfer.ajax.clubes') }}",         // ?federacao_id=
      atletas:       "{{ route('transfer.ajax.atletas') }}",        // ?clube_id=
      buscaAtletas:  "{{ route('transfer.ajax.buscar.atletas') }}", // ?q=
      buscaClubes:   "{{ route('transfer.ajax.buscar.clubes') }}",  // ?q=&federacao_id=
    };
    const ROUTES_POST = {
      local:         "{{ route('transfer.request.local') }}",
      interestadual: "{{ route('transfer.request.interstate') }}",
    };

    async function getJSON(url){ const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}); if(!r.ok) throw new Error(url); return r.json(); }
    function fillSelect(el, items, placeholder='Selecione...'){
      el.innerHTML=''; const opt=document.createElement('option'); opt.value=''; opt.textContent=placeholder; el.appendChild(opt);
      items.forEach(i=>{ const o=document.createElement('option'); o.value=i.id; o.textContent=i.nome; el.appendChild(o); });
    }
    function debounce(fn, wait=300){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), wait); }; }
    function flash(msg, ok=true){
      const box=document.getElementById('msg'); box.className = ok ? 'mt-4 p-3 rounded bg-green-100 text-green-800' : 'mt-4 p-3 rounded bg-red-100 text-red-800';
      box.textContent=msg; box.classList.remove('hidden'); setTimeout(()=>box.classList.add('hidden'), 3000);
    }

    const origem_fed=document.getElementById('origem_federacao');
    const origem_clube=document.getElementById('origem_clube');
    const origem_atleta=document.getElementById('origem_atleta');
    const destino_fed=document.getElementById('destino_federacao');
    const destino_clube=document.getElementById('destino_clube');
    const tipo_input=document.getElementById('tipo_transferencia');
    const btn_enviar=document.getElementById('btn-enviar');

    const busca_atleta=document.getElementById('busca_atleta');
    const lista_atletas=document.getElementById('lista_atletas');
    const busca_clube=document.getElementById('busca_clube');
    const lista_clubes=document.getElementById('lista_clubes');

    (async()=>{ const feds=await getJSON(R.fed); fillSelect(origem_fed,feds,'Selecione a federação'); fillSelect(destino_fed,feds,'Selecione a federação'); })();

    origem_fed.addEventListener('change', async e=>{
      const id=e.target.value; fillSelect(origem_clube,[],'Selecione o clube'); fillSelect(origem_atleta,[],'Selecione o atleta');
      if(!id) return; const clubes=await getJSON(`${R.clubes}?federacao_id=${id}`); fillSelect(origem_clube,clubes,'Selecione o clube');
    });
    origem_clube.addEventListener('change', async e=>{
      const id=e.target.value; fillSelect(origem_atleta,[],'Selecione o atleta');
      if(!id) return; const atletas=await getJSON(`${R.atletas}?clube_id=${id}`); fillSelect(origem_atleta,atletas,'Selecione o atleta');
    });

    destino_fed.addEventListener('change', async e=>{
      const id=e.target.value; fillSelect(destino_clube,[],'Selecione o clube');
      if(!id) return; const clubes=await getJSON(`${R.clubes}?federacao_id=${id}`); fillSelect(destino_clube,clubes,'Selecione o clube');
    });

    busca_atleta.addEventListener('input', debounce(async e=>{
      const q=e.target.value.trim(); if(q.length<2){ lista_atletas.classList.add('hidden'); lista_atletas.innerHTML=''; return; }
      const data=await getJSON(`${R.buscaAtletas}?q=${encodeURIComponent(q)}`);
      lista_atletas.innerHTML=''; data.forEach(a=>{
        const b=document.createElement('button'); b.type='button'; b.className='w-full text-left px-3 py-2 border rounded mb-1';
        b.textContent=`${a.nome} — ${a.clube_nome} / ${a.federacao_nome}`;
        b.addEventListener('click', async ()=>{
          origem_fed.value=a.federacao_id; const clubes=await getJSON(`${R.clubes}?federacao_id=${a.federacao_id}`); fillSelect(origem_clube,clubes,'Selecione o clube');
          origem_clube.value=a.clube_id; const atletas=await getJSON(`${R.atletas}?clube_id=${a.clube_id}`); fillSelect(origem_atleta,atletas,'Selecione o atleta'); origem_atleta.value=a.id;
          lista_atletas.classList.add('hidden'); lista_atletas.innerHTML=''; busca_atleta.value=a.nome; calcularTipo();
        });
        lista_atletas.appendChild(b);
      });
      lista_atletas.classList.toggle('hidden', data.length===0);
    },300));

    busca_clube.addEventListener('input', debounce(async e=>{
      const q=e.target.value.trim(); if(q.length<2){ lista_clubes.classList.add('hidden'); lista_clubes.innerHTML=''; return; }
      const data=await getJSON(`${R.buscaClubes}?q=${encodeURIComponent(q)}${destino_fed.value?('&federacao_id='+destino_fed.value):''}`);
      lista_clubes.innerHTML=''; data.forEach(c=>{
        const b=document.createElement('button'); b.type='button'; b.className='w-full text-left px-3 py-2 border rounded mb-1';
        b.textContent=`${c.nome} — ${c.federacao_nome}`;
        b.addEventListener('click', async ()=>{
          destino_fed.value=c.federacao_id; const clubes=await getJSON(`${R.clubes}?federacao_id=${c.federacao_id}`); fillSelect(destino_clube,clubes,'Selecione o clube');
          destino_clube.value=c.id; lista_clubes.classList.add('hidden'); lista_clubes.innerHTML=''; busca_clube.value=c.nome; calcularTipo();
        });
        lista_clubes.appendChild(b);
      });
      lista_clubes.classList.toggle('hidden', data.length===0);
    },300));

    function calcularTipo(){ const o=origem_fed.value, d=destino_fed.value; tipo_input.value = (!o||!d) ? '' : (o===d ? 'local' : 'interestadual'); }
    [origem_fed,destino_fed].forEach(el=>el.addEventListener('change',calcularTipo));

    btn_enviar.addEventListener('click', ()=>{
      calcularTipo(); const tipo=tipo_input.value; if(!tipo) return flash('Selecione origem e destino para calcular o tipo.', false);
      const action=ROUTES_POST[tipo]; if(!action) return flash('Tipo de transferência não suportado.', false);
      const form=document.getElementById('form-transfer'); form.action=action; form.submit();
    });
    </script>
</x-app-layout>
