<?php

use Illuminate\Support\Facades\Route;

// =====================================================
// CONTROLLERS
// =====================================================

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\EquipementController;
use App\Http\Controllers\Api\LocalisationController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\TelemetrieController;
use App\Http\Controllers\Api\AlerteController;
use App\Http\Controllers\Api\CommandeController;


// =====================================================
// AUTHENTIFICATION
// =====================================================

// Connexion
Route::post('/login', [AuthController::class, 'login'])
    ->name('login');


// =====================================================
// TÉLÉMÉTRIE - BOÎTIERS IoT
// =====================================================

// Réception des données envoyées par le boîtier IoT / Bridge
Route::post('/telemetrie', [TelemetrieController::class, 'store'])
    ->name('telemetrie.store')
    ->middleware('shango.api');


// =====================================================
// ALERTES - BOÎTIERS IoT
// =====================================================

// Réception des alertes envoyées par le Bridge IoT
Route::post('/alertes', [AlerteController::class, 'store'])
    ->middleware('shango.api')
    ->name('alertes.store');


// =====================================================
// COMMANDES - BOÎTIERS IoT
// =====================================================

// Envoi d'une commande depuis Laravel vers le Bridge IoT
Route::post('/commande', [CommandeController::class, 'store'])
    ->name('commande.store');


// =====================================================
// ROUTES PROTÉGÉES
// =====================================================

