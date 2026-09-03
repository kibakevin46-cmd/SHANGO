<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PredictionBatterie extends Model
{
    protected $table = 'predictions_batteries';

    protected $fillable = [
        'device_id',
        'date_heure',
        'voltage_v',
        'current_a',
        'temperature_c',
        'dod_percent',
        'soh_pourcent',
        'capacite_ah',
        'rul_jours',
        'etat',
    ];

    protected $casts = [
        'date_heure' => 'datetime',
        'voltage_v' => 'float',
        'current_a' => 'float',
        'temperature_c' => 'float',
        'dod_percent' => 'float',
        'soh_pourcent' => 'float',
        'capacite_ah' => 'float',
        'rul_jours' => 'float',
    ];
}