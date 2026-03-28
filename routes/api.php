<?php

use App\Http\Controllers\Api\Admin\AdminPublicationController;
use App\Http\Controllers\Api\Admin\AdminStatsController;
use App\Http\Controllers\Api\AppraisalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IndividualizationController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\ProfessionalController;
use App\Http\Controllers\Api\PublicationController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Sanctum-protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('appraisals',
        AppraisalController::class);

    Route::apiResource('library',
        LibraryController::class)->only(['index', 'store', 'destroy']);

    Route::apiResource('individualization',
        IndividualizationController::class)->only(['index', 'store']);

    // Admin-only routes
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

        Route::get('/stats',
            [AdminStatsController::class, 'index']);

        Route::apiResource('publications',
            AdminPublicationController::class);
    });
});

// Public publication routes
Route::get('/publications',
    [PublicationController::class, 'index']);
Route::get('/publications/{slug}',
    [PublicationController::class, 'show']);
Route::post('/publications/{slug}/view',
    [PublicationController::class, 'recordView']);
Route::post('/publications/{slug}/share',
    [PublicationController::class, 'recordShare']);
Route::get('/publications/{slug}/stats',
    [PublicationController::class, 'stats']);
Route::get('/publications/{slug}/pdf',
    [PublicationController::class, 'pdf']);

// Public content routes
Route::get('/journal',       [JournalController::class, 'index']);
Route::get('/professional',  [ProfessionalController::class, 'index']);
