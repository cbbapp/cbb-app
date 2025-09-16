<?php

namespace App\Http\Controllers;

use App\Models\Federacao;
use Illuminate\Http\Request;

class FederacaoController extends Controller
{
    public function index()
    {
        $itens = Federacao::orderBy('sigla')->paginate(20);
        return view('federacoes.index', compact('itens'));
    }

    public function create()
    {
        return view('federacoes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sigla' => ['required','string','max:10'],
            'nome'  => ['required','string','max:255'],
        ]);

        $fed = Federacao::create($data);

        return redirect()->route('federation.index')->with('ok', "Federação #{$fed->id} cadastrada.");
    }

    public function destroy($id)
    {
        $fed = Federacao::findOrFail($id);
        $fed->delete();

        return back()->with('ok', 'Federação excluída.');
    }
}
