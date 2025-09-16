<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Federação (ex.: MG, RS...)
 */
class Federacao extends Model
{
    // ✅ estas linhas PRECISAM estar DENTRO das chaves da classe
    protected $table = 'federacoes';
    protected $fillable = ['nome', 'sigla'];

    public function clubes()
    {
        return $this->hasMany(Clube::class);
    }
}
