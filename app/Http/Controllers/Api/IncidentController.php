<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    /**
     * Liste des incidents.
     */
    public function index()
    {
        $incidents = Incident::with(['equipement', 'reporter'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Liste des incidents',
            'data' => $incidents,
        ]);
    }

    /**
     * Enregistrer un nouvel incident.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipement_id' => ['required', 'exists:equipements,id'],
            'reported_by' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'in:basse,moyenne,haute'],
            'status' => ['nullable', 'in:en_attente,en_cours,resolu,a_suivre'],
        ]);

        $incident = Incident::create($validated);

        return response()->json([
            'message' => 'Incident enregistré avec succès.',
            'data' => $incident->load(['equipement', 'reporter']),
        ], 201);
    }

    /**
     * Afficher un incident.
     */
    public function show(string $id)
    {
        $incident = Incident::with(['equipement', 'reporter'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Incident trouvé.',
            'data' => $incident,
        ]);
    }

    /**
     * Modifier un incident.
     */
    public function update(Request $request, string $id)
    {
        $incident = Incident::findOrFail($id);

        $validated = $request->validate([
            'equipement_id' => ['sometimes', 'exists:equipements,id'],
            'reported_by' => ['sometimes', 'nullable', 'exists:users,id'],
            'type' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'in:basse,moyenne,haute'],
            'status' => ['sometimes', 'in:en_attente,en_cours,resolu,a_suivre'],
        ]);

        $incident->update($validated);

        return response()->json([
            'message' => 'Incident modifié avec succès.',
            'data' => $incident->load(['equipement', 'reporter']),
        ]);
    }

    /**
     * Supprimer un incident.
     */
    public function destroy(string $id)
    {
        $incident = Incident::findOrFail($id);

        $incident->delete();

        return response()->json([
            'message' => 'Incident supprimé avec succès.',
        ]);
    }
}
