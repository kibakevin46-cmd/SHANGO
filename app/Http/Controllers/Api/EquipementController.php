<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipementController extends Controller
{
    /**
     * Afficher la liste des équipements.
     */
    public function index()
    {
        $equipements = Equipement::with([
            'organization',
            'technicien'
        ])->get();

        return response()->json([
            'message' => 'Liste des équipements',
            'data' => $equipements
        ]);
    }

    /**
     * Créer un équipement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => [
                'required',
                'exists:organizations,id'
            ],

            'type' => [
                'required',
                'string',
                'max:255'
            ],

            'reference' => [
                'required',
                'string',
                'max:255',
                'unique:equipements,reference'
            ],

            'technicien_id' => [
                'nullable',
                'exists:users,id'
            ],

            'status' => [
                'required',
                Rule::in([
                    'actif',
                    'en_panne',
                    'maintenance',
                    'hors_service'
                ])
            ],

            'installed_at' => [
                'nullable',
                'date'
            ],
        ]);

        $equipement = Equipement::create($validated);

        return response()->json([
            'message' => 'Équipement créé avec succès.',
            'data' => $equipement->load([
                'organization',
                'technicien'
            ])
        ], 201);
    }

    /**
     * Afficher un équipement précis.
     */
    public function show(string $id)
    {
        $equipement = Equipement::with([
            'organization',
            'technicien',
            'localisations',
            'incidents',
            'maintenances',
            'interventions'
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Équipement trouvé.',
            'data' => $equipement
        ]);
    }

    /**
     * Modifier un équipement.
     */
    public function update(Request $request, string $id)
    {
        $equipement = Equipement::findOrFail($id);

        $validated = $request->validate([
            'organization_id' => [
                'sometimes',
                'exists:organizations,id'
            ],

            'type' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'reference' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('equipements', 'reference')
                    ->ignore($equipement->id)
            ],

            'technicien_id' => [
                'nullable',
                'exists:users,id'
            ],

            'status' => [
                'sometimes',
                Rule::in([
                    'actif',
                    'en_panne',
                    'maintenance',
                    'hors_service'
                ])
            ],

            'installed_at' => [
                'nullable',
                'date'
            ],
        ]);

        $equipement->update($validated);

        return response()->json([
            'message' => 'Équipement modifié avec succès.',
            'data' => $equipement->fresh()->load([
                'organization',
                'technicien'
            ])
        ]);
    }

    /**
     * Supprimer un équipement.
     */
    public function destroy(string $id)
    {
        $equipement = Equipement::findOrFail($id);

        $equipement->delete();

        return response()->json([
            'message' => 'Équipement supprimé avec succès.'
        ]);
    }
}