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
     *
     * Superadmin + Admin :
     *      voient tous les équipements.
     *
     * Technicien :
     *      voit uniquement les équipements qui lui sont affectés.
     */
    public function index()
    {
        $user = request()->user();

        $query = Equipement::with([
            'organization',
            'technicien'
        ]);

        // Le technicien ne voit que ses équipements.
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
     * Seuls Superadmin et Admin peuvent créer.
     *
     * La route est déjà protégée dans api.php.
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
     * =====================================================
     * AFFICHER UN ÉQUIPEMENT
     * =====================================================
     *
     * Superadmin + Admin :
     *      peuvent consulter n'importe quel équipement.
     *
     * Technicien :
     *      peut uniquement consulter un équipement
     *      qui lui est affecté.
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

        // Vérification de sécurité pour le technicien.
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
     * Seuls Superadmin et Admin peuvent modifier.
     *
     * Les routes sont déjà protégées dans api.php.
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
     * =====================================================
     * SUPPRIMER UN ÉQUIPEMENT
     * =====================================================
     *
     * Seuls Superadmin et Admin peuvent supprimer.
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
