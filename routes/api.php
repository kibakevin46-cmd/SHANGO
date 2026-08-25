<?php

use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\IncidentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\EquipementController;
use App\Http\Controllers\Api\LocalisationController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Superadmin + Admin
    Route::middleware('role:superadmin,admin')->group(function () {

        Route::apiResource('users', UserController::class);
        Route::apiResource('organizations', OrganizationController::class);
        Route::apiResource('equipements', EquipementController::class);
        Route::apiResource('localisations', LocalisationController::class);
        Route::apiResource('incidents', IncidentController::class);
        Route::apiResource('maintenances', MaintenanceController::class);
    });

    // Superadmin + Admin + Technicien
    Route::middleware('role:superadmin,admin,technicien')->group(function () {

        Route::apiResource('interventions', InterventionController::class);
    });
});