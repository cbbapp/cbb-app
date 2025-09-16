<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransferController;

// === Importa os middlewares do Spatie por FQCN (sem precisar do Kernel) ===
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

// === Controllers de cadastros (novos) ===
use App\Http\Controllers\AtletaController;
use App\Http\Controllers\ClubeController;
use App\Http\Controllers\FederacaoController;


// ==================================================
// PÁGINA INICIAL (PÚBLICA)
// ==================================================
Route::get('/', function () {
    return 'Página inicial pública';
});


// ==================================================
// DASHBOARD (SOMENTE USUÁRIO AUTENTICADO + VERIFICADO)
// ==================================================
// Ao logar, todos os usuários (admin, federação, clube) cairão aqui.
// A view `resources/views/dashboard.blade.php` mostra links diferentes
// conforme as permissões do usuário logado.
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// ==================================================
// ÁREAS RESTRITAS POR PAPEL (ROLE)
// ==================================================
// Essas rotas são apenas ilustrativas para testar restrições de role.
// O acesso é controlado pelo middleware RoleMiddleware.
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/admin', function () {
        return 'Área restrita da CBB (apenas admin)';
    });
});

Route::middleware(['auth', RoleMiddleware::class . ':federacao'])->group(function () {
    Route::get('/federacao', function () {
        return 'Área restrita da Federação';
    });
});

Route::middleware(['auth', RoleMiddleware::class . ':clube'])->group(function () {
    Route::get('/clube', function () {
        return 'Área restrita do Clube';
    });
});


// ==================================================
// ROTAS DE PERFIL (Breeze) - JÁ EXISTENTES
// ==================================================
// Rotas padrão do Laravel Breeze (editar, atualizar e excluir perfil).
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Importa as rotas de autenticação do Breeze (login, registro, etc.)
require __DIR__ . '/auth.php';


// ==================================================
// NEGÓCIO: TRANSFERÊNCIAS (com FQCN dos middlewares)
// ==================================================
// Aqui ficam todas as rotas para solicitações, aprovações e rejeição de transferências.
// Usamos PermissionMiddleware para controlar permissões granulares.
Route::middleware(['auth'])->group(function () {

    // -------------------------
    // SOLICITAÇÕES DE TRANSFERÊNCIA (POSTs existentes)
    // -------------------------

    // LOCAL (mesma federação)
    Route::post('/transferencias/solicitar/local',
        [TransferController::class, 'requestLocal']
    )->middleware(PermissionMiddleware::class . ':transfer.request.local')
     ->name('transfer.request.local');

    // INTERESTADUAL (federações diferentes)
    Route::post('/transferencias/solicitar/interestadual',
        [TransferController::class, 'requestInterstate']
    )->middleware(PermissionMiddleware::class . ':transfer.request.interstate')
     ->name('transfer.request.interstate');

    // INTERNACIONAL
    Route::post('/transferencias/solicitar/internacional',
        [TransferController::class, 'requestInternational']
    )->middleware(PermissionMiddleware::class . ':transfer.request.international')
     ->name('transfer.request.international');

    // -------------------------
    // FORM ÚNICO (GET) PARA SOLICITAR TRANSFERÊNCIA
    // -------------------------
    // Exibe formulário único. O POST é enviado para a rota específica
    // conforme o tipo selecionado (local / interestadual / internacional).
    Route::get('/transferencias/solicitar',
        [TransferController::class, 'requestForm']
    )->middleware(PermissionMiddleware::class . ':transfer.request.local|transfer.request.interstate|transfer.request.international')
     ->name('transfer.request.form');

    // -------------------------
    // APROVAÇÕES / REJEIÇÃO DE TRANSFERÊNCIA
    // -------------------------

    // Aprovar LOCAL:
    // - admin aprova qualquer local
    // - federacao aprova local somente se origem e destino forem da SUA federação
    Route::post('/transferencias/{id}/aprovar-local',
        [TransferController::class, 'approveLocal']
    )->middleware(PermissionMiddleware::class . ':transfer.approve.local')
     ->name('transfer.approve.local');

    // Aprovar INTERESTADUAL: somente admin
    Route::post('/transferencias/{id}/aprovar-interestadual',
        [TransferController::class, 'approveInterstate']
    )->middleware(PermissionMiddleware::class . ':transfer.approve.interstate')
     ->name('transfer.approve.interstate');

    // Aprovar INTERNACIONAL: somente admin
    Route::post('/transferencias/{id}/aprovar-internacional',
        [TransferController::class, 'approveInternational']
    )->middleware(PermissionMiddleware::class . ':transfer.approve.international')
     ->name('transfer.approve.international');

    // Rejeitar:
    // - admin pode rejeitar qualquer
    // - federação só pode rejeitar local da própria federação (checagem no Controller)
    Route::post('/transferencias/{id}/rejeitar',
        [TransferController::class, 'reject']
    )->middleware(PermissionMiddleware::class . ':transfer.reject')
     ->name('transfer.reject');
});


