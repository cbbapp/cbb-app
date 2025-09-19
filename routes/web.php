<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransferController;

// Middlewares Spatie (via FQCN)
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

// Controllers
use App\Http\Controllers\AtletaController;
use App\Http\Controllers\ClubeController;
use App\Http\Controllers\FederacaoController; // <- VOLTANDO para o controller existente
use App\Http\Controllers\TransferAjaxController;
use App\Http\Controllers\Admin\AccessController;

// ==================================================
// HOME (PÚBLICA)
// ==================================================
Route::get('/', function () {
    return 'Página inicial pública';
});

// ==================================================
// DASHBOARD (AUTH + VERIFIED)
// ==================================================
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ==================================================
// ÁREAS RESTRITAS POR PAPEL (exemplos)
// ==================================================
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/admin', fn () => 'Área restrita da CBB (apenas admin)');
});
Route::middleware(['auth', RoleMiddleware::class . ':federacao'])->group(function () {
    Route::get('/federacao', fn () => 'Área restrita da Federação');
});
Route::middleware(['auth', RoleMiddleware::class . ':clube'])->group(function () {
    Route::get('/clube', fn () => 'Área restrita do Clube');
});

// ==================================================
// PERFIL (Breeze)
// ==================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});
require __DIR__ . '/auth.php';

// ==================================================
// TRANSFERÊNCIAS (negócio)
// ==================================================
Route::middleware(['auth'])->group(function () {
    // Solicitações
    Route::post('/transferencias/solicitar/local',
        [TransferController::class, 'requestLocal']
    )->middleware(PermissionMiddleware::class . ':transfer.request.local')
     ->name('transfer.request.local');

    Route::post('/transferencias/solicitar/interestadual',
        [TransferController::class, 'requestInterstate']
    )->middleware(PermissionMiddleware::class . ':transfer.request.interstate')
     ->name('transfer.request.interstate');

    Route::post('/transferencias/solicitar/internacional',
        [TransferController::class, 'requestInternational']
    )->middleware(PermissionMiddleware::class . ':transfer.request.international')
     ->name('transfer.request.international');

    // Form único de solicitação
    Route::get('/transferencias/solicitar',
        [TransferController::class, 'requestForm']
    )->middleware(PermissionMiddleware::class . ':transfer.request.local|transfer.request.interstate|transfer.request.international')
     ->name('transfer.request.form');

    // Aprovações / Rejeição
    Route::post('/transferencias/{id}/aprovar-local',
        [TransferController::class, 'approveLocal']
    )->middleware(PermissionMiddleware::class . ':transfer.approve.local')
     ->whereNumber('id')
     ->name('transfer.approve.local');

    Route::post('/transferencias/{id}/aprovar-interestadual',
        [TransferController::class, 'approveInterstate']
    )->middleware(PermissionMiddleware::class . ':transfer.approve.interstate')
     ->whereNumber('id')
     ->name('transfer.approve.interstate');

    Route::post('/transferencias/{id}/aprovar-internacional',
        [TransferController::class, 'approveInternational']
    )->middleware(PermissionMiddleware::class . ':transfer.approve.international')
     ->whereNumber('id')
     ->name('transfer.approve.international');

    Route::post('/transferencias/{id}/rejeitar',
        [TransferController::class, 'reject']
    )->middleware(PermissionMiddleware::class . ':transfer.reject')
     ->whereNumber('id')
     ->name('transfer.reject');
});

