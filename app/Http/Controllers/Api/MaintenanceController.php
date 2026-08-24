<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Liste des maintenances.
     */
    public function index()
{
    $user = Auth::user();

    $query = Maintenance::with([
        'equipement',
        'incident',
        'requester',
        'validator',
        'assignedTechnicien'
    ]);

    // Le technicien ne voit que les maintenances
    // qui lui sont affectées.
    if ($user->role === 'technicien') {
        $query->where('assigned_to', $user->id);
    }

    $maintenances = $query->latest()->get();

    return response()->json([
        'message' => 'Liste des maintenances',
        'data' => $maintenances,
    ]);
}

    /**
     * Enregistrer une nouvelle maintenance.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipement_id' => ['required', 'exists:equipements,id'],
            'incident_id' => ['nullable', 'exists:incidents,id'],
            'type' => ['required', 'in:preventive,corrective'],
            'requested_by' => ['nullable', 'exists:users,id'],
            'validated_by' => ['nullable', 'exists:users,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:demandee,validee,planifiee,en_cours,terminee'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $maintenance = Maintenance::create($validated);

        return response()->json([
            'message' => 'Maintenance enregistrée avec succès.',
            'data' => $maintenance->load([
                'equipement',
                'incident',
                'requester',
                'validator',
                'assignedTechnicien'
            ]),
        ], 201);
    }

    /**
     * Afficher une maintenance.
     */
    public function show(string $id)
    {
        $maintenance = Maintenance::with([
            'equipement',
            'incident',
            'requester',
            'validator',
            'assignedTechnicien'
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Maintenance trouvée.',
            'data' => $maintenance,
        ]);
    }

    /**
     * Modifier une maintenance.
     */
    public function update(Request $request, string $id)
    {
        $maintenance = Maintenance::findOrFail($id);

        $validated = $request->validate([
            'equipement_id' => ['sometimes', 'exists:equipements,id'],
            'incident_id' => ['sometimes', 'nullable', 'exists:incidents,id'],
            'type' => ['sometimes', 'in:preventive,corrective'],
            'requested_by' => ['sometimes', 'nullable', 'exists:users,id'],
            'validated_by' => ['sometimes', 'nullable', 'exists:users,id'],
            'assigned_to' => ['sometimes', 'nullable', 'exists:users,id'],
            'status' => ['sometimes', 'in:demandee,validee,planifiee,en_cours,terminee'],
            'scheduled_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $maintenance->update($validated);

        return response()->json([
            'message' => 'Maintenance modifiée avec succès.',
            'data' => $maintenance->load([
                'equipement',
                'incident',
                'requester',
                'validator',
                'assignedTechnicien'
            ]),
        ]);
    }

    /**
     * Supprimer une maintenance.
     */
    public function destroy(string $id)
    {
        $maintenance = Maintenance::findOrFail($id);

        $maintenance->delete();

        return response()->json([
            'message' => 'Maintenance supprimée avec succès.',
        ]);
    }
}