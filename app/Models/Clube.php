<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Clube extends Model
{
    protected $table = 'clubes';

    protected $fillable = [
        'nome',
        'federacao_id',
        'cnpj',
        'cidade',
        'estado',
        'whatsapp_admin',
    ];

    protected $casts = [
        // adicione casts se necessário no futuro
    ];

    /*
    |--------------------------------------------------------------------------
    | Mutators (set) e Accessors (get)
    |--------------------------------------------------------------------------
    */

    // CNPJ: salva apenas dígitos
    protected function cnpj(): Attribute
    {
        return Attribute::make(
            set: fn ($v) => $v ? preg_replace('/\D+/', '', $v) : null
        );
    }

    // WhatsApp admin: salva apenas dígitos
    protected function whatsappAdmin(): Attribute
    {
        return Attribute::make(
            set: fn ($v) => $v ? preg_replace('/\D+/', '', $v) : null
        );
    }

    // Cidade: normaliza em Title Case (mantém acentos)
    protected function cidade(): Attribute
    {
        return Attribute::make(
            set: function ($v) {
                if (!$v) return null;
                $v = mb_strtolower($v, 'UTF-8');
                return mb_convert_case($v, MB_CASE_TITLE, 'UTF-8');
            }
        );
    }

    // Estado (UF): garante 2 letras maiúsculas
    protected function estado(): Attribute
    {
        return Attribute::make(
            set: function ($v) {
                if (!$v) return null;
                $v = strtoupper(trim($v));
                return substr($v, 0, 2);
            }
        );
    }

    // CNPJ formatado para exibição: 00.000.000/0000-00
    public function getCnpjFormatadoAttribute(): ?string
    {
        $c = $this->cnpj;
        if (!$c || strlen($c) !== 14) return $c;
        return substr($c,0,2).'.'.substr($c,2,3).'.'.substr($c,5,3).'/'.substr($c,8,4).'-'.substr($c,12,2);
    }

    // WhatsApp formatado para exibição (tentativa simples): (DD) 9MMMM-NNNN
    public function getWhatsappAdminFormatadoAttribute(): ?string
    {
        $w = $this->whatsapp_admin;
        if (!$w) return $w;

        // remove +55 se vier
        $w = preg_replace('/^\+?55/', '', $w);
        $w = preg_replace('/\D+/', '', $w);

        // tenta formatar: DD + 9 + 8 dígitos -> (DD) 9XXXX-XXXX
        if (strlen($w) === 11) {
            $dd   = substr($w, 0, 2);
            $parte1 = substr($w, 2, 5);
            $parte2 = substr($w, 7, 4);
            return "($dd) $parte1-$parte2";
        }

        // fallback: retorna como está
        return $this->whatsapp_admin;
    }

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function federacao()
    {
        return $this->belongsTo(Federacao::class);
    }

    public function atletas()
    {
        return $this->hasMany(Atleta::class, 'clube_id')->orderBy('nome');
    }

    // Transferências em que este clube foi a ORIGEM
    public function transferenciasOrigem()
    {
        return $this->hasMany(Transferencia::class, 'club_origem_id')->latest();
    }

    // Transferências em que este clube foi o DESTINO
    public function transferenciasDestino()
    {
        return $this->hasMany(Transferencia::class, 'club_destino_id')->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes úteis
    |--------------------------------------------------------------------------
    */

    // Busca por nome (like)
    public function scopeBuscaNome($query, ?string $q)
    {
        if (!$q) return $query;
        return $query->where('nome', 'like', "%{$q}%");
    }

    // Filtra por federação
    public function scopeDaFederacao($query, $federacaoId)
    {
        if (!$federacaoId) return $query;
        return $query->where('federacao_id', $federacaoId);
    }
}
