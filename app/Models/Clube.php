<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clube extends Model
{
    protected $table = 'clubes';
    protected $fillable = ['nome', 'federacao_id'];

    public function federacao()
    {
        return $this->belongsTo(Federacao::class);
    }

    public function atletas()
    {
        return $this->hasMany(Atleta::class);
    }
}