Route::middleware('auth:sanctum')->group(function () {

    // =================================================
    // UTILISATEUR CONNECTÉ
    // =================================================

    Route::get('/me', [AuthController::class, 'me'])
        ->name('me');


    // =================================================
    // DÉCONNEXION
    // =================================================

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


    // =====================================================
    // SUPERADMIN + ADMIN
    // =====================================================

    Route::middleware('role:superadmin,admin')->group(function () {

        // =================================================
        // UTILISATEURS
        // =================================================

        Route::apiResource('users', UserController::class);


        // =================================================
        // ORGANISATIONS
        // =================================================

        Route::apiResource('organizations', OrganizationController::class);


        // =================================================
        // ÉQUIPEMENTS - ADMINISTRATION
        // =================================================

        // Créer
        Route::post('/equipements', [EquipementController::class, 'store'])
            ->name('equipements.store');

        // Modifier
        Route::put('/equipements/{equipement}', [EquipementController::class, 'update'])
            ->name('equipements.update');

        Route::patch('/equipements/{equipement}', [EquipementController::class, 'update']);

        // Supprimer
        Route::delete('/equipements/{equipement}', [EquipementController::class, 'destroy'])
            ->name('equipements.destroy');


        // =================================================
        // LOCALISATIONS - ADMINISTRATION
        // =================================================

        // Créer
        Route::post('/localisations', [LocalisationController::class, 'store'])
            ->name('localisations.store');

        // Modifier
        Route::put('/localisations/{localisation}', [LocalisationController::class, 'update'])
            ->name('localisations.update');

        Route::patch('/localisations/{localisation}', [LocalisationController::class, 'update']);

        // Supprimer
        Route::delete('/localisations/{localisation}', [LocalisationController::class, 'destroy'])
            ->name('localisations.destroy');


        // =================================================
        // INCIDENTS - ADMINISTRATION
        // =================================================

        // Créer
        Route::post('/incidents', [IncidentController::class, 'store'])
            ->name('incidents.store');

        // Modifier complètement
        Route::put('/incidents/{incident}', [IncidentController::class, 'update'])
            ->name('incidents.update');

        Route::patch('/incidents/{incident}', [IncidentController::class, 'update']);

        // Supprimer
        Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy'])
            ->name('incidents.destroy');


        // =================================================
        // MAINTENANCES - ADMINISTRATION
        // =================================================

        // Créer
        Route::post('/maintenances', [MaintenanceController::class, 'store'])
            ->name('maintenances.store');

        // Supprimer
        Route::delete('/maintenances/{maintenance}', [MaintenanceController::class, 'destroy'])
            ->name('maintenances.destroy');
    });


    // =====================================================
    // CONSULTATION DES ÉQUIPEMENTS
    // SUPERADMIN + ADMIN + TECHNICIEN
    // =====================================================

    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        // Liste
        Route::get('/equipements', [EquipementController::class, 'index'])
            ->name('equipements.index');

        // Détail
        Route::get('/equipements/{equipement}', [EquipementController::class, 'show'])
            ->name('equipements.show');
    });


    // =====================================================
    // INCIDENTS
    // SUPERADMIN + ADMIN + TECHNICIEN
    // =====================================================

    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        // Liste
        Route::get('/incidents', [IncidentController::class, 'index'])
            ->name('incidents.index');

        // Détail
        Route::get('/incidents/{incident}', [IncidentController::class, 'show'])
            ->name('incidents.show');

        // Modifier uniquement le statut
        Route::patch('/incidents/{incident}/status', [IncidentController::class, 'updateStatus'])
            ->name('incidents.updateStatus');
    });


    // =====================================================
    // MAINTENANCES
    // SUPERADMIN + ADMIN + TECHNICIEN
    // =====================================================

    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        // Liste
        Route::get('/maintenances', [MaintenanceController::class, 'index'])
            ->name('maintenances.index');

        // Détail
        Route::get('/maintenances/{maintenance}', [MaintenanceController::class, 'show'])
            ->name('maintenances.show');

        // Modifier
        Route::put('/maintenances/{maintenance}', [MaintenanceController::class, 'update'])
            ->name('maintenances.update');

        Route::patch('/maintenances/{maintenance}', [MaintenanceController::class, 'update']);
    });


    // =====================================================
    // INTERVENTIONS
    // SUPERADMIN + ADMIN + TECHNICIEN
    // =====================================================

    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        Route::apiResource('interventions', InterventionController::class);
    });


    // =====================================================
    // LOCALISATIONS - LECTURE
    // SUPERADMIN + ADMIN + TECHNICIEN
    // =====================================================

    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        // Liste
        Route::get('/localisations', [LocalisationController::class, 'index'])
            ->name('localisations.index');

        // Détail
        Route::get('/localisations/{localisation}', [LocalisationController::class, 'show'])
            ->name('localisations.show');
    });


    // =====================================================
    // TÉLÉMÉTRIES - CONSULTATION
    // SUPERADMIN + ADMIN + TECHNICIEN
    // =====================================================

    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        // Liste des télémétries
        Route::get('/telemetries', [TelemetrieController::class, 'index'])
            ->name('telemetries.index');

        // Historique des télémétries d'un équipement
        Route::get(
            '/equipements/{equipement}/telemetries',
            [TelemetrieController::class, 'equipementTelemetries']
        )->name('equipements.telemetries');
    });


    // =====================================================
    // ALERTES
    // SUPERADMIN + ADMIN + TECHNICIEN
    // =====================================================

    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        // Liste des alertes
        Route::get('/alertes', [AlerteController::class, 'index'])
            ->name('alertes.index');

        // Historique des alertes d'un équipement
        Route::get(
            '/equipements/{equipement}/alertes',
            [AlerteController::class, 'equipementAlertes']
        )->name('equipements.alertes');

        // Modifier le statut d'une alerte
        Route::patch(
            '/alertes/{alerte}/status',
            [AlerteController::class, 'updateStatus']
        )->name('alertes.updateStatus');
    });


    // =====================================================
    // COMMANDES - CONSULTATION
    // SUPERADMIN + ADMIN + TECHNICIEN
    // =====================================================

    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        // Liste de toutes les commandes
        Route::get('/commandes', [CommandeController::class, 'index'])
            ->name('commandes.index');

        // Historique des commandes d'un équipement
        Route::get(
            '/equipements/{equipement}/commandes',
            [CommandeController::class, 'equipementCommandes']
        )->name('equipements.commandes');
    });

});