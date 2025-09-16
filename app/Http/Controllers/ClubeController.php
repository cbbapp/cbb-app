<?php

namespace App\Http\Controllers;

use App\Models\Clube;
use App\Models\Federacao;
use Illuminate\Http\Request;

class ClubeController extends Controller
{
    public function index()
    {
        $itens = Clube::with('federacao')->orderBy('nome')->paginate(20);
        return view('clubes.index', compact('itens'));
    }

    public function create()
    {
        $user = auth()->user();

        // Admin vê todas as federações; Federação vê apenas a sua; (clube não acessa esta tela).
        if ($user->hasRole('admin')) {
            $federacoes = Federacao::orderBy('sigla')->get();
        } elseif ($user->hasRole('federacao')) {
            $federacoes = Federacao::where('id', $user->federacao_id)->get();
        } else {
            abort(403, 'Você não pode criar clubes.');
        }

        return view('clubes.create', compact('federacoes'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Validação básica
        $data = $request->validate([
            'nome'         => ['required','string','max:255'],
            'federacao_id' => ['required','integer','exists:federacoes,id'],
        ]);

        // Regras de escopo:
        // - admin pode criar em qualquer federação
        // - federacao SÓ pode criar na própria ($user->federacao_id)
        if ($user->hasRole('admin')) {
            // tudo liberado
        } elseif ($user->hasRole('federacao')) {
            // Hard enforcement: se veio ID de outra federação, bloqueia
            if ((int)$data['federacao_id'] !== (int)$user->federacao_id) {
                abort(403, 'Você só pode criar clubes na sua própria federação.');
            }
        } else {
            abort(403, 'Você não pode criar clubes.');
        }

        $clube = Clube::create($data);

        return redirect()->route('club.index')->with('ok', "Clube #{$clube->id} cadastrado.");
    }

    public function destroy($id)
    {
        // Exclusão já está limitada por rota ao admin.
        $clube = Clube::findOrFail($id);
        $clube->delete();

        return back()->with('ok', 'Clube excluído.');
    }
}
