<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TherapyController;
use App\Http\Controllers\CalculatorsController;

// Home page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Calculator routes
Route::get('/calculators', [CalculatorsController::class, 'index'])->name('calculators.index');
Route::get('/calculators/diagnostics', [CalculatorsController::class, 'diagnostics'])->name('calculators.diagnostics');
Route::get('/calculators/prognosis', [CalculatorsController::class, 'prognosis'])->name('calculators.prognosis');

// Therapy routes
Route::prefix('therapy')->name('therapy.')->group(function () {
    // Article form (new study entry)
    Route::get('/article-form', [TherapyController::class, 'articleForm'])->name('article.form');
    
    // Studies list
    Route::get('/studies', [TherapyController::class, 'studiesList'])->name('studies.list');
    
    // Delete study
    Route::delete('/studies/{id}', [TherapyController::class, 'deleteStudy'])->name('study.delete');
    
    // Individualization list for a study
    Route::get('/studies/{id}/individualizations', [TherapyController::class, 'indList'])->name('ind.list');

    // Individualization create/store
    Route::get('/studies/{id}/individualizations/new', [TherapyController::class, 'indCreate'])->name('ind.create');
    Route::post('/studies/{id}/individualizations', [TherapyController::class, 'indStore'])->name('ind.store');

    // Individualization results
    Route::get('/individualizations/{id}', [TherapyController::class, 'indResults'])->name('ind.results');
    
    // Reading journal (list view)
    Route::get('/reading-journal', [TherapyController::class, 'readingJournal'])->name('reading.journal');
    
    // Reading journal form (intermediate step)
    Route::post('/reading-journal-form', [TherapyController::class, 'readingJournalForm'])->name('reading.journal.post');
    
    // Compute results (form submission)
    Route::post('/compute-results', [TherapyController::class, 'computeResults'])->name('compute.results.post');
    
    // DOI autofetch
    Route::post('/doi-autofetch', [TherapyController::class, 'doiAutofetchSave'])->name('doi.autofetch');
});
