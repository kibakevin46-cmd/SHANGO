<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PredictionBatterie;
use Illuminate\Http\Request;

class BatterieDiagnosticController extends Controller
{
    /**
     * Diagnostic d'une batterie
     */
    public function store(Request $request)
    {
        // 1. Validation des données reçues
        $validated = $request->validate([
            'device_id' => 'required|string',
            'voltage_v' => 'required|numeric',
            'current_a' => 'required|numeric',
            'temperature_c' => 'required|numeric',
            'dod_percent' => 'required|numeric|min:0|max:100',
        ]);

        // 2. Récupération des données
        $deviceId = $validated['device_id'];
        $voltage = (float) $validated['voltage_v'];
        $current = (float) $validated['current_a'];
        $temperature = (float) $validated['temperature_c'];
        $dod = (float) $validated['dod_percent'];

        // 3. Conversion de la tension du pack
        // Alioth possède 4 cellules
        $voltageCellule = $voltage / 4;

        // 4. Données qui seront envoyées au modèle IA
        $donneesIA = [
            $voltageCellule,
            $current,
            $temperature,
            $dod,
        ];

        // 5. Pour l'instant, on ne lance pas encore le vrai modèle IA.
        // On vérifie simplement que les données sont correctement préparées.

        return response()->json([
            'success' => true,
            'message' => 'Données batterie reçues et préparées pour l’IA.',
            'donnees_recues' => [
                'device_id' => $deviceId,
                'voltage_v' => $voltage,
                'current_a' => $current,
                'temperature_c' => $temperature,
                'dod_percent' => $dod,
            ],
            'tension_par_cellule' => $voltageCellule,
            'donnees_envoyees_a_l_ia' => $donneesIA,
        ], 200);
    }
}