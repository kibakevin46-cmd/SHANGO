<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotCredential extends Model
{
    protected $fillable = [
        'equipement_id',
        'device_id',
        'api_key_hash',
        'actif',
        'last_used_at',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * L'identifiant IoT appartient à un équipement.
     */
    public function equipement(): BelongsTo
    {
        return $this->belongsTo(Equipement::class);
    }
}