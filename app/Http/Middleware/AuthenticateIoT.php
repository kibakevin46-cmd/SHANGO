<?php

namespace App\Http\Middleware;

use App\Models\IotCredential;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateIoT
{
    /**
     * Authentifie un appareil IoT avec son identifiant et sa clé secrète.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Récupération des identifiants envoyés dans les headers HTTP
        $deviceId = $request->header('X-Device-ID');
        $apiKey = $request->header('X-IoT-Key');

        // Vérification de la présence des deux identifiants
        if (!$deviceId || !$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants IoT manquants.',
            ], 401);
        }

        // Recherche du credential correspondant au boîtier
        $credential = IotCredential::where('device_id', $deviceId)
            ->where('actif', true)
            ->first();

        // Boîtier inconnu ou désactivé
        if (!$credential) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiant IoT invalide ou désactivé.',
            ], 401);
        }

        // Vérification de la clé secrète
        if (!Hash::check($apiKey, $credential->api_key_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Clé IoT invalide.',
            ], 401);
        }

        // Mise à jour de la dernière utilisation
        $credential->update([
            'last_used_at' => now(),
        ]);

        // Identifiants valides : la requête peut continuer
        return $next($request);
    }
}