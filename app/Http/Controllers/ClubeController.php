<?php

namespace App\Http\Controllers;

use App\Models\Clube;
use App\Models\Federacao;
use App\Http\Requests\ClubeRequest;
use Illuminate\Http\Request;

class ClubeController extends Controller
{
    /**
     * Listagem de clubes com filtros e escopo por papel.
     * - admin: vê todos
     * - federacao: apenas clubes da própria federação
     * - clube: apenas o próprio clube
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $federacaoId = $request->query('federacao_id');
        $estado      = $request->query('estado');
        $q           = trim((string) $request->query('q', ''));

        $query = Clube::query()->with('federacao');

        if ($user->hasRole('admin')) {
            // admin vê tudo
        } elseif ($user->hasRole('federacao')) {
            $query->where('federacao_id', $user->federacao_id);
            $federacaoId = $user->federacao_id; // fixa no filtro
        } elseif ($user->hasRole('clube')) {
            $query->where('id', $user->clube_id);
            $federacaoId = optional($user->clube)->federacao_id ?? null;
        } else {
            abort(403, 'Sem permissão para listar clubes.');
        }

        if (!empty($federacaoId)) {
            $query->where('federacao_id', $federacaoId);
        }
        if (!empty($estado)) {
            $query->where('estado', $estado);
        }
        if ($q !== '') {
            $query->where('nome', 'like', "%{$q}%");
        }

        $itens = $query->orderBy('nome')->paginate(20)->withQueryString();

        // listas para filtros (e para a view não quebrar)
        if ($user->hasRole('admin')) {
            $federacoes = Federacao::orderBy('sigla')->get();
        } elseif ($user->hasRole('federacao')) {
            $federacoes = Federacao::where('id', $user->federacao_id)->orderBy('sigla')->get();
        } else { // clube
            $federacoes = Federacao::where('id', $federacaoId)->get();
        }

        // lista de UFs que existem na base, opcional para filtro
        $estados = Clube::select('estado')
            ->whereNotNull('estado')
            ->when(!$user->hasRole('admin') && $user->hasRole('federacao'), fn($q2) => $q2->where('federacao_id', $user->federacao_id))
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        return view('clubes.index', compact(
            'itens',
            'federacoes',
            'federacaoId',
            'estados',
            'estado',
            'q'
        ));
    }

    /**
     * Ficha do clube — atletas paginados (corrige erro de Collection::links()).
     */
    public function show($id)
    {
        $clube = Clube::with('federacao')->findOrFail($id);

        // Escopos de acesso
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            // ok
        } elseif ($user->hasRole('federacao')) {
            if ((int)$clube->federacao_id !== (int)$user->federacao_id) {
                abort(403, 'Você só pode ver clubes da sua federação.');
            }
        } elseif ($user->hasRole('clube')) {
            if ((int)$clube->id !== (int)$user->clube_id) {
                abort(403, 'Você só pode ver seu próprio clube.');
            }
        } else {
            abort(403);
        }

        // Agora como paginator:
        $atletas = $clube->atletas()
            ->select('id', 'nome', 'clube_id')
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return view('clubes.show', compact('clube', 'atletas'));
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $federacoes = Federacao::orderBy('sigla')->get();
        } elseif ($user->hasRole('federacao')) {
            $federacoes = Federacao::where('id', $user->federacao_id)->orderBy('sigla')->get();
        } else {
            abort(403, 'Você não pode cadastrar clubes.');
        }

        return view('clubes.create', compact('federacoes'));
    }

    /**
     * Persistência de novo clube — usa ClubeRequest.
     */
    public function store(ClubeRequest $request)
    {
        $user = auth()->user();

        $data = $request->validated();

        // escopos
        if ($user->hasRole('admin')) {
            // ok
        } elseif ($user->hasRole('federacao')) {
            if ((int)$data['federacao_id'] !== (int)$user->federacao_id) {
                abort(403, 'Você só pode cadastrar clubes na sua federação.');
            }
        } else {
            abort(403, 'Você não pode cadastrar clubes.');
        }

        // salva só os dígitos no banco
        $data['cnpj'] = $data['cnpj_digits'];
        unset($data['cnpj_digits']);

        // normaliza whatsapp (apenas dígitos)
        if (!empty($data['whatsapp_admin'])) {
            $data['whatsapp_admin'] = preg_replace('/\D+/', '', $data['whatsapp_admin']);
        }

        Clube::create($data);

        return redirect()->route('club.index')->with('ok', 'Clube cadastrado com sucesso.');
    }

    /**
     * Editar (ADMIN).
     */
    public function edit($id)
    {
        $this->authorizeAdmin();
        $clube = Clube::with('federacao')->findOrFail($id);
        $federacoes = Federacao::orderBy('sigla')->get();

        return view('clubes.edit', compact('clube','federacoes'));
    }

    /**
     * Update (ADMIN) — usa ClubeRequest.
     */
    public function update(ClubeRequest $request, $id)
    {
        $this->authorizeAdmin();

        $clube = Clube::findOrFail($id);

        $data = $request->validated();

        // salva só os dígitos no banco
        $data['cnpj'] = $data['cnpj_digits'];
        unset($data['cnpj_digits']);

        if (!empty($data['whatsapp_admin'])) {
            $data['whatsapp_admin'] = preg_replace('/\D+/', '', $data['whatsapp_admin']);
        }

        $clube->update($data);

        return redirect()->route('club.index')->with('ok', 'Clube atualizado.');
    }

    /**
     * Exclusão (ADMIN) — checagem defensiva.
     */
    public function destroy($id)
    {
        $this->authorizeAdmin();

        $clube = Clube::findOrFail($id);

        // (opcional) impedir apagar se tiver atletas
        // if ($clube->atletas()->exists()) {
        //     return back()->withErrors('Não é possível excluir um clube que possui atletas.');
        // }

        $clube->delete();

        return back()->with('ok', 'Clube excluído.');
    }

    protected function authorizeAdmin(): void
    {
        if (!auth()->user()?->hasRole('admin')) {
            abort(403, 'Apenas admin.');
        }
    }
}
