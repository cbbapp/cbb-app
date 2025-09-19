<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Clube;
use App\Models\Federacao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AtletaController extends Controller
{
    /**
     * Listagem com filtros de Federação e Clube.
     * Escopo por papel:
     * - admin: vê todos
     * - federacao: apenas clubes da própria federação
     * - clube: apenas o próprio clube
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Filtros (query string)
        $federacaoId = $request->query('federacao_id');
        $clubeId     = $request->query('clube_id');

        $query = Atleta::query()->with('clube.federacao');

        if ($user->hasRole('admin')) {
            // admin vê todos
        } elseif ($user->hasRole('federacao')) {
            $query->whereHas('clube', function ($q) use ($user) {
                $q->where('federacao_id', $user->federacao_id);
            });
            // Travamos a federação no filtro para a UI
            $federacaoId = $user->federacao_id;
        } elseif ($user->hasRole('clube')) {
            $query->where('clube_id', $user->clube_id);
            $clubeId = $user->clube_id;
            $federacaoId = optional($user->clube)->federacao_id ?? null;
        } else {
            abort(403, 'Sem permissão para listar atletas.');
        }

        if (!empty($federacaoId)) {
            $query->whereHas('clube', function ($q) use ($federacaoId) {
                $q->where('federacao_id', $federacaoId);
            });
        }
        if (!empty($clubeId)) {
            $query->where('clube_id', $clubeId);
        }

        $itens = $query->orderBy('nome')->paginate(20)->withQueryString();

        // Listas para os selects
        if ($user->hasRole('admin')) {
            $federacoes = Federacao::orderBy('sigla')->get();
            $clubes = Clube::when($federacaoId, fn($q) => $q->where('federacao_id', $federacaoId))
                ->orderBy('nome')->get();
        } elseif ($user->hasRole('federacao')) {
            $federacoes = Federacao::where('id', $user->federacao_id)->orderBy('sigla')->get();
            $clubes = Clube::where('federacao_id', $user->federacao_id)
                ->orderBy('nome')->get();
        } else { // clube
            $federacoes = Federacao::where('id', optional($user->clube)->federacao_id)->get();
            $clubes = Clube::where('id', $user->clube_id)->get();
        }

        // >>> NOVO: carrega o clube selecionado (com federação) para renderizar a ficha no topo
        $clubeSelecionado = null;
        if (!empty($clubeId)) {
            $clubeSelecionado = Clube::with('federacao')->find($clubeId);
        }

        return view('atletas.index', compact(
            'itens',
            'federacoes',
            'clubes',
            'federacaoId',
            'clubeId',
            'clubeSelecionado' // <<< passa para a view
        ));
    }

    /**
     * Formulário de criação (escopo por papel).
     */
    public function create()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $clubes = Clube::with('federacao')->orderBy('nome')->get();
        } elseif ($user->hasRole('federacao')) {
            $clubes = Clube::where('federacao_id', $user->federacao_id)
                ->with('federacao')->orderBy('nome')->get();
        } elseif ($user->hasRole('clube')) {
            $clubes = Clube::where('id', $user->clube_id)->with('federacao')->get();
        } else {
            abort(403, 'Você não pode cadastrar atletas.');
        }

        return view('atletas.create', compact('clubes'));
    }

    /**
     * Persistência de novo atleta (escopo por papel).
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'nome'             => ['required','string','max:255'],
            'cpf'              => ['required','string','size:11','unique:atletas,cpf'],
            'data_nascimento'  => ['required','date','before:today'],
            'clube_id'         => ['required','integer','exists:clubes,id'],
            // 'situacao'       => ['in:apto,irregular'], // se estiver usando o campo
        ], [
            'cpf.size' => 'O CPF deve conter 11 dígitos (apenas números).',
        ]);

        // garante somente dígitos (o mutator também faz)
        $data['cpf'] = preg_replace('/\D+/', '', $data['cpf']);

        $clube = Clube::with('federacao')->findOrFail($data['clube_id']);

        if ($user->hasRole('admin')) {
            // ok
        } elseif ($user->hasRole('federacao')) {
            if ((int)$clube->federacao_id !== (int)$user->federacao_id) {
                abort(403, 'Você só pode cadastrar atletas em clubes da sua federação.');
            }
        } elseif ($user->hasRole('clube')) {
            if ((int)$clube->id !== (int)$user->clube_id) {
                abort(403, 'Você só pode cadastrar atletas no seu próprio clube.');
            }
        } else {
            abort(403, 'Você não pode cadastrar atletas.');
        }

        $atleta = Atleta::create($data);

        return redirect()->route('athlete.index')->with('ok', "Atleta {$atleta->nome} cadastrado.");
    }

    /**
     * Edição (ADMIN).
     */
    public function edit($id)
    {
        $this->authorizeAdmin();
        $atleta = Atleta::with('clube.federacao')->findOrFail($id);
        $clubes = Clube::with('federacao')->orderBy('nome')->get(); // admin pode mover de clube
        return view('atletas.edit', compact('atleta', 'clubes'));
    }

    /**
     * Update (ADMIN).
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();

        $atleta = Atleta::findOrFail($id);

        $data = $request->validate([
            'nome'             => ['required','string','max:255'],
            'cpf'              => ['required','string','size:11', Rule::unique('atletas','cpf')->ignore($atleta->id)],
            'data_nascimento'  => ['required','date','before:today'],
            'clube_id'         => ['required','integer','exists:clubes,id'],
            // 'situacao'       => ['in:apto,irregular'],
        ], [
            'cpf.size' => 'O CPF deve conter 11 dígitos (apenas números).',
        ]);

        $data['cpf'] = preg_replace('/\D+/', '', $data['cpf']);

        $atleta->update($data);

        return redirect()->route('athlete.index')->with('ok', 'Atleta atualizado.');
    }

    /**
     * Exclusão (escopo por papel).
     * Mantivemos admin-only via rota, mas deixamos checagem defensiva.
     */
    public function destroy($id)
    {
        $user = auth()->user();

        $atleta = Atleta::with('clube.federacao')->findOrFail($id);

        if ($user->hasRole('admin')) {
            // ok
        } elseif ($user->hasRole('federacao')) {
            if ($atleta->clube->federacao_id !== $user->federacao_id) {
                abort(403, 'Você só pode excluir atletas da sua federação.');
            }
        } elseif ($user->hasRole('clube')) {
            if ($atleta->clube_id !== $user->clube_id) {
                abort(403, 'Você só pode excluir atletas do seu próprio clube.');
            }
        } else {
            abort(403, 'Sem permissão para excluir atletas.');
        }

        $atleta->delete();

        return back()->with('ok', 'Atleta excluído.');
    }

    /**
     * Ficha do atleta (show).
     */
    public function show($id)
    {
        $atleta = Atleta::with([
                'clube.federacao',
                'transferencias' => function ($q) {
                    $q->select('id','atleta_id','club_destino_id','created_at')
                      ->with(['clubDestino:id,nome']);
                }
            ])->findOrFail($id);

        return view('atletas.show', compact('atleta'));
    }

    /**
     * Helper para garantir admin nos endpoints de edição.
     */
    protected function authorizeAdmin(): void
    {
        if (!auth()->user()?->hasRole('admin')) {
            abort(403, 'Apenas admin pode editar.');
        }
    }
}
