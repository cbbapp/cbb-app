<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Federacao;
use App\Models\Clube;
use App\Models\Atleta;

class TransferAjaxController extends Controller
{
    // Retorna todas as federações (restrinja aqui se necessário)
    public function federacoes(Request $request)
    {
        $feds = Federacao::query()
            ->select('id','nome')
            ->orderBy('nome')
            ->get();

        return response()->json($feds);
    }

    // Lista clubes por federação
    public function clubes(Request $request)
    {
        $request->validate(['federacao_id' => 'required|integer']);
        $clubes = Clube::where('federacao_id', $request->federacao_id)
            ->select('id','nome','federacao_id')
            ->orderBy('nome')
            ->get();

        return response()->json($clubes);
    }

    // Lista atletas por clube
    public function atletas(Request $request)
    {
        $request->validate(['clube_id' => 'required|integer']);
        $atletas = Atleta::where('clube_id', $request->clube_id)
            ->select('id','nome','clube_id')
            ->orderBy('nome')
            ->get();

        return response()->json($atletas);
    }

    // Busca global de atletas por nome (preenche origem automaticamente)
    public function buscarAtletas(Request $request)
    {
        $q = trim($request->get('q',''));
        if ($q === '') return response()->json([]);

        $atletas = Atleta::with(['clube:id,nome,federacao_id','clube.federacao:id,nome'])
            ->where('nome','like',"%{$q}%")
            ->limit(15)
            ->get()
            ->map(function ($a) {
                return [
                    'id'              => $a->id,
                    'nome'            => $a->nome,
                    'clube_id'        => $a->clube->id,
                    'clube_nome'      => $a->clube->nome,
                    'federacao_id'    => $a->clube->federacao->id,
                    'federacao_nome'  => $a->clube->federacao->nome,
                ];
            });

        return response()->json($atletas);
    }

    // Busca de clubes por nome OU CNPJ (para preencher destino)
    public function buscarClubes(Request $request)
    {
        $q = trim($request->get('q',''));
        $fedId = $request->get('federacao_id');

        if ($q === '') return response()->json([]);

        // limpar dígitos do CNPJ quando usuário digita com máscara
        $digits = preg_replace('/\D+/', '', $q);

        $clubes = Clube::with('federacao:id,nome,sigla')
            ->when($fedId, fn($qr) => $qr->where('federacao_id', $fedId))
            ->where(function($sub) use ($q, $digits) {
                $sub->where('nome','like',"%{$q}%");
                if ($digits !== '') {
                    $sub->orWhere('cnpj','like',"%{$digits}%");
                }
            })
            ->orderBy('nome')
            ->limit(20)
            ->get()
            ->map(function ($c) {
                return [
                    'id'               => $c->id,
                    'nome'             => $c->nome,
                    'federacao_id'     => $c->federacao?->id,
                    'federacao_nome'   => $c->federacao?->nome,
                    'federacao_sigla'  => $c->federacao?->sigla,
                    'cnpj'             => $c->cnpj,
                    'cnpj_formatado'   => $c->cnpj_formatado, // accessor do model
                    // campo "text" útil para componentes de autocomplete
                    'text'             => $c->nome
                        .($c->cnpj_formatado ? ' ('.$c->cnpj_formatado.')' : '')
                        .($c->federacao?->sigla ? ' - '.$c->federacao->sigla : ''),
                ];
            });

        return response()->json($clubes);
    }
}
