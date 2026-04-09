<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentChargeTypeController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehiclePhotoController;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\ViolationTypeController;
use App\Http\Controllers\ViolationVehiclePhotoController;
use App\Http\Controllers\ViolatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

// Guest-only
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// All authenticated users
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard/search', [DashboardController::class, 'search'])->name('dashboard.search');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');

    // ── VIOLATORS ─────────────────────────────────────────────────────────────
    Route::get('/violators', [ViolatorController::class, 'index'])->name('violators.index');

    // Static paths MUST come before {violator} wildcard
    Route::middleware('role:operator')->group(function () {
        Route::get('/violators/create', [ViolatorController::class, 'create'])->name('violators.create');
        Route::get('/violators/create-from-incident/{motorist}', [ViolatorController::class, 'createFromIncident'])->name('violators.create-from-incident');
        Route::post('/violators', [ViolatorController::class, 'store'])->name('violators.store');
    });

    Route::get('/violators/{violator}', [ViolatorController::class, 'show'])->name('violators.show');
    Route::get('/violators/{violator}/print', [ViolatorController::class, 'printRecord'])->name('violators.print');

    Route::middleware('role:operator')->group(function () {
        Route::get('/violators/{violator}/edit', [ViolatorController::class, 'edit'])->name('violators.edit');
        Route::put('/violators/{violator}', [ViolatorController::class, 'update'])->name('violators.update');
        Route::delete('/violators/{violator}', [ViolatorController::class, 'destroy'])->name('violators.destroy');
    });

    // ── VEHICLES ──────────────────────────────────────────────────────────────
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

    Route::middleware('role:operator')->group(function () {
        Route::get('/violators/{violator}/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/violators/{violator}/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
        Route::delete('/vehicle-photos/{vehiclePhoto}', [VehiclePhotoController::class, 'destroy'])->name('vehicle-photos.destroy');
    });

    // ── VIOLATION TYPES ───────────────────────────────────────────────────────
    Route::get('/violation-types', [ViolationTypeController::class, 'index'])->name('violation-types.index');

    Route::middleware('role:operator')->group(function () {
        Route::get('/violation-types/create', [ViolationTypeController::class, 'create'])->name('violation-types.create');
        Route::post('/violation-types', [ViolationTypeController::class, 'store'])->name('violation-types.store');
        Route::get('/violation-types/{violationType}/edit', [ViolationTypeController::class, 'edit'])->name('violation-types.edit');
        Route::put('/violation-types/{violationType}', [ViolationTypeController::class, 'update'])->name('violation-types.update');
        Route::delete('/violation-types/{violationType}', [ViolationTypeController::class, 'destroy'])->name('violation-types.destroy');
    });

    // ── VIOLATIONS ────────────────────────────────────────────────────────────
    Route::get('/violations', [ViolationController::class, 'index'])->name('violations.index');
    Route::get('/violations/{violation}', [ViolationController::class, 'show'])->name('violations.show');
    Route::get('/violations/{violation}/print', [ViolationController::class, 'printRecord'])->name('violations.print');

    Route::middleware('role:operator')->group(function () {
        Route::get('/violators/{violator}/violations/create', [ViolationController::class, 'create'])->name('violations.create');
        Route::post('/violators/{violator}/violations', [ViolationController::class, 'store'])->name('violations.store');
        Route::get('/violations/{violation}/edit', [ViolationController::class, 'edit'])->name('violations.edit');
        Route::put('/violations/{violation}', [ViolationController::class, 'update'])->name('violations.update');
        Route::delete('/violations/{violation}', [ViolationController::class, 'destroy'])->name('violations.destroy');
        Route::patch('/violations/{violation}/settle', [ViolationController::class, 'settle'])->name('violations.settle');
        Route::delete('/violation-vehicle-photos/{violationVehiclePhoto}', [ViolationVehiclePhotoController::class, 'destroy'])->name('violation-vehicle-photos.destroy');
    });

    // ── INCIDENT CHARGE TYPES ─────────────────────────────────────────────────
    Route::get('/incident-charge-types', [IncidentChargeTypeController::class, 'index'])->name('incident-charge-types.index');

    Route::middleware('role:operator')->group(function () {
        Route::get('/incident-charge-types/create', [IncidentChargeTypeController::class, 'create'])->name('incident-charge-types.create');
        Route::post('/incident-charge-types', [IncidentChargeTypeController::class, 'store'])->name('incident-charge-types.store');
        Route::get('/incident-charge-types/{incidentChargeType}/edit', [IncidentChargeTypeController::class, 'edit'])->name('incident-charge-types.edit');
        Route::put('/incident-charge-types/{incidentChargeType}', [IncidentChargeTypeController::class, 'update'])->name('incident-charge-types.update');
        Route::delete('/incident-charge-types/{incidentChargeType}', [IncidentChargeTypeController::class, 'destroy'])->name('incident-charge-types.destroy');
    });

    // ── INCIDENTS ─────────────────────────────────────────────────────────────
    Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');

    // Static path before wildcard
    Route::middleware('role:operator')->group(function () {
        Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
        Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
    });

    Route::get('/incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
    Route::get('/incidents/{incident}/print', [IncidentController::class, 'printRecord'])->name('incidents.print');

    Route::middleware('role:operator')->group(function () {
        Route::get('/incidents/{incident}/edit', [IncidentController::class, 'edit'])->name('incidents.edit');
        Route::put('/incidents/{incident}', [IncidentController::class, 'update'])->name('incidents.update');
        Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');
        Route::delete('/incident-media/{media}', [IncidentController::class, 'destroyMedia'])->name('incident-media.destroy');
    });

    // ── REPORTS ───────────────────────────────────────────────────────────────
    Route::get('/reports/violator-suggestions', [ReportController::class, 'suggestions'])->name('reports.suggestions')->middleware('throttle:30,1');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // ── USERS (operator only) ─────────────────────────────────────────────────
    Route::middleware('role:operator')->group(function () {
        Route::resource('users', UserController::class);
    });

    // ── TRAFFIC OFFICER MOBILE ────────────────────────────────────────────────
    Route::middleware('role:traffic_officer')->prefix('officer')->name('officer.')->group(function () {
        Route::get('/dashboard', [OfficerController::class, 'dashboard'])->name('dashboard');
        Route::put('/password', [OfficerController::class, 'updatePassword'])->name('password.update');

        // Motorists
        Route::get('/motorists', [OfficerController::class, 'motorists'])->name('motorists.index');
        Route::get('/motorists/suggestions', [OfficerController::class, 'motoristSuggestions'])->name('motorists.suggestions');
        Route::get('/motorists/create', [OfficerController::class, 'createMotorist'])->name('motorists.create');
        Route::post('/motorists', [OfficerController::class, 'storeMotorist'])->name('motorists.store');
        Route::get('/motorists/{violator}', [OfficerController::class, 'showMotorist'])->name('motorists.show');
        Route::get('/motorists/{violator}/edit', [OfficerController::class, 'editMotorist'])->name('motorists.edit');
        Route::put('/motorists/{violator}', [OfficerController::class, 'updateMotorist'])->name('motorists.update');

        // Vehicles (from motorist context)
        Route::get('/motorists/{violator}/vehicles/create', [OfficerController::class, 'createVehicle'])->name('motorists.vehicles.create');
        Route::post('/motorists/{violator}/vehicles', [OfficerController::class, 'storeVehicle'])->name('motorists.vehicles.store');

        // Violations (from motorist context)
        Route::get('/motorists/{violator}/violations/create', [OfficerController::class, 'createViolation'])->name('violations.create');
        Route::post('/motorists/{violator}/violations', [OfficerController::class, 'storeViolation'])->name('violations.store');
        Route::get('/violations/{violation}', [OfficerController::class, 'showViolation'])->name('violations.show');
        Route::get('/violations/{violation}/edit', [OfficerController::class, 'editViolation'])->name('violations.edit');
        Route::put('/violations/{violation}', [OfficerController::class, 'updateViolation'])->name('violations.update');

        // Incidents
        Route::get('/incidents', [OfficerController::class, 'incidents'])->name('incidents.index');
        Route::get('/incidents/create', [OfficerController::class, 'createIncident'])->name('incidents.create');
        Route::post('/incidents', [OfficerController::class, 'storeIncident'])->name('incidents.store');
        Route::get('/incidents/{incident}', [OfficerController::class, 'showIncident'])->name('incidents.show');
    });
});
