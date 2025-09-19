<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Federacao extends Model
{
    protected $table = 'federacoes';

    protected $fillable = [
        'nome',
        'sigla',
        'cnpj',       // <- agora é cnpj
        'presidente',
        'site',
        'email',
        'telefone',
    ];

    // Normaliza CNPJ (apenas dígitos)
    protected function cnpj(): Attribute
    {
        return Attribute::make(
            set: fn ($v) => $v ? preg_replace('/\D+/', '', $v) : null
        );
    }

    // Normaliza telefone (apenas dígitos)
    protected function telefone(): Attribute
    {
        return Attribute::make(
            set: fn ($v) => $v ? preg_replace('/\D+/', '', $v) : null
        );
    }

    // CNPJ formatado (##.###.###/####-##)
    public function getCnpjFormatadoAttribute(): ?string
    {
        $c = preg_replace('/\D+/', '', (string) $this->cnpj);
        if (strlen($c) !== 14) {
            return $this->cnpj;
        }
        return substr($c, 0, 2) . '.' . substr($c, 2, 3) . '.' . substr($c, 5, 3)
             . '/' . substr($c, 8, 4) . '-' . substr($c, 12, 2);
    }

    public function clubes()
    {
        return $this->hasMany(Clube::class, 'federacao_id')->orderBy('nome');
    }
}
