<?php

use App\Http\Controllers\AiReviewController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\LegalCaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'org.access'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('casos', LegalCaseController::class)->names([
        'index'   => 'cases.index',
        'create'  => 'cases.create',
        'store'   => 'cases.store',
        'show'    => 'cases.show',
        'edit'    => 'cases.edit',
        'update'  => 'cases.update',
        'destroy' => 'cases.destroy',
    ]);

    Route::resource('documentos', DocumentController::class)->names([
        'index'   => 'documents.index',
        'create'  => 'documents.create',
        'store'   => 'documents.store',
        'show'    => 'documents.show',
        'edit'    => 'documents.edit',
        'update'  => 'documents.update',
        'destroy' => 'documents.destroy',
    ]);

    Route::resource('minutas', DraftController::class)->names([
        'index'   => 'drafts.index',
        'create'  => 'drafts.create',
        'store'   => 'drafts.store',
        'show'    => 'drafts.show',
        'edit'    => 'drafts.edit',
        'update'  => 'drafts.update',
        'destroy' => 'drafts.destroy',
    ]);

    Route::get('/revisor', [AiReviewController::class, 'index'])->name('review.index');
    Route::get('/configuracoes', [SettingsController::class, 'index'])->name('settings.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('organizations', Admin\OrganizationController::class)->names([
        'index'   => 'organizations.index',
        'create'  => 'organizations.create',
        'store'   => 'organizations.store',
        'show'    => 'organizations.show',
        'edit'    => 'organizations.edit',
        'update'  => 'organizations.update',
        'destroy' => 'organizations.destroy',
    ]);

    Route::get('/financeiro', [Admin\FinanceController::class, 'index'])->name('finance.index');
    Route::get('/chamados', [Admin\SupportController::class, 'index'])->name('support.index');
    Route::get('/leads', [Admin\LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/comparativo', [Admin\LeadController::class, 'comparison'])->name('leads.comparison');
});

require __DIR__.'/auth.php';
