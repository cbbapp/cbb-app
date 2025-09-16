<?php

namespace App\Http\Controllers;

use App\Models\Atleta;
use App\Models\Clube;
use Illuminate\Http\Request;

class AtletaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Escopo da listagem:
        if ($user->hasRole('admin')) {
            $itens = Atleta::with('clube.federacao')->orderBy('nome')->paginate(20);
        } elseif ($user->hasRole('federacao')) {
            $itens = Atleta::whereHas('clube', function ($q) use ($user) {
                $q->where('federacao_id', $user->federacao_id);
            })->with('clube.federacao')->orderBy('nome')->paginate(20);
        } elseif ($user->hasRole('clube')) {
            $itens = Atleta::where('clube_id', $user->clube_id)
                ->with('clube.federacao')->orderBy('nome')->paginate(20);
        } else {
            abort(403, 'Sem permissão para listar atletas.');
        }

        return view('atletas.index', compact('itens'));
    }

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

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'nome'     => ['required','string','max:255'],
            'clube_id' => ['required','integer','exists:clubes,id'],
        ]);

        $clube = Clube::with('federacao')->findOrFail($data['clube_id']);

        // Regras de escopo
        if ($user->hasRole('admin')) {
            // Admin pode tudo
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

    public function destroy($id)
    {
        $user = auth()->user();

        $atleta = Atleta::with('clube.federacao')->findOrFail($id);

        // Escopo de exclusão
        if ($user->hasRole('admin')) {
            // pode deletar qualquer
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
}
