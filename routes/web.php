<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceRegistrationController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\IncidentChargeTypeController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\LguController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentReportController;
use App\Http\Controllers\ProvinceDashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehiclePhotoController;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\ViolationTypeController;
use App\Http\Controllers\ViolationVehiclePhotoController;
use App\Http\Controllers\ViolatorController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\GuestPaymentController;
use App\Http\Controllers\PaymentClaimController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');
Route::get('/health', [HealthCheckController::class, 'check'])->name('health');
Route::get('/privacy-policy', [PrivacyController::class, 'policy'])->name('privacy.policy');
Route::get('/privacy/data-subject-request', [PrivacyController::class, 'showDsrForm'])->name('privacy.dsr');
Route::post('/privacy/data-subject-request', [PrivacyController::class, 'submitDsr'])->name('privacy.dsr.submit');
Route::get('/privacy/search-motorists', [PrivacyController::class, 'searchMotorists'])->name('privacy.dsr.search');

// Guest QR payment flow — no login, access controlled only by the violation's
// non-guessable public_payment_token (never ticket_number, which is sequential).
Route::prefix('pay/{token}')->name('guest-payment.')->group(function () {
    Route::get('/', [GuestPaymentController::class, 'show'])->name('show')->middleware('throttle:20,1');
    Route::post('/paymongo-checkout', [GuestPaymentController::class, 'createPayMongoCheckout'])->name('paymongo-checkout')->middleware('throttle:5,1');
    Route::get('/checkout-status', [GuestPaymentController::class, 'checkoutStatus'])->name('checkout-status')->middleware('throttle:20,1');
    Route::get('/check-session-status/{sessionRef}', [GuestPaymentController::class, 'checkSessionStatus'])->name('check-session-status')->middleware('throttle:30,1');
    Route::post('/claim', [GuestPaymentController::class, 'submitClaim'])->name('claim')->middleware('throttle:5,1');
    Route::get('/claim-status', [GuestPaymentController::class, 'claimStatus'])->name('claim-status')->middleware('throttle:20,1');
});

// Guest-only
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    // Mid-login 2FA challenge — the user has passed their password but isn't
    // authenticated yet, so this stays under the guest group, gated by a
    // pending-login session marker set by LoginController.
    Route::prefix('two-factor-challenge')->name('two-factor.')->group(function () {
        Route::get('/', [TwoFactorChallengeController::class, 'show'])->name('challenge');
        Route::post('/', [TwoFactorChallengeController::class, 'verify'])->name('challenge.verify')->middleware('throttle:5,1');
    });
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// All authenticated users
Route::middleware('auth')->group(function () {

    Route::middleware('role:admin,operator,traffic_officer,cashier,province_admin,treasurer,traffic_supervisor,records_officer,auditor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // ── PROVINCE DASHBOARD (B3) ───────────────────────────────────────────────
    Route::middleware('role:province_admin')->prefix('province')->name('province.')->group(function () {
        Route::get('/dashboard', [ProvinceDashboardController::class, 'index'])->name('dashboard');
    });

    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard/search', [DashboardController::class, 'search'])->name('dashboard.search');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');

    // ── SYSTEM NOTIFICATIONS ──────────────────────────────────────────────────
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // ── SECURITY: TWO-FACTOR AUTHENTICATION (any authenticated user) ───────────
    Route::prefix('security/two-factor')->name('security.two-factor.')->group(function () {
        Route::get('/', [TwoFactorController::class, 'show'])->name('show');
        Route::get('/enable', [TwoFactorController::class, 'enable'])->name('enable');
        Route::post('/confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
        Route::delete('/', [TwoFactorController::class, 'disable'])->name('disable');
        Route::post('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes');
    });

    // ── VIOLATORS ─────────────────────────────────────────────────────────────
    Route::get('/violators', [ViolatorController::class, 'index'])->name('violators.index');

    // Static paths MUST come before {violator} wildcard
    Route::middleware('role:operator,records_officer,traffic_supervisor')->group(function () {
        Route::get('/violators/create', [ViolatorController::class, 'create'])->name('violators.create');
        Route::get('/violators/create-from-incident/{motorist}', [ViolatorController::class, 'createFromIncident'])->name('violators.create-from-incident');
        Route::post('/violators', [ViolatorController::class, 'store'])->name('violators.store');
    });

    Route::get('/violators/{violator}', [ViolatorController::class, 'show'])->name('violators.show');
    Route::get('/violators/{violator}/print', [ViolatorController::class, 'printRecord'])->name('violators.print');

    Route::middleware('role:operator,records_officer,traffic_supervisor')->group(function () {
        Route::get('/violators/{violator}/edit', [ViolatorController::class, 'edit'])->name('violators.edit');
        Route::put('/violators/{violator}', [ViolatorController::class, 'update'])->name('violators.update');
    });

    Route::middleware('role:operator')->group(function () {
        Route::delete('/violators/{violator}', [ViolatorController::class, 'destroy'])->name('violators.destroy');
    });

    // ── VEHICLES ──────────────────────────────────────────────────────────────
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

    Route::middleware('role:operator,records_officer,traffic_supervisor')->group(function () {
        Route::get('/violators/{violator}/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/violators/{violator}/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    });

    Route::middleware('role:operator')->group(function () {
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
    Route::get('/violations/{violation}/print-thermal', [ViolationController::class, 'printThermal'])->name('violations.print-thermal');
    Route::post('/violations/{violation}/send-sms', [ViolationController::class, 'sendSms'])->name('violations.send-sms');

    // ── SMS GATEWAY ───────────────────────────────────────────────────────────
    Route::get('/sms-gateway', [\App\Http\Controllers\SmsController::class, 'index'])->name('sms.index');
    Route::post('/sms-gateway/settings', [\App\Http\Controllers\SmsController::class, 'updateSettings'])->name('sms.settings');

    Route::middleware('role:operator,records_officer,traffic_supervisor')->group(function () {
        Route::get('/violators/{violator}/violations/create', [ViolationController::class, 'create'])->name('violations.create');
        Route::post('/violators/{violator}/violations', [ViolationController::class, 'store'])->name('violations.store');
        Route::get('/violations/{violation}/edit', [ViolationController::class, 'edit'])->name('violations.edit');
        Route::put('/violations/{violation}', [ViolationController::class, 'update'])->name('violations.update');
    });

    Route::middleware('role:operator')->group(function () {
        Route::delete('/violations/{violation}', [ViolationController::class, 'destroy'])->name('violations.destroy');
        Route::delete('/violation-vehicle-photos/{violationVehiclePhoto}', [ViolationVehiclePhotoController::class, 'destroy'])->name('violation-vehicle-photos.destroy');
    });

    Route::middleware('role:cashier,treasurer')->group(function () {
        Route::get('/cashier', [ViolationController::class, 'cashier'])->name('violations.cashier');
        Route::patch('/violations/{violation}/settle', [ViolationController::class, 'settle'])->name('violations.settle')->middleware('throttle:10,1');
        Route::get('/violations/{violation}/payments/{payment}/receipt', [ViolationController::class, 'printReceipt'])->name('payments.receipt');

        Route::get('/online-payment-claims', [PaymentClaimController::class, 'index'])->name('payment-claims.index');
        Route::post('/online-payment-claims/{paymentClaim}/verify', [PaymentClaimController::class, 'verify'])->name('payment-claims.verify')->middleware('throttle:10,1');
        Route::post('/online-payment-claims/{paymentClaim}/reject', [PaymentClaimController::class, 'reject'])->name('payment-claims.reject')->middleware('throttle:10,1');
    });

    // ── PAYMENT MANAGEMENT (Cashier / Treasurer / Admin) ────────────────────
    Route::middleware('role:admin,treasurer,cashier')->group(function () {
        Route::patch('/payments/{payment}/void', [PaymentController::class, 'void'])->name('payments.void');
        Route::patch('/payments/{payment}/correct', [PaymentController::class, 'correct'])->name('payments.correct');
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
    Route::middleware('role:operator,records_officer,traffic_supervisor')->group(function () {
        Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
        Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
        Route::get('/incidents/{incident}/edit', [IncidentController::class, 'edit'])->name('incidents.edit');
        Route::put('/incidents/{incident}', [IncidentController::class, 'update'])->name('incidents.update');
    });

    Route::get('/incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
    Route::get('/incidents/{incident}/print', [IncidentController::class, 'printRecord'])->name('incidents.print');

    Route::middleware('role:operator')->group(function () {
        Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');
        Route::delete('/incident-media/{media}', [IncidentController::class, 'destroyMedia'])->name('incident-media.destroy');
    });

    // ── REPORTS ───────────────────────────────────────────────────────────────
    Route::get('/reports/violator-suggestions', [ReportController::class, 'suggestions'])->name('reports.suggestions')->middleware('throttle:30,1');
    Route::get('/reports/incident-stats', [ReportController::class, 'incidentStats'])->name('reports.incidentStats');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.exportExcel');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // ── AUDIT LOGS ────────────────────────────────────────────────────────────
    Route::middleware('role:admin,province_admin,operator,auditor,traffic_supervisor')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // ── USERS (admin and LGU admin) ─────────────────────────────────────────
    Route::middleware('role:admin,operator')->group(function () {
        Route::resource('users', UserController::class);
        Route::put('/users/{user}/devices/{device}', [DeviceRegistrationController::class, 'update'])->name('users.devices.update');
        Route::delete('/users/{user}/devices/{device}', [DeviceRegistrationController::class, 'destroy'])->name('users.devices.destroy');
    });

    // ── LGUs (admin, province admin, and LGU admin) ─────────────────────────
    Route::middleware('role:admin,province_admin,operator')->group(function () {
        Route::resource('lgus', LguController::class)->except(['show']);
    });

    // ── PAYMENT MONITORING: Collection Reports & Reconciliation ─────────────
    // Treasurers are scoped to their own LGU inside PaymentReportController.
    Route::middleware('role:admin,operator,province_admin,treasurer,cashier,auditor')->group(function () {
        Route::get('/payments/report', [PaymentReportController::class, 'index'])->name('payments.report');
        Route::get('/payments/report/export', [PaymentReportController::class, 'exportExcel'])->name('payments.report.export');
    });

    // ── TRAFFIC OFFICER MOBILE ────────────────────────────────────────────────
    Route::middleware('role:traffic_officer')->prefix('officer')->name('officer.')->group(function () {
        Route::get('/dashboard', [OfficerController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [OfficerController::class, 'profile'])->name('profile');
        Route::get('/sync', [OfficerController::class, 'sync'])->name('sync');
        Route::put('/password', [OfficerController::class, 'updatePassword'])->name('password.update');
        Route::get('/offline/violations/create', [OfficerController::class, 'createOfflineViolation'])->name('offline.violations.create');
        Route::get('/offline/vehicles/create', [OfficerController::class, 'createOfflineVehicle'])->name('offline.vehicles.create');
        Route::post('/offline/vehicles/create', [OfficerController::class, 'storeOfflineVehicle'])->name('offline.vehicles.store');
        Route::get('/offline/incidents/create', [OfficerController::class, 'createOfflineIncident'])->name('offline.incidents.create');

        // Motorists
        Route::get('/motorists', [OfficerController::class, 'motorists'])->name('motorists.index');
        Route::get('/motorists/suggestions', [OfficerController::class, 'motoristSuggestions'])->name('motorists.suggestions');
        Route::get('/motorists/create', [OfficerController::class, 'createMotorist'])->name('motorists.create');
        Route::post('/motorists', [OfficerController::class, 'storeMotorist'])->name('motorists.store');
        Route::get('/motorists/{violator}', [OfficerController::class, 'showMotorist'])->name('motorists.show')->whereNumber('violator');
        Route::get('/motorists/{violator}/edit', [OfficerController::class, 'editMotorist'])->name('motorists.edit')->whereNumber('violator');
        Route::put('/motorists/{violator}', [OfficerController::class, 'updateMotorist'])->name('motorists.update')->whereNumber('violator');

        // Vehicles
        Route::get('/vehicles', fn () => redirect()->route('officer.motorists.index'))->name('vehicles.index');
        Route::get('/vehicles/create', fn () => redirect()->route('officer.offline.vehicles.create'))->name('vehicles.create');
        Route::get('/motorists/{violator}/vehicles/create', [OfficerController::class, 'createVehicle'])->name('motorists.vehicles.create')->whereNumber('violator');
        Route::post('/motorists/{violator}/vehicles', [OfficerController::class, 'storeVehicle'])->name('motorists.vehicles.store')->whereNumber('violator');
        Route::get('/vehicles/{vehicle}', [OfficerController::class, 'showVehicle'])->name('vehicles.show')->whereNumber('vehicle');

        // Violations (from motorist context)
        Route::get('/violations', [OfficerController::class, 'violations'])->name('violations.index');
        Route::get('/motorists/{violator}/violations/create', [OfficerController::class, 'createViolation'])->name('violations.create')->whereNumber('violator');
        Route::post('/motorists/{violator}/violations', [OfficerController::class, 'storeViolation'])->name('violations.store')->whereNumber('violator');
        Route::get('/violations/{violation}', [OfficerController::class, 'showViolation'])->name('violations.show')->whereNumber('violation');
        Route::get('/violations/{violation}/edit', [OfficerController::class, 'editViolation'])->name('violations.edit')->whereNumber('violation');
        Route::put('/violations/{violation}', [OfficerController::class, 'updateViolation'])->name('violations.update')->whereNumber('violation');

        // Incidents
        Route::get('/incidents', [OfficerController::class, 'incidents'])->name('incidents.index');
        Route::get('/incidents/create', [OfficerController::class, 'createIncident'])->name('incidents.create');
        Route::post('/incidents', [OfficerController::class, 'storeIncident'])->name('incidents.store');
        Route::get('/incidents/{incident}', [OfficerController::class, 'showIncident'])->name('incidents.show')->whereNumber('incident');
        Route::get('/incidents/{incident}/edit', [OfficerController::class, 'editIncident'])->name('incidents.edit')->whereNumber('incident');
        Route::put('/incidents/{incident}', [OfficerController::class, 'updateIncident'])->name('incidents.update')->whereNumber('incident');
    });

    // ── AI CHAT SUPPORT ───────────────────────────────────────────────────────
    Route::get('/chat-support/faqs', [\App\Http\Controllers\SupportChatController::class, 'getFaqs'])->name('chat-support.faqs');
    Route::post('/chat-support/query', [\App\Http\Controllers\SupportChatController::class, 'query'])->name('chat-support.query')->middleware('throttle:20,1');
});
