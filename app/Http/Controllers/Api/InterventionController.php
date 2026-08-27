<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    /**
     * Liste des interventions.
     */
    public function index()
    {
        $user = request()->user();

        $query = Intervention::with([
            'maintenance',
            'equipement',
            'technicien'
        ]);

        // Le technicien ne voit que ses propres interventions.
        if ($user->role === 'technicien') {
            $query->where('technicien_id', $user->id);
        }

        $interventions = $query->latest()->get();

        return response()->json([
            'message' => 'Liste des interventions',
            'data' => $interventions,
        ]);
    }

    /**
     * Enregistrer une nouvelle intervention.
     */
    public function store(Request $request)
    {
        $user = request()->user();

        $validated = $request->validate([
            'maintenance_id' => [
                'nullable',
                'exists:maintenances,id'
            ],

            'equipement_id' => [
                'required',
                'exists:equipements,id'
            ],

            'technicien_id' => [
                'required',
                'exists:users,id'
            ],

            'date_intervention' => [
                'required',
                'date'
            ],

            'diagnostic' => [
                'nullable',
                'string'
            ],

            'actions_realisees' => [
                'nullable',
                'string'
            ],

            'pieces_utilisees' => [
                'nullable',
                'array'
            ],

            'etat_equipement_apres' => [
                'nullable',
                'string'
            ],

            'commentaire' => [
                'nullable',
                'string'
            ],

            'status' => [
                'nullable',
                'in:en_attente,en_cours,resolu,a_suivre'
            ],
        ]);

        /*
         * Si l'utilisateur connecté est un technicien,
         * il ne peut pas créer une intervention pour
         * un autre technicien.
         */
        if ($user->role === 'technicien') {

            if ((int) $validated['technicien_id'] !== (int) $user->id) {
                return response()->json([
                    'message' => 'Accès interdit. Vous ne pouvez créer une intervention que pour vous-même.'
                ], 403);
            }
        }

        $intervention = Intervention::create($validated);

        return response()->json([
            'message' => 'Intervention enregistrée avec succès.',
            'data' => $intervention->load([
                'maintenance',
                'equipement',
                'technicien'
            ]),
        ], 201);
    }

    /**
     * Afficher une intervention.
     */
    public function show(string $id)
    {
        $intervention = Intervention::with([
            'maintenance',
            'equipement',
            'technicien'
        ])->findOrFail($id);

        $user = request()->user();

        // Un technicien ne peut consulter que ses propres interventions.
        if (
            $user->role === 'technicien' &&
            $intervention->technicien_id !== $user->id
        ) {
            return response()->json([
                'message' => 'Accès interdit. Cette intervention ne vous est pas affectée.'
            ], 403);
        }

        return response()->json([
            'message' => 'Intervention trouvée.',
            'data' => $intervention,
        ]);
    }

    /**
     * Modifier une intervention.
     */
    public function update(Request $request, string $id)
    {
        $intervention = Intervention::findOrFail($id);

        $user = request()->user();

        // Le technicien ne peut modifier que ses propres interventions.
        if (
            $user->role === 'technicien' &&
            $intervention->technicien_id !== $user->id
        ) {
            return response()->json([
                'message' => 'Accès interdit. Cette intervention ne vous est pas affectée.'
            ], 403);
        }

        // Un technicien ne peut pas changer le technicien affecté.
        if (
            $user->role === 'technicien' &&
            $request->has('technicien_id')
        ) {
            return response()->json([
                'message' => 'Accès interdit. Un technicien ne peut pas modifier le technicien affecté.'
            ], 403);
        }

        $validated = $request->validate([
            'maintenance_id' => [
                'sometimes',
                'nullable',
                'exists:maintenances,id'
            ],

            'equipement_id' => [
                'sometimes',
                'exists:equipements,id'
            ],

            'date_intervention' => [
                'sometimes',
                'date'
            ],

            'diagnostic' => [
                'sometimes',
                'nullable',
                'string'
            ],

            'actions_realisees' => [
                'sometimes',
                'nullable',
                'string'
            ],

            'pieces_utilisees' => [
                'sometimes',
                'nullable',
                'array'
            ],

            'etat_equipement_apres' => [
                'sometimes',
                'nullable',
                'string'
            ],

            'commentaire' => [
                'sometimes',
                'nullable',
                'string'
            ],

            'status' => [
                'sometimes',
                'in:en_attente,en_cours,resolu,a_suivre'
            ],
        ]);

        $intervention->update($validated);

        /*
         * Si l'intervention est résolue,
         * la maintenance correspondante passe automatiquement
         * à "terminee".
         */
        if (
            $intervention->status === 'resolu' &&
            $intervention->maintenance_id
        ) {
            $intervention->maintenance->update([
                'status' => 'terminee',
            ]);
        }

        return response()->json([
            'message' => 'Intervention modifiée avec succès.',
            'data' => $intervention->load([
                'maintenance',
                'equipement',
                'technicien'
            ]),
        ]);
    }

    /**
     * Supprimer une intervention.
     */
    public function destroy(string $id)
    {
        $user = request()->user();

        // Le technicien ne peut pas supprimer une intervention.
        if ($user->role === 'technicien') {
            return response()->json([
                'message' => 'Accès interdit. Les techniciens ne peuvent pas supprimer une intervention.'
            ], 403);
        }

        $intervention = Intervention::findOrFail($id);

        $intervention->delete();

        return response()->json([
            'message' => 'Intervention supprimée avec succès.',
        ]);
    }
}