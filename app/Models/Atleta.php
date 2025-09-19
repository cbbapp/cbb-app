<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Atleta extends Model
{
    protected $table = 'atletas';

    // inclui os novos campos
    protected $fillable = [
        'nome',
        'cpf',
        'data_nascimento',
        'clube_id',
        'situacao', // ok mesmo que a coluna ainda não exista; será ignorada
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    // não expor CPF em arrays/json/blades acidentalmente
    protected $hidden = ['cpf', 'data_nascimento'];

    /**
     * Mutator: salva CPF apenas com dígitos
     */
    protected function cpf(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? preg_replace('/\D+/', '', $value) : null
        );
    }

    /**
     * Accessor: idade calculada em anos
     */
    protected function idade(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->data_nascimento) return null;
                return Carbon::parse($this->data_nascimento)->age;
            }
        );
    }

    /**
     * Relacionamento com Clube
     */
    public function clube()
    {
        return $this->belongsTo(Clube::class);
    }

    /**
     * Histórico de transferências do atleta
     */
    public function transferencias()
    {
        return $this->hasMany(Transferencia::class, 'atleta_id')->latest();
    }
}