// ==================================================
// CADASTROS VIA NAVEGADOR (LISTA / CREATE / DELETE)
// ==================================================
// Aqui ficam os cadastros que agora podem ser feitos via navegador,
// liberados conforme role/permissão.
Route::middleware(['auth'])->group(function () {

    // ===== ATLETAS =====
    // - Clube: pode criar e listar apenas seus atletas
    // - Federação: pode criar/listar atletas dos clubes da sua federação
    // - Admin: pode tudo
    Route::get('/atletas', [AtletaController::class, 'index'])
        ->middleware(PermissionMiddleware::class . ':athlete.view')
        ->name('athlete.index');

    Route::get('/atletas/create', [AtletaController::class, 'create'])
        ->middleware(PermissionMiddleware::class . ':athlete.create')
        ->name('athlete.create');

    Route::post('/atletas', [AtletaController::class, 'store'])
        ->middleware(PermissionMiddleware::class . ':athlete.create')
        ->name('athlete.store');

    Route::delete('/atletas/{id}', [AtletaController::class, 'destroy'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->name('athlete.destroy');


    // ===== CLUBES =====
    // - Federação: pode criar clubes apenas dentro da sua federação
    // - Admin: pode criar em qualquer federação
    Route::get('/clubes', [ClubeController::class, 'index'])
        ->middleware(PermissionMiddleware::class . ':report.view')
        ->name('club.index');

    Route::get('/clubes/create', [ClubeController::class, 'create'])
        ->middleware(PermissionMiddleware::class . ':club.create')
        ->name('club.create');

    Route::post('/clubes', [ClubeController::class, 'store'])
        ->middleware(PermissionMiddleware::class . ':club.create')
        ->name('club.store');

    Route::delete('/clubes/{id}', [ClubeController::class, 'destroy'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->name('club.destroy');


    // ===== FEDERAÇÕES =====
    // - Apenas Admin pode gerenciar federações
    Route::get('/federacoes', [FederacaoController::class, 'index'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->name('federation.index');

    Route::get('/federacoes/create', [FederacaoController::class, 'create'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->name('federation.create');

    Route::post('/federacoes', [FederacaoController::class, 'store'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->name('federation.store');

    Route::delete('/federacoes/{id}', [FederacaoController::class, 'destroy'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->name('federation.destroy');
});


// ==================================================
// (LEGADO) Rotas GET simples que você tinha para aprovações
// Mantidas abaixo apenas se ainda forem úteis para teste rápido.
// Caso não use, pode remover para ficar só com os POST acima.
// ==================================================
Route::middleware(['auth', PermissionMiddleware::class . ':transfer.approve.local'])
    ->get('/transfer/local', fn () => 'Aprovar transferência local');

Route::middleware(['auth', PermissionMiddleware::class . ':transfer.approve.interstate'])
    ->get('/transfer/interstate', fn () => 'Aprovar transferência interestadual');

Route::middleware(['auth', PermissionMiddleware::class . ':transfer.approve.international'])
    ->get('/transfer/international', fn () => 'Aprovar transferência internacional');


// ==================================================
// LISTAGEM + TELA DE AÇÕES
// ==================================================
// Exibe transferências pendentes e tela de ações (aprovar/rejeitar).
// Protegido para quem tem ao menos uma permissão de aprovação ou rejeição.
Route::middleware([
    'auth',
    'permission:transfer.approve.local|transfer.approve.interstate|transfer.approve.international|transfer.reject'
])->group(function () {
    Route::get('/transferencias/pendentes', [TransferController::class, 'indexPending'])
        ->name('transfer.index.pendentes');

    Route::get('/transferencias/{id}/acoes', [TransferController::class, 'showActions'])
        ->name('transfer.acoes');
});


// ==================================================
// LOGS DE AUDITORIA (APENAS ADMIN)
// ==================================================
// Exibe últimos registros de auditoria de transferências.
// Apenas admin tem acesso.
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/transferencias/logs', [TransferController::class, 'logs'])
        ->name('transfer.logs');
});
