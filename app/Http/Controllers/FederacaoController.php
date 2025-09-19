<?php

namespace App\Http\Controllers;

use App\Http\Requests\FederacaoRequest;
use App\Models\Federacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FederacaoController extends Controller
{
    public function index(Request $request): View
    {
        // termo de busca (ou vazio)
        $q = trim((string) $request->query('q', ''));

        $query = Federacao::query();

        if ($q !== '') {
            // também tenta casar por dígitos do CNPJ
            $digits = preg_replace('/\D+/', '', $q);

            $query->where(function ($w) use ($q, $digits) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('sigla', 'like', "%{$q}%");

                if ($digits !== '') {
                    // coluna cnpj guarda só dígitos (mutator do Model)
                    $w->orWhere('cnpj', 'like', "%{$digits}%");
                }
            });
        }

        $federacoes = $query->orderBy('nome')->paginate(20)->withQueryString();

        return view('federacoes.index', compact('federacoes', 'q'));
    }

    public function create(): View
    {
        return view('federacoes.create');
    }

    public function store(FederacaoRequest $request): RedirectResponse
    {
        Federacao::create($request->validated());

        return redirect()
            ->route('federation.index')
            ->with('ok', 'Federação cadastrada com sucesso.');
    }

    public function show(int $id): View
    {
        $federacao = Federacao::findOrFail($id);
        return view('federacoes.show', compact('federacao'));
    }

    public function edit(int $id): View
    {
        $federacao = Federacao::findOrFail($id);
        return view('federacoes.edit', compact('federacao'));
    }

    public function update(FederacaoRequest $request, int $id): RedirectResponse
    {
        $federacao = Federacao::findOrFail($id);
        $federacao->update($request->validated());

        return redirect()
            ->route('federation.show', $federacao->id)
            ->with('ok', 'Federação atualizada com sucesso.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $federacao = Federacao::findOrFail($id);
        $federacao->delete();

        return redirect()
            ->route('federation.index')
            ->with('ok', 'Federação removida com sucesso.');
    }
}
