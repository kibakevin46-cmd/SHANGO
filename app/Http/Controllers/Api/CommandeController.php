<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Equipement;
use App\Services\BridgeService;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    /**
     * =====================================================
     * ENVOYER UNE COMMANDE À UN BOÎTIER IoT
     * =====================================================
     *
     * Endpoint :
     * POST /api/commande
     */
    public function store(Request $request, BridgeService $bridgeService)
    {
        // =================================================
        // 1. VALIDATION
        // =================================================

        $validated = $request->validate([
            'id_appareil' => 'required|string|max:255',

            'commande' => 'required|string|in:BLOQUER,DEBLOQUER',

            'emis_par' => 'required|string|max:100',

            'raison' => 'nullable|string|max:255',
        ]);

        // =================================================
        // 2. RECHERCHER L'ÉQUIPEMENT
        // =================================================

        $equipement = Equipement::where(
            'device_id',
            $validated['id_appareil']
        )->first();

        if (!$equipement) {
            return response()->json([
                'success' => false,
                'message' => 'Équipement introuvable pour cet id_appareil.',
                'id_appareil' => $validated['id_appareil'],
            ], 404);
        }

        // =================================================
        // 3. PRÉPARER LA COMMANDE POUR LE BRIDGE
        // =================================================

        $commandeBridge = [
            'id_appareil' => $validated['id_appareil'],
            'commande' => $validated['commande'],
            'emis_par' => $validated['emis_par'],
            'raison' => $validated['raison'] ?? null,

            // Timestamp Unix généré automatiquement par Laravel
            'horodatage' => time(),
        ];

        // =================================================
        // 4. ENVOYER AU BRIDGE
        // =================================================

        try {
            $responseBridge = $bridgeService->envoyerCommande(
                $commandeBridge
            );
        } catch (\Throwable $e) {

            // Enregistrer l'échec de communication
            $commande = Commande::create([
                'equipement_id' => $equipement->id,
                'device_id' => $equipement->device_id,
                'commande' => $validated['commande'],
                'emis_par' => $validated['emis_par'],
                'raison' => $validated['raison'] ?? null,
                'statut' => 'echouee',
                'reponse_bridge' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de contacter le Bridge MQTT.',
                'data' => $commande,
            ], 502);
        }

        // =================================================
        // 5. DÉTERMINER LE STATUT
        // =================================================

        $statut = $responseBridge->successful()
            ? 'transmise'
            : 'echouee';

        // =================================================
        // 6. ENREGISTRER LA COMMANDE
        // =================================================

        $commande = Commande::create([
            'equipement_id' => $equipement->id,
            'device_id' => $equipement->device_id,
            'commande' => $validated['commande'],
            'emis_par' => $validated['emis_par'],
            'raison' => $validated['raison'] ?? null,
            'statut' => $statut,
            'reponse_bridge' => $responseBridge->body(),
        ]);

        // =================================================
        // 7. SI LE BRIDGE REFUSE
        // =================================================

        if (!$responseBridge->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Le Bridge MQTT a refusé la commande.',
                'bridge_status' => $responseBridge->status(),
                'data' => $commande,
            ], 502);
        }

        // =================================================
        // 8. RÉPONSE FINALE
        // =================================================

        return response()->json([
            'success' => true,
            'message' => 'Commande reçue et transmise au Bridge MQTT avec succès.',
            'data' => $commande,
        ], 201);
    }


    /**
     * =====================================================
     * LISTE DE TOUTES LES COMMANDES
     * =====================================================
     *
     * Endpoint :
     * GET /api/commandes
     *
     * Accessible aux utilisateurs authentifiés.
     */
    public function index()
    {
        $commandes = Commande::with('equipement')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des commandes récupérée avec succès.',
            'data' => $commandes,
        ]);
    }


    /**
     * =====================================================
     * HISTORIQUE DES COMMANDES D'UN ÉQUIPEMENT
     * =====================================================
     *
     * Endpoint :
     * GET /api/equipements/{equipement}/commandes
     */
    public function equipementCommandes($equipementId)
    {
        $commandes = Commande::where(
            'equipement_id',
            $equipementId
        )
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Historique des commandes récupéré avec succès.',
            'data' => $commandes,
        ]);
    }
}