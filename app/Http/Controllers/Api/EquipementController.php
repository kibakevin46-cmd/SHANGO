<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipementController extends Controller
{
    /**
     * =====================================================
     * LISTE DES ÉQUIPEMENTS
     * =====================================================
     */
    public function index()
    {
        $user = request()->user();

        $query = Equipement::with([
            'organization',
            'technicien'
        ]);

        // Un technicien voit seulement ses équipements.
        if ($user->role === 'technicien') {
            $query->where('technicien_id', $user->id);
        }

        $equipements = $query->latest()->get();

        return response()->json([
            'message' => 'Liste des équipements',
            'data' => $equipements
        ]);
    }


    /**
     * =====================================================
     * CRÉER UN ÉQUIPEMENT
     * =====================================================
     *
     * Seuls les Admin et Superadmin peuvent créer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            // Organisation obligatoire
            'organization_id' => [
                'required',
                'exists:organizations,id'
            ],

            // Type de l'équipement
            'type' => [
                'required',
                'string',
                'max:255'
            ],

            // Référence unique
            'reference' => [
                'required',
                'string',
                'max:255',
                'unique:equipements,reference'
            ],

            // Identifiant unique du boîtier IoT
            'device_id' => [
                'required',
                'string',
                'max:255',
                'unique:equipements,device_id'
            ],

            // Technicien facultatif
            // Mais s'il est fourni, il doit être un technicien actif.
            'technicien_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'technicien')
                          ->where('status', 'actif');
                })
            ],

            // Statut de l'équipement
            'status' => [
                'required',
                Rule::in([
                    'actif',
                    'en_panne',
                    'maintenance',
                    'hors_service'
                ])
            ],

            // État du kit SHANGO V3.0
            'etat_kit' => [
                'nullable',
                Rule::in([
                    'MARCHE',
                    'BLOQUE'
                ])
            ],

            // Date d'installation facultative
            'installed_at' => [
                'nullable',
                'date'
            ],
        ]);

        // Création de l'équipement
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
     * =====================================================
     * AFFICHER UN ÉQUIPEMENT
     * =====================================================
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

        $user = request()->user();

        // Un technicien ne peut voir que son propre équipement.
        if (
            $user->role === 'technicien' &&
            (int) $equipement->technicien_id !== (int) $user->id
        ) {
            return response()->json([
                'message' => 'Accès interdit. Cet équipement ne vous est pas affecté.'
            ], 403);
        }

        return response()->json([
            'message' => 'Équipement trouvé.',
            'data' => $equipement
        ]);
    }


    /**
     * =====================================================
     * MODIFIER UN ÉQUIPEMENT
     * =====================================================
     *
     * PATCH permet de modifier seulement les champs envoyés.
     */
    public function update(Request $request, string $id)
    {
        $equipement = Equipement::findOrFail($id);

        $validated = $request->validate([

            // Organisation
            'organization_id' => [
                'sometimes',
                'exists:organizations,id'
            ],

            // Type
            'type' => [
                'sometimes',
                'string',
                'max:255'
            ],

            // Référence unique
            'reference' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('equipements', 'reference')
                    ->ignore($equipement->id)
            ],

            // Device ID unique
            'device_id' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('equipements', 'device_id')
                    ->ignore($equipement->id)
            ],

            // Technicien actif uniquement
            'technicien_id' => [
                'sometimes',
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'technicien')
                          ->where('status', 'actif');
                })
            ],

            // Statut
            'status' => [
                'sometimes',
                Rule::in([
                    'actif',
                    'en_panne',
                    'maintenance',
                    'hors_service'
                ])
            ],

            // État du kit SHANGO V3.0
            'etat_kit' => [
                'sometimes',
                'nullable',
                Rule::in([
                    'MARCHE',
                    'BLOQUE'
                ])
            ],

            // Date d'installation
            'installed_at' => [
                'sometimes',
                'nullable',
                'date'
            ],
        ]);

        // Mise à jour uniquement des champs envoyés.
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
     * =====================================================
     * SUPPRIMER UN ÉQUIPEMENT
     * =====================================================
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