<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyShangoApiKey
{
    /**
     * Vérifier la clé API utilisée par le Bridge IoT.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Récupérer la clé envoyée dans le header
        $apiKey = $request->header('X-API-Key');

        // Récupérer la clé configurée dans le fichier .env
        $expectedApiKey = env('SHANGO_IOT_API_KEY');

        // Vérifier la clé
        if (!$apiKey || $apiKey !== $expectedApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Clé API IoT invalide ou absente.',
            ], 401);
        }

        // Clé correcte → continuer vers le contrôleur
        return $next($request);
    }
}