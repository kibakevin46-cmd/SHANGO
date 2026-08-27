<?php

use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\EquipementController;
use App\Http\Controllers\Api\LocalisationController;
use Illuminate\Support\Facades\Route;


// =====================================================
// AUTHENTIFICATION
// =====================================================

Route::post('/login', [AuthController::class, 'login']);


// =====================================================
// ROUTES PROTEGEES
// =====================================================

Route::middleware('auth:sanctum')->group(function () {

    // -------------------------------------------------
    // DECONNEXION
    // -------------------------------------------------

    Route::post('/logout', [AuthController::class, 'logout']);


    // =================================================
    // SUPERADMIN + ADMIN
    // =================================================

    Route::middleware('role:superadmin,admin')->group(function () {

        // Utilisateurs
        Route::apiResource('users', UserController::class);

        // Organisations
        Route::apiResource('organizations', OrganizationController::class);

        // Equipements
        Route::apiResource('equipements', EquipementController::class);

        // Localisations
        Route::apiResource('localisations', LocalisationController::class);

        // Incidents
        Route::apiResource('incidents', IncidentController::class);


        // -------------------------------------------------
        // MAINTENANCES : ADMINISTRATION
        // -------------------------------------------------

        // Créer une maintenance
        Route::post('/maintenances', [MaintenanceController::class, 'store'])
            ->name('maintenances.store');

        // Supprimer une maintenance
        Route::delete('/maintenances/{maintenance}', [MaintenanceController::class, 'destroy'])
            ->name('maintenances.destroy');
    });


    // =================================================
    // SUPERADMIN + ADMIN + TECHNICIEN
    // =================================================

    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        // -------------------------------------------------
        // MAINTENANCES : CONSULTATION
        // -------------------------------------------------

        // Liste des maintenances
        Route::get('/maintenances', [MaintenanceController::class, 'index'])
            ->name('maintenances.index');

        // Voir une maintenance
        Route::get('/maintenances/{maintenance}', [MaintenanceController::class, 'show'])
            ->name('maintenances.show');


        // -------------------------------------------------
        // MAINTENANCES : MODIFICATION
        // -------------------------------------------------

        // Le technicien pourra modifier uniquement
        // le statut d'une maintenance qui lui est affectée.
        Route::put('/maintenances/{maintenance}', [MaintenanceController::class, 'update'])
            ->name('maintenances.update');

        Route::patch('/maintenances/{maintenance}', [MaintenanceController::class, 'update']);


        // -------------------------------------------------
        // INTERVENTIONS
        // -------------------------------------------------

        Route::apiResource('interventions', InterventionController::class);
    });
});