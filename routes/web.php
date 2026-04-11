<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompaniesHouseCompanyPreviewController;
use App\Http\Controllers\CompaniesHouseLookupController;
use App\Http\Controllers\CompaniesHouseSearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::post('lookup/companies-house', CompaniesHouseLookupController::class)
        ->name('lookup.companies-house');
    Route::post('lookup/companies-house/search', CompaniesHouseSearchController::class)
        ->name('lookup.companies-house.search');
    Route::post('lookup/companies-house/preview', CompaniesHouseCompanyPreviewController::class)
        ->name('lookup.companies-house.preview');

    Route::resource('clients', ClientController::class);

    Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::resource('tasks', TaskController::class)->except(['create', 'store']);

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings/firm', [SettingsController::class, 'updateFirm'])
        ->middleware('tenant.admin')
        ->name('settings.firm.update');
    Route::post('settings/team', [SettingsController::class, 'storeTeamMember'])
        ->middleware('tenant.admin')
        ->name('settings.team.store');
    Route::delete('settings/team/{teamMember}', [SettingsController::class, 'destroyTeamMember'])
        ->middleware('tenant.admin')
        ->name('settings.team.destroy');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
