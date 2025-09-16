<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\Atleta;
use App\Models\Clube;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    // ===================== LISTAGEM / VISUALIZAÇÃO =====================

    /**
     * Lista transferências pendentes.
     * Admin verá todas; federação verá as locais da sua federação (regra aplicada na aprovação).
     */
    public function indexPending(Request $request)
    {
        $pendentes = Transferencia::with([
            'atleta', 'clubOrigem.federacao', 'clubDestino.federacao'
        ])->where('status', 'pendente')->latest()->paginate(15);

        return view('transfer.listar', compact('pendentes'));
    }

    /**
     * Tela com informações de uma transferência específica e botões de ação.
     */
    public function showActions($id)
    {
        $t = Transferencia::with([
            'atleta', 'clubOrigem.federacao', 'clubDestino.federacao'
        ])->findOrFail($id);

        return view('transfer.acoes', compact('t'));
    }

    // ===================== FORM (GET) DE SOLICITAÇÃO =====================

    /**
     * Exibe o formulário único para solicitar transferência.
     * O POST efetivo é direcionado para a rota correta conforme o tipo selecionado (local/interstate/international).
     */
    public function requestForm()
    {
        $atletas = Atleta::orderBy('nome')->get();
        $clubes  = Clube::with('federacao')->orderBy('nome')->get();

        return view('transfer.solicitar', compact('atletas', 'clubes'));
    }

    // ===================== SOLICITAÇÕES (POST) =====================

    public function requestLocal(Request $request)
    {
        $data = $this->validateRequest($request);
        $clubOrigem  = Clube::findOrFail($data['club_origem_id']);
        $clubDestino = Clube::findOrFail($data['club_destino_id']);

        if ($clubOrigem->federacao_id !== $clubDestino->federacao_id) {
            return back()->withErrors('Transferência não é local (federações diferentes).');
        }

        $transfer = $this->storeTransfer($data, 'local');
        return redirect()->route('transfer.acoes', $transfer->id)->with('ok', 'Solicitação criada.');
    }

    public function requestInterstate(Request $request)
    {
        $data = $this->validateRequest($request);
        $clubOrigem  = Clube::findOrFail($data['club_origem_id']);
        $clubDestino = Clube::findOrFail($data['club_destino_id']);

        if ($clubOrigem->federacao_id === $clubDestino->federacao_id) {
            return back()->withErrors('Transferência não é interestadual (mesma federação).');
        }

        $transfer = $this->storeTransfer($data, 'interstate');
        return redirect()->route('transfer.acoes', $transfer->id)->with('ok', 'Solicitação criada.');
    }

    public function requestInternational(Request $request)
    {
        $data = $this->validateRequest($request);
        $transfer = $this->storeTransfer($data, 'international');
        return redirect()->route('transfer.acoes', $transfer->id)->with('ok', 'Solicitação criada.');
    }

    protected function validateRequest(Request $request): array
    {
        $data = $request->validate([
            'atleta_id'       => ['required','integer','exists:atletas,id'],
            'club_origem_id'  => ['required','integer','exists:clubes,id'],
            'club_destino_id' => ['required','integer','exists:clubes,id'],
        ]);

        $atleta = Atleta::findOrFail($data['atleta_id']);
        if ((int)$atleta->clube_id !== (int)$data['club_origem_id']) {
            abort(422, 'Atleta não pertence ao clube de origem informado.');
        }

        return $data;
    }

    protected function storeTransfer(array $data, string $tipo)
    {
        return Transferencia::create([
            'atleta_id'       => $data['atleta_id'],
            'club_origem_id'  => $data['club_origem_id'],
            'club_destino_id' => $data['club_destino_id'],
            'tipo'            => $tipo,
            'status'          => 'pendente',
        ]);
    }

    // ===================== APROVAÇÕES =====================

    public function approveLocal($id)
    {
        $transfer = Transferencia::with(['atleta','clubOrigem.federacao','clubDestino.federacao'])->findOrFail($id);
        $user = auth()->user();

        // ✅ Sempre garantir que o tipo é 'local'
        if ($transfer->tipo !== 'local') {
            abort(422, 'Tipo da transferência não é local.');
        }

        // Admin aprova qualquer local
        if ($user->hasRole('admin')) {
            return $this->approveAndMove($transfer, 'local (admin)');
        }

        // Federação: só se for LOCAL e ambos os clubes forem da SUA federação
        if ($user->hasRole('federacao')) {
            $fedId = $user->federacao_id;
            $isLocalThisFederation =
                $transfer->clubOrigem->federacao_id === $fedId &&
                $transfer->clubDestino->federacao_id === $fedId;

            if ($isLocalThisFederation) {
                return $this->approveAndMove($transfer, 'local (federação)');
            }

            abort(403, 'Você só pode aprovar transferências locais da sua federação.');
        }

        abort(403, 'Sem permissão para aprovar esta transferência.');
    }

    public function approveInterstate($id)
    {
        $transfer = Transferencia::with(['atleta'])->findOrFail($id);
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            if ($transfer->tipo !== 'interstate') {
                abort(422, 'Tipo da transferência não é interestadual.');
            }
            return $this->approveAndMove($transfer, 'interestadual (admin)');
        }

        abort(403, 'Somente o admin da CBB pode aprovar transferências interestaduais.');
    }

    public function approveInternational($id)
    {
        $transfer = Transferencia::with(['atleta'])->findOrFail($id);
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            if ($transfer->tipo !== 'international') {
                abort(422, 'Tipo da transferência não é internacional.');
            }
            return $this->approveAndMove($transfer, 'internacional (admin)');
        }

        abort(403, 'Somente o admin da CBB pode aprovar transferências internacionais.');
    }

    // ===================== REJEIÇÃO =====================

    /**
     * Rejeita uma transferência com motivo obrigatório.
     * - admin: pode rejeitar qualquer tipo
     * - federacao: só pode rejeitar LOCAL da própria federação (mesma regra da aprovação)
     */
    public function reject(Request $request, $id)
    {
        $transfer = Transferencia::with(['atleta','clubOrigem.federacao','clubDestino.federacao'])->findOrFail($id);
        $user = auth()->user();

        // Valida motivo obrigatório
        $data = $request->validate([
            'reason' => ['required','string','min:3','max:2000'],
        ]);

        if ($transfer->status !== 'pendente') {
            return back()->with('ok', 'Transferência já processada.');
        }

        // Escopo da federação (só rejeita LOCAL da própria federação)
        if ($user->hasRole('federacao')) {
            $fedId = $user->federacao_id;
            $isLocalThisFederation =
                $transfer->tipo === 'local' &&
                $transfer->clubOrigem->federacao_id === $fedId &&
                $transfer->clubDestino->federacao_id === $fedId;

            if (! $isLocalThisFederation) {
                abort(403, 'Você só pode rejeitar transferências locais da sua federação.');
            }
        }

        // Admin pode rejeitar qualquer uma; federação passou pela validação acima
        $transfer->update([
            'status'           => 'rejeitada',
            'rejection_reason' => $data['reason'],
            'approved_by'      => $user->id,   // quem tomou a decisão
            'approved_at'      => now(),       // quando
        ]);

        return back()->with('ok', 'Transferência rejeitada com sucesso.');
    }

    // ===================== LOGS (somente rota admin) =====================

    /**
     * Exibe os logs/auditoria das transferências (últimos 200 registros),
     * com filtros opcionais por status e tipo. A rota é protegida por role:admin.
     */
    public function logs(Request $request)
    {
        $status = $request->query('status');      // pendente|aprovada|rejeitada
        $tipo   = $request->query('tipo');        // local|interstate|international

        $query = Transferencia::with(['atleta','clubOrigem.federacao','clubDestino.federacao'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }
        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        $itens = $query->limit(200)->get();

        return view('transfer.logs', compact('itens', 'status', 'tipo'));
    }

    // ===================== Helper =====================

    protected function approveAndMove(Transferencia $transfer, string $context)
    {
        if ($transfer->status !== 'pendente') {
            return back()->with('ok', 'Transferência já processada.');
        }

        DB::transaction(function () use ($transfer) {
            $transfer->status      = 'aprovada';
            $transfer->approved_by = auth()->id();
            $transfer->approved_at = now();
            $transfer->save();

            // move o atleta para o clube de destino
            $transfer->atleta->update(['clube_id' => $transfer->club_destino_id]);
        });

        return back()->with('ok', "Transferência aprovada: {$context}");
    }
}
