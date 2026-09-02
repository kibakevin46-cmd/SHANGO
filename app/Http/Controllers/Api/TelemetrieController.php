<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipement;
use App\Models\Telemetrie;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\AlerteDetectionService;

class TelemetrieController extends Controller
{
    /**
     * =====================================================
     * ENREGISTRER UNE TÉLÉMÉTRIE
     * =====================================================
     *
     * Endpoint :
     * POST /api/telemetrie
     *
     * Format reçu du bridge IoT :
     *
     * {
     *     "id_appareil": "SH-001",
     *     "horodatage": 1735689600,
     *     "gps": {
     *         "latitude": 12.36,
     *         "longitude": -1.53
     *     },
     *     "tension": 12.8,
     *     "courant": 2.1,
     *     "temperature": 34.5,
     *     "etat_relais": "MARCHE",
     *     "statut_paiement": "OK",
     *     "signal_gsm": -75
     * }
     */
    public function store(
        Request $request,
        AlerteDetectionService $alerteDetectionService
    ) {
        // =================================================
        // 1. VALIDATION DES DONNÉES
        // =================================================

        $validated = $request->validate([
            'id_appareil' => 'required|string|max:255',

            'horodatage' => 'required|integer',

            'gps' => 'nullable|array',
            'gps.latitude' => 'nullable|numeric|between:-90,90',
            'gps.longitude' => 'nullable|numeric|between:-180,180',

            'tension' => 'nullable|numeric',
            'courant' => 'nullable|numeric',
            'temperature' => 'nullable|numeric',

            'etat_relais' => 'nullable|string|max:50',
            'statut_paiement' => 'nullable|string|max:50',

            'signal_gsm' => 'nullable|integer',
        ]);

        // =================================================
        // 2. RECHERCHER L'ÉQUIPEMENT
        // =================================================

        $equipement = Equipement::where(
            'device_id',
            $validated['id_appareil']
        )->first();

        // =================================================
        // 3. VÉRIFIER QUE L'ÉQUIPEMENT EXISTE
        // =================================================

        if (!$equipement) {
            return response()->json([
                'success' => false,
                'message' => 'Équipement introuvable pour cet id_appareil.',
                'id_appareil' => $validated['id_appareil'],
            ], 404);
        }

        // =================================================
        // 4. CONVERSION DE L'HORODATAGE UNIX
        // =================================================

        $horodatage = Carbon::createFromTimestamp(
            $validated['horodatage']
        );

        // =================================================
        // 5. EXTRAIRE LES COORDONNÉES GPS
        // =================================================

        $latitude = $validated['gps']['latitude'] ?? null;
        $longitude = $validated['gps']['longitude'] ?? null;

        // =================================================
        // 6. PRÉPARER LES DONNÉES POUR LA BASE
        // =================================================

        $data = [
            'equipement_id' => $equipement->id,

            // id_appareil → device_id
            'device_id' => $validated['id_appareil'],

            // Timestamp Unix → date Laravel
            'horodatage' => $horodatage,

            // GPS
            'latitude' => $latitude,
            'longitude' => $longitude,

            'tension' => $validated['tension'] ?? null,
            'courant' => $validated['courant'] ?? null,
            'temperature' => $validated['temperature'] ?? null,

            // etat_relais → etat_kit
            'etat_kit' => $validated['etat_relais'] ?? null,

            'statut_paiement' => $validated['statut_paiement'] ?? null,

            'signal_gsm' => $validated['signal_gsm'] ?? null,
        ];

        // =================================================
        // 7. ENREGISTRER LA TÉLÉMÉTRIE
        // =================================================

        $telemetrie = Telemetrie::create($data);

        // =================================================
        // 8. DÉTECTION AUTOMATIQUE DES ALERTES
        // =================================================

        $alerteDetectionService->detecter($telemetrie);

        // =================================================
        // 9. RÉPONSE
        // =================================================

        return response()->json([
            'success' => true,
            'message' => 'Télémétrie enregistrée avec succès.',
            'data' => $telemetrie,
        ], 201);
    }


    /**
     * =====================================================
     * LISTE DES TÉLÉMÉTRIES
     * =====================================================
     *
     * Endpoint :
     * GET /api/telemetries
     */
    public function index()
    {
        $telemetries = Telemetrie::with('equipement')
            ->orderBy('horodatage', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des télémétries récupérée avec succès.',
            'data' => $telemetries,
        ]);
    }


    /**
     * =====================================================
     * HISTORIQUE DES TÉLÉMÉTRIES D'UN ÉQUIPEMENT
     * =====================================================
     *
     * Endpoint :
     * GET /api/equipements/{equipement}/telemetries
     *
     * Exemple :
     * GET /api/equipements/1/telemetries
     */
    public function equipementTelemetries($equipementId)
    {
        // =================================================
        // 1. RÉCUPÉRER LES TÉLÉMÉTRIES
        // =================================================

        $telemetries = Telemetrie::where(
            'equipement_id',
            $equipementId
        )
            ->orderBy('horodatage', 'desc')
            ->get();

        // =================================================
        // 2. RÉPONSE
        // =================================================

        return response()->json([
            'success' => true,
            'message' => 'Historique des télémétries récupéré avec succès.',
            'data' => $telemetries,
        ]);
    }
}