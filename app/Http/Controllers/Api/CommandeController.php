<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
     *
     * Exemple :
     * {
     *     "id_appareil": "SH-001",
     *     "commande": "BLOQUER",
     *     "emis_par": "systeme_payg",
     *     "raison": "impaye"
     * }
     */
    public function store(Request $request, BridgeService $bridgeService)
    {
        // Validation des données reçues
        $validated = $request->validate([
            'id_appareil' => 'required|string|max:255',
            'commande' => 'required|string|max:50',
            'emis_par' => 'required|string|max:100',
            'raison' => 'nullable|string|max:255',
        ]);

        // Recherche de l'équipement grâce au device_id
        $equipement = Equipement::where(
            'device_id',
            $validated['id_appareil']
        )->first();

        // Vérifier que le boîtier existe
        if (!$equipement) {
            return response()->json([
                'success' => false,
                'message' => 'Équipement introuvable pour cet id_appareil.',
                'id_appareil' => $validated['id_appareil'],
            ], 404);
        }

        // Préparer les données à envoyer au Bridge
        $commandeBridge = [
            'id_appareil' => $validated['id_appareil'],
            'commande' => $validated['commande'],
            'emis_par' => $validated['emis_par'],
            'raison' => $validated['raison'] ?? null,
        ];

        // Envoyer la commande au Bridge MQTT
        $responseBridge = $bridgeService->envoyerCommande($commandeBridge);

        // Vérifier si le Bridge a accepté la commande
        if (!$responseBridge->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de transmettre la commande au Bridge MQTT.',
                'bridge_status' => $responseBridge->status(),
                'bridge_response' => $responseBridge->json(),
            ], 502);
        }

        // Réponse finale
        return response()->json([
            'success' => true,
            'message' => 'Commande reçue et transmise au Bridge MQTT avec succès.',
            'data' => [
                'equipement_id' => $equipement->id,
                'device_id' => $equipement->device_id,
                'commande' => $validated['commande'],
                'emis_par' => $validated['emis_par'],
                'raison' => $validated['raison'] ?? null,
            ],
        ], 201);
    }
}