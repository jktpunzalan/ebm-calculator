<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppraisalController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\IndividualizationController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\ProfessionalController;
use App\Http\Controllers\Api\AdminController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::post('/auth/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Task 4 — Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('appraisals', AppraisalController::class);
    Route::apiResource('library', LibraryController::class)->only(['index','store','destroy']);
    Route::get('individualization',  [IndividualizationController::class, 'index']);
    Route::post('individualization', [IndividualizationController::class, 'store']);
});

// Task 5 — Publications (public)
Route::get('publications',               [PublicationController::class, 'index']);
Route::get('publications/{slug}',        [PublicationController::class, 'show']);
Route::get('publications/{slug}/stats',  [PublicationController::class, 'stats']);
Route::post('publications/{slug}/view',  [PublicationController::class, 'recordView']);
Route::post('publications/{slug}/share', [PublicationController::class, 'recordEvent']);

// Task 6 — Journal + Professional (public)
Route::get('journal',      [JournalController::class, 'index']);
Route::get('professional', [ProfessionalController::class, 'index']);

// Task 7 — Admin (auth + role:admin)
Route::middleware(['auth:sanctum','role:admin'])->prefix('admin')->group(function () {
    Route::get('stats', [AdminController::class,'stats']);
    Route::post('publications',        [AdminController::class,'store']);
    Route::put('publications/{id}',    [AdminController::class,'update']);
    Route::delete('publications/{id}', [AdminController::class,'destroy']);
    Route::post('journal',             [AdminController::class,'storeArticle']);
    Route::put('journal/{id}',         [AdminController::class,'updateArticle']);
    Route::delete('journal/{id}',      [AdminController::class,'destroyArticle']);
    Route::post('professional',        [AdminController::class,'storeProfessional']);
    Route::put('professional/{id}',    [AdminController::class,'updateProfessional']);
    Route::delete('professional/{id}', [AdminController::class,'destroyProfessional']);
});