// ==================================================
// CADASTROS (via navegador)
// ==================================================
Route::middleware(['auth'])->group(function () {

    // ===== ATLETAS =====
    Route::get('/atletas', [AtletaController::class, 'index'])
        ->middleware(PermissionMiddleware::class . ':athlete.view')
        ->name('athlete.index');

    Route::get('/atletas/create', [AtletaController::class, 'create'])
        ->middleware(PermissionMiddleware::class . ':athlete.create')
        ->name('athlete.create');

    Route::post('/atletas', [AtletaController::class, 'store'])
        ->middleware(PermissionMiddleware::class . ':athlete.create')
        ->name('athlete.store');

    Route::get('/atletas/{id}', [AtletaController::class, 'show'])
        ->middleware(PermissionMiddleware::class . ':athlete.view')
        ->whereNumber('id')
        ->name('athlete.show');

    Route::delete('/atletas/{id}', [AtletaController::class, 'destroy'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->whereNumber('id')
        ->name('athlete.destroy');

    Route::middleware(RoleMiddleware::class . ':admin')->group(function () {
        Route::get('/atletas/{id}/edit', [AtletaController::class, 'edit'])
            ->whereNumber('id')
            ->name('athlete.edit');
        Route::put('/atletas/{id}', [AtletaController::class, 'update'])
            ->whereNumber('id')
            ->name('athlete.update');
    });

    // ===== CLUBES =====
    Route::get('/clubes', [ClubeController::class, 'index'])
        ->middleware(PermissionMiddleware::class . ':report.view')
        ->name('club.index');

    Route::get('/clubes/create', [ClubeController::class, 'create'])
        ->middleware(PermissionMiddleware::class . ':club.create')
        ->name('club.create');

    Route::post('/clubes', [ClubeController::class, 'store'])
        ->middleware(PermissionMiddleware::class . ':club.create')
        ->name('club.store');

    Route::get('/clubes/{id}', [ClubeController::class, 'show'])
        ->middleware(PermissionMiddleware::class . ':report.view')
        ->whereNumber('id')
        ->name('club.show');

    Route::delete('/clubes/{id}', [ClubeController::class, 'destroy'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->whereNumber('id')
        ->name('club.destroy');

    Route::middleware(RoleMiddleware::class . ':admin')->group(function () {
        Route::get('/clubes/{id}/edit', [ClubeController::class, 'edit'])
            ->whereNumber('id')
            ->name('club.edit');
        Route::put('/clubes/{id}', [ClubeController::class, 'update'])
            ->whereNumber('id')
            ->name('club.update');
    });

    // ===== FEDERAÇÕES ===== (ADMIN)
    Route::get('/federacoes', [FederacaoController::class, 'index'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->name('federation.index');

    // IMPORTANTE: declare "create" antes de qualquer {id} para não conflitar
    Route::get('/federacoes/create', [FederacaoController::class, 'create'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->name('federation.create');

    Route::post('/federacoes', [FederacaoController::class, 'store'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->name('federation.store');

    // Ficha (SHOW)
    Route::get('/federacoes/{id}', [FederacaoController::class, 'show'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->whereNumber('id')
        ->name('federation.show');

    // Edit/Update
    Route::middleware(RoleMiddleware::class . ':admin')->group(function () {
        Route::get('/federacoes/{id}/edit', [FederacaoController::class, 'edit'])
            ->whereNumber('id')
            ->name('federation.edit');
        Route::put('/federacoes/{id}', [FederacaoController::class, 'update'])
            ->whereNumber('id')
            ->name('federation.update');
    });

    // Destroy
    Route::delete('/federacoes/{id}', [FederacaoController::class, 'destroy'])
        ->middleware(RoleMiddleware::class . ':admin')
        ->whereNumber('id')
        ->name('federation.destroy');
});

// ==================================================
// (LEGADO) Rotas GET simples de aprovação (se ainda precisar)
// ==================================================
Route::middleware(['auth', PermissionMiddleware::class . ':transfer.approve.local'])
    ->get('/transfer/local', fn () => 'Aprovar transferência local');

Route::middleware(['auth', PermissionMiddleware::class . ':transfer.approve.interstate'])
    ->get('/transfer/interstate', fn () => 'Aprovar transferência interestadual');

Route::middleware(['auth', PermissionMiddleware::class . ':transfer.approve.international'])
    ->get('/transfer/international', fn () => 'Aprovar transferência internacional');

// ==================================================
// LISTAGEM + AÇÕES (pendentes / ações)
// ==================================================
Route::middleware([
    'auth',
    'permission:transfer.approve.local|transfer.approve.interstate|transfer.approve.international|transfer.reject'
])->group(function () {
    Route::get('/transferencias/pendentes', [TransferController::class, 'indexPending'])
        ->name('transfer.index.pendentes');

    Route::get('/transferencias/{id}/acoes', [TransferController::class, 'showActions'])
        ->whereNumber('id')
        ->name('transfer.acoes');
});

// ==================================================
// LOGS (ADMIN)
// ==================================================
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/transferencias/logs', [TransferController::class, 'logs'])
        ->name('transfer.logs');
});

// ==================================================
// AJAX (ORIGEM/DESTINO)
// ==================================================
Route::middleware(['auth'])->prefix('transferencias/ajax')->name('transfer.ajax.')->group(function () {
    Route::get('federacoes',     [TransferAjaxController::class, 'federacoes'])->name('federacoes');
    Route::get('clubes',         [TransferAjaxController::class, 'clubes'])->name('clubes');
    Route::get('atletas',        [TransferAjaxController::class, 'atletas'])->name('atletas');
    Route::get('buscar-atletas', [TransferAjaxController::class, 'buscarAtletas'])->name('buscar.atletas');
    Route::get('buscar-clubes',  [TransferAjaxController::class, 'buscarClubes'])->name('buscar.clubes');
});

// ==================================================
// PAINEL DE ACESSO — ADMIN
// ==================================================
Route::middleware(['auth', RoleMiddleware::class . ':admin'])
    ->prefix('admin/acesso')
    ->name('admin.access.')
    ->group(function () {
        // Usuários
        Route::get('usuarios',             [AccessController::class, 'usersIndex'])->name('users.index');
        Route::get('usuarios/{user}/edit', [AccessController::class, 'usersEdit'])->name('users.edit');
        Route::put('usuarios/{user}',      [AccessController::class, 'usersUpdate'])->name('users.update');

        // Papéis × Permissões (matriz)
        Route::get('papeis',               [AccessController::class, 'rolesIndex'])->name('roles.index');
        Route::put('papeis',               [AccessController::class, 'rolesUpdate'])->name('roles.update');
    });
