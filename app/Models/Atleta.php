<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atleta extends Model
{
    protected $table = 'atletas';
    protected $fillable = ['nome', 'data_nascimento', 'clube_id'];

    public function clube()
    {
        return $this->belongsTo(Clube::class);
    }
}
