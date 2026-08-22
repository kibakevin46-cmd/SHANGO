<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Vérifie que l'utilisateur possède l'un des rôles autorisés.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié.',
            ], 401);
        }

        // Vérifier que son rôle est autorisé
        if (!in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Accès interdit. Vous n’avez pas les permissions nécessaires.',
            ], 403);
        }

        return $next($request);
    }
}