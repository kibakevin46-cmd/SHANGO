<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\EquipementController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {

    

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:superadmin,admin')->group(function () {

        // Gestion des utilisateurs
        Route::apiResource('users', UserController::class);

        // Gestion des organisations
        Route::apiResource('organizations', OrganizationController::class);

        // Gestion des équipements
        Route::apiResource('equipements', EquipementController::class);
    });
});