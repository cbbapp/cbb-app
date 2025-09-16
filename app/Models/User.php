<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 👇 Importa o trait do Spatie (permissões e papéis)
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles; // 👈 Adiciona aqui o HasRoles

    /**
     * Campos que podem ser preenchidos em massa (create/update).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // 👇 Importante: se o usuário for do tipo "federação", guardamos o federacao_id
        'federacao_id',
    ];

    /**
     * Campos que devem ser escondidos na serialização (ex.: APIs, JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Campos que devem ser convertidos/cast automaticamente.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * RELAÇÃO: se o usuário tiver papel de "federação",
     * este campo guarda a federação à qual ele pertence.
     * Assim conseguimos limitar permissões (ex.: aprovar transferências locais).
     */
    public function federacao()
    {
        return $this->belongsTo(\App\Models\Federacao::class);
    }
}
