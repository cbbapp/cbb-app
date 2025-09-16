<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Ações da transferência #{{ $t->id }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>Transferência #{{ $t->id }}</h1>

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

  <ul>
    <li><strong>Atleta:</strong> {{ $t->atleta->nome ?? '-' }}</li>
    <li><strong>Origem:</strong> {{ $t->clubOrigem->nome ?? '-' }} ({{ $t->clubOrigem->federacao->sigla ?? '-' }})</li>
    <li><strong>Destino:</strong> {{ $t->clubDestino->nome ?? '-' }} ({{ $t->clubDestino->federacao->sigla ?? '-' }})</li>
    <li><strong>Tipo:</strong> {{ $t->tipo }}</li>
    <li><strong>Status:</strong> {{ $t->status }}</li>

    {{-- Auditoria básica: quem decidiu e quando --}}
    <li><strong>Aprovado/Rejeitado por:</strong>
      {{ optional(\App\Models\User::find($t->approved_by))->email ?? '-' }}
    </li>
    <li><strong>Data da decisão:</strong> {{ $t->approved_at ?? '-' }}</li>

    {{-- Motivo da rejeição, se houver --}}
    @if ($t->status === 'rejeitada')
      <li><strong>Motivo da rejeição:</strong> {{ $t->rejection_reason }}</li>
    @endif
  </ul>

  <hr>

  @if ($t->status !== 'pendente')
    {{-- Já processada: não mostra nenhuma ação --}}
    <p><em>Esta transferência já foi processada ({{ $t->status }}). Nenhuma ação disponível.</em></p>
  @else
    {{-- Exibe apenas o botão correspondente ao tipo da transferência --}}
    @if ($t->tipo === 'local')
      @can('transfer.approve.local')
        <form method="POST" action="{{ route('transfer.approve.local', $t->id) }}" style="display:inline-block;">
          @csrf
          <button type="submit">Aprovar LOCAL</button>
        </form>
      @endcan
    @endif

    @if ($t->tipo === 'interstate')
      @can('transfer.approve.interstate')
        <form method="POST" action="{{ route('transfer.approve.interstate', $t->id) }}" style="display:inline-block;">
          @csrf
          <button type="submit">Aprovar INTERESTADUAL</button>
        </form>
      @endcan
    @endif

    @if ($t->tipo === 'international')
      @can('transfer.approve.international')
        <form method="POST" action="{{ route('transfer.approve.international', $t->id) }}" style="display:inline-block;">
          @csrf
          <button type="submit">Aprovar INTERNACIONAL</button>
        </form>
      @endcan
    @endif

    <hr>

    {{-- REJEITAR: admin sempre pode; federação só quando LOCAL da própria federação --}}
    @php
      $user = auth()->user();
      $canReject = false;
      if ($user && $user->can('transfer.reject')) {
          if ($user->hasRole('admin')) {
              $canReject = true;
          } elseif ($user->hasRole('federacao')) {
              $fedId = $user->federacao_id ?? null;
              $canReject =
                  $t->tipo === 'local' &&
                  optional($t->clubOrigem)->federacao_id === $fedId &&
                  optional($t->clubDestino)->federacao_id === $fedId;
          }
      }
    @endphp

    @if ($canReject)
      <h3>Rejeitar transferência</h3>
      <form method="POST" action="{{ route('transfer.reject', $t->id) }}">
        @csrf
        <label for="reason"><strong>Motivo da rejeição:</strong></label><br>
        <textarea id="reason" name="reason" rows="3" cols="60" required></textarea><br>
        <button type="submit">Rejeitar</button>
      </form>
    @endif
  @endif

  <p style="margin-top:16px;">
    <a href="{{ route('transfer.index.pendentes') }}">← voltar para lista</a>
  </p>
</body>
</html>
