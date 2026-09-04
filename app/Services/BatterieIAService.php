<?php

namespace App\Services;

class BatterieIAService
{
    /**
     * Prépare les données de batterie pour le modèle IA.
     *
     * L'ordre attendu par le modèle est :
     * 1. Tension par cellule
     * 2. Courant
     * 3. Température
     * 4. DoD
     */
    public function preparerDonnees(
        float $voltage,
        float $current,
        float $temperature,
        float $dod
    ): array {
        // Alioth possède 4 cellules.
        $voltageCellule = $voltage / 4;

        return [
            $voltageCellule,
            $current,
            $temperature,
            $dod,
        ];
    }

    /**
     * Prépare les données dans un format
     * qui pourra être envoyé au futur service Python.
     *
     * Pour le moment, aucune requête Python n'est effectuée.
     */
    public function preparerRequeteIA(
        string $deviceId,
        array $donneesIA
    ): array {
        return [
            'device_id' => $deviceId,

            'features' => $donneesIA,
        ];
    }
}