<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PredictionBatterie;
use App\Services\BatterieIAService;
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

        // 3. Utilisation du service IA
        $serviceIA = new BatterieIAService();

        $donneesIA = $serviceIA->preparerDonnees(
            $voltage,
            $current,
            $temperature,
            $dod
        );

        // 4. Récupération de la tension par cellule
        $voltageCellule = $donneesIA[0];

        // 5. Enregistrement dans la base de données
        // Les résultats IA restent null pour le moment.
        $prediction = PredictionBatterie::create([
            'device_id' => $deviceId,
            'date_heure' => now(),
            'voltage_v' => $voltage,
            'current_a' => $current,
            'temperature_c' => $temperature,
            'dod_percent' => $dod,
            'soh_pourcent' => null,
            'capacite_ah' => null,
            'rul_jours' => null,
            'etat' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Données batterie reçues et enregistrées avec succès.',
            'prediction_id' => $prediction->id,

            'donnees_recues' => [
                'device_id' => $deviceId,
                'voltage_v' => $voltage,
                'current_a' => $current,
                'temperature_c' => $temperature,
                'dod_percent' => $dod,
            ],

            'tension_par_cellule' => $voltageCellule,

            'donnees_envoyees_a_l_ia' => $donneesIA,

            'resultats_ia' => [
                'soh_pourcent' => null,
                'capacite_ah' => null,
                'rul_jours' => null,
                'etat' => null,
            ],
        ], 200);
    }

    /**
     * Historique des diagnostics d'une batterie
     */
    public function history(string $device_id)
    {
        $historique = PredictionBatterie::where('device_id', $device_id)
            ->orderBy('date_heure', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'device_id' => $device_id,
            'nombre_diagnostics' => $historique->count(),
            'historique' => $historique,
        ], 200);
    }
}
