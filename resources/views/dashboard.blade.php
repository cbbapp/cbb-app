<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Painel do Sistema CBB</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { margin-bottom: 20px; }
    .menu { display: flex; flex-wrap: wrap; gap: 16px; }
    .card {
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 12px;
      width: 250px;
      background: #f9f9f9;
    }
    .card h3 { margin-top: 0; }
    .card ul { padding-left: 20px; margin: 0; }
    .card ul li { margin-bottom: 6px; }
  </style>
</head>
<body>
  <h1>Bem-vindo, {{ auth()->user()->name }}</h1>

  <div class="menu">

    {{-- ===== ATLETAS ===== --}}
    @canany(['athlete.create','athlete.view'])
    <div class="card">
      <h3>Atletas</h3>
      <ul>
        @can('athlete.create')
          <li><a href="{{ route('athlete.create') }}">Cadastrar Atleta</a></li>
        @endcan
        @can('athlete.view')
          <li><a href="{{ route('athlete.index') }}">Listar Atletas</a></li>
        @endcan
      </ul>
    </div>
    @endcanany

    {{-- ===== CLUBES ===== --}}
    @canany(['club.create'])
    <div class="card">
      <h3>Clubes</h3>
      <ul>
        @can('club.create')
          <li><a href="{{ route('club.create') }}">Cadastrar Clube</a></li>
        @endcan
        <li><a href="{{ route('club.index') }}">Listar Clubes</a></li>
      </ul>
    </div>
    @endcanany

    {{-- ===== FEDERAÇÕES ===== --}}
    @canany(['federation.create'])
    <div class="card">
      <h3>Federações</h3>
      <ul>
        @can('federation.create')
          <li><a href="{{ route('federation.create') }}">Cadastrar Federação</a></li>
        @endcan
        <li><a href="{{ route('federation.index') }}">Listar Federações</a></li>
      </ul>
    </div>
    @endcanany

    {{-- ===== TRANSFERÊNCIAS ===== --}}
    @canany(['transfer.request.local','transfer.request.interstate','transfer.request.international'])
    <div class="card">
      <h3>Transferências</h3>
      <ul>
        <li><a href="{{ route('transfer.request.form') }}">Solicitar Transferência</a></li>
        <li><a href="{{ route('transfer.index.pendentes') }}">Pendentes</a></li>
        @role('admin')
          <li><a href="{{ route('transfer.logs') }}">Logs/Auditoria</a></li>
        @endrole
      </ul>
    </div>
    @endcanany

    {{-- ===== ACESSO (ADMIN) ===== --}}
    @role('admin')
    <div class="card">
      <h3>Acesso (Admin)</h3>
      <ul>
        <li><a href="{{ route('admin.access.users.index') }}">Gerenciar Usuários</a></li>
        <li><a href="{{ route('admin.access.roles.index') }}">Papéis × Permissões</a></li>
      </ul>
    </div>
    @endrole

  </div>

  <p style="margin-top:30px;">
    <a href="{{ route('profile.edit') }}">Editar Perfil</a> |
    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
      @csrf
      <button type="submit">Sair</button>
    </form>
  </p>
</body>
</html>
