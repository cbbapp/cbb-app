<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representa uma transferência de atleta de um clube para outro.
 * Campos principais:
 * - atleta_id
 * - club_origem_id
 * - club_destino_id
 * - tipo: local | interstate | international
 * - status: pendente | aprovada | rejeitada
 * - rejection_reason: motivo em caso de rejeição
 * - approved_by: id do usuário que aprovou/rejeitou
 * - approved_at: data/hora da decisão
 */
class Transferencia extends Model
{
    protected $table = 'transferencias';

    protected $fillable = [
        'atleta_id',
        'club_origem_id',
        'club_destino_id',
        'tipo',
        'status',
        'rejection_reason', // 👈 novo campo
        'approved_by',      // 👈 novo campo
        'approved_at',      // 👈 novo campo
    ];

    /**
     * A transferência é de um atleta.
     */
    public function atleta()
    {
        return $this->belongsTo(Atleta::class);
    }

    /**
     * Clube de origem do atleta (antes da transferência).
     */
    public function clubOrigem()
    {
        return $this->belongsTo(Clube::class, 'club_origem_id');
    }

    /**
     * Clube de destino do atleta (após a transferência).
     */
    public function clubDestino()
    {
        return $this->belongsTo(Clube::class, 'club_destino_id');
    }
}
