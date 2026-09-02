<?php

namespace App\Services;

use App\Models\Alerte;
use App\Models\Telemetrie;

class AlerteDetectionService
{
    /**
     * Détecter automatiquement les anomalies
     * à partir d'une télémétrie.
     */
    public function detecter(Telemetrie $telemetrie): void
    {
        // =================================================
        // 1. TEMPÉRATURE ÉLEVÉE
        // =================================================

        if (
            $telemetrie->temperature !== null &&
            $telemetrie->temperature >= 60
        ) {
            $this->creerAlerte(
                $telemetrie,
                'TEMPERATURE',
                'ELEVEE',
                $telemetrie->temperature
            );
        }

        // =================================================
        // 2. TENSION FAIBLE
        // =================================================

        if (
            $telemetrie->tension !== null &&
            $telemetrie->tension < 10
        ) {
            $this->creerAlerte(
                $telemetrie,
                'TENSION_FAIBLE',
                'MOYENNE',
                $telemetrie->tension
            );
        }

        // =================================================
        // 3. COURANT ÉLEVÉ
        // =================================================

        if (
            $telemetrie->courant !== null &&
            $telemetrie->courant > 10
        ) {
            $this->creerAlerte(
                $telemetrie,
                'COURANT_ELEVE',
                'ELEVEE',
                $telemetrie->courant
            );
        }

        // =================================================
        // 4. SIGNAL GSM FAIBLE
        // =================================================

        if (
            $telemetrie->signal_gsm !== null &&
            $telemetrie->signal_gsm < -90
        ) {
            $this->creerAlerte(
                $telemetrie,
                'SIGNAL_GSM_FAIBLE',
                'MOYENNE',
                $telemetrie->signal_gsm
            );
        }
    }

    /**
     * Créer une alerte automatiquement.
     */
    private function creerAlerte(
        Telemetrie $telemetrie,
        string $type,
        string $gravite,
        $valeur
    ): void {
        Alerte::create([
            'equipement_id' => $telemetrie->equipement_id,
            'device_id' => $telemetrie->device_id,
            'type_alerte' => $type,
            'gravite' => $gravite,
            'statut' => 'nouvelle',
            'valeur' => $valeur,
            'horodatage' => $telemetrie->horodatage,
        ]);
    }
}