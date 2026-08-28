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
// ROUTES PROTÉGÉES
// =====================================================

Route::middleware('auth:sanctum')->group(function () {

    // =================================================
    // DÉCONNEXION
    // =================================================

    Route::post('/logout', [AuthController::class, 'logout']);


    // =================================================
    // SUPERADMIN + ADMIN
    // =================================================

    Route::middleware('role:superadmin,admin')->group(function () {

        // -------------------------------------------------
        // UTILISATEURS
        // -------------------------------------------------

        Route::apiResource('users', UserController::class);


        // -------------------------------------------------
        // ORGANISATIONS
        // -------------------------------------------------

        Route::apiResource('organizations', OrganizationController::class);


        // -------------------------------------------------
        // EQUIPEMENTS
        // -------------------------------------------------

        Route::apiResource('equipements', EquipementController::class);


        // -------------------------------------------------
        // LOCALISATIONS
        // -------------------------------------------------

        Route::apiResource('localisations', LocalisationController::class);


        // =================================================
        // INCIDENTS - ADMINISTRATION
        // =================================================

        // Créer un incident
        Route::post('/incidents', [IncidentController::class, 'store'])
            ->name('incidents.store');

        // Modifier complètement un incident
        Route::put('/incidents/{incident}', [IncidentController::class, 'update'])
            ->name('incidents.update');

        // Modifier complètement un incident avec PATCH
        Route::patch('/incidents/{incident}', [IncidentController::class, 'update']);

        // Supprimer un incident
        Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy'])
            ->name('incidents.destroy');


        // =================================================
        // MAINTENANCES - ADMINISTRATION
        // =================================================

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


        // =================================================
        // INCIDENTS
        // =================================================

        // Consulter la liste des incidents
        Route::get('/incidents', [IncidentController::class, 'index'])
            ->name('incidents.index');

        // Consulter un incident précis
        Route::get('/incidents/{incident}', [IncidentController::class, 'show'])
            ->name('incidents.show');

        // Modifier uniquement le statut d'un incident
        Route::patch('/incidents/{incident}/status', [IncidentController::class, 'updateStatus'])
            ->name('incidents.updateStatus');


        // =================================================
        // MAINTENANCES
        // =================================================

        // Consulter la liste des maintenances
        Route::get('/maintenances', [MaintenanceController::class, 'index'])
            ->name('maintenances.index');

        // Consulter une maintenance
        Route::get('/maintenances/{maintenance}', [MaintenanceController::class, 'show'])
            ->name('maintenances.show');

        // Modifier une maintenance
        Route::put('/maintenances/{maintenance}', [MaintenanceController::class, 'update'])
            ->name('maintenances.update');

        // Modifier une maintenance avec PATCH
        Route::patch('/maintenances/{maintenance}', [MaintenanceController::class, 'update']);


        // =================================================
        // INTERVENTIONS
        // =================================================

        Route::apiResource('interventions', InterventionController::class);
    });
});