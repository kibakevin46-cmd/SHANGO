<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Equipement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AlerteController extends Controller
{
    /**
     * =====================================================
     * ENREGISTRER UNE ALERTE
     * =====================================================
     *
     * Endpoint :
     * POST /api/alertes
     *
     * Utilisé par le bridge IoT.
     *
     * Format reçu :
     *
     * {
     *     "id_appareil": "SH-001",
     *     "type_alerte": "CHOC",
     *     "gravite": "ELEVEE",
     *     "valeur": 8.2,
     *     "horodatage": 1735689600
     * }
     */
    public function store(Request $request)
    {
        // =================================================
        // 1. VALIDATION
        // =================================================

        $validated = $request->validate([
            'id_appareil' => 'required|string|max:255',

            'type_alerte' => 'required|string|max:100',

            'gravite' => 'required|string|max:50',

            'valeur' => 'nullable|numeric',

            'horodatage' => 'required|integer',
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
        // 4. CONVERTIR L'HORODATAGE UNIX
        // =================================================

        $horodatage = Carbon::createFromTimestamp(
            $validated['horodatage']
        );

        // =================================================
        // 5. PRÉPARER LES DONNÉES
        // =================================================

        $data = [
            'equipement_id' => $equipement->id,

            // id_appareil → device_id
            'device_id' => $validated['id_appareil'],

            'type_alerte' => $validated['type_alerte'],

            'gravite' => $validated['gravite'],

            'valeur' => $validated['valeur'] ?? null,

            // Timestamp Unix → date Laravel
            'horodatage' => $horodatage,
        ];

        // =================================================
        // 6. ENREGISTRER L'ALERTE
        // =================================================

        $alerte = Alerte::create($data);

        // =================================================
        // 7. RÉPONSE
        // =================================================

        return response()->json([
            'success' => true,
            'message' => 'Alerte enregistrée avec succès.',
            'data' => $alerte,
        ], 201);
    }


    /**
     * =====================================================
     * LISTE DES ALERTES
     * =====================================================
     *
     * Endpoint :
     * GET /api/alertes
     *
     * Accessible aux utilisateurs SHANGO authentifiés.
     */
    public function index()
    {
        $alertes = Alerte::with('equipement')
            ->orderBy('horodatage', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des alertes récupérée avec succès.',
            'data' => $alertes,
        ]);
    }


    /**
     * =====================================================
     * HISTORIQUE DES ALERTES D'UN ÉQUIPEMENT
     * =====================================================
     *
     * Endpoint :
     * GET /api/equipements/{equipement}/alertes
     */
    public function equipementAlertes($equipementId)
    {
        $alertes = Alerte::where(
            'equipement_id',
            $equipementId
        )
            ->orderBy('horodatage', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Historique des alertes récupéré avec succès.',
            'data' => $alertes,
        ]);
    }
}