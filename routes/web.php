<?php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('halle-register', [RegistrationController::class, 'create'])->name('register');
Route::post('halle-register', [RegistrationController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('register.store');

Route::get('verify/{token}', [RegistrationController::class, 'verify'])->name('verify');

// Angepasste Dashboard-Weiterleitung basierend auf der Rolle
Route::get('dashboard', function () {
    if (auth()->check() && auth()->user()->is_admin) {
        return redirect()->route('admin.index');
    }
    return redirect()->route('staff');
})->middleware('auth')->name('dashboard');

// Alle geschützten Routen erfordern Login
Route::middleware('auth')->group(function () {

    // BEREICH FÜR ALLE (Staff + Admin)
    Route::get('hallendienst', [StaffController::class, 'index'])->name('staff');

    Route::post('hallendienst/{registration}/check-in',
        [StaffController::class, 'checkin'])->name('staff.checkin');

    Route::post('hallendienst/{registration}/parent-consent',
        [StaffController::class, 'confirmParentConsent'])->name('staff.parent-consent');

    Route::post('hallendienst/checkout-all',
        [StaffController::class, 'checkoutAll'])->name('staff.checkout-all');

    Route::post('hallendienst/import-members',
        [StaffController::class, 'importMembers'])->name('staff.importMembers');

    // QR-Code Check-in (Self-Service)
    Route::post('verify/{token}/checkin',
        [RegistrationController::class, 'checkin'])->name('verify.checkin');
});

// EXKLUSIVER ADMIN-BEREICH
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('import-members', [AdminController::class, 'importMembers'])->name('importMembers');
    Route::get('export-checkins', [AdminController::class, 'exportCheckins'])->name('exportCheckins');
    Route::delete('registrations/{registration}', [AdminController::class, 'destroyRegistration'])->name('registrations.destroy');
    Route::patch('registrations/{registration}/notes', [AdminController::class, 'updateRegistrationNotes'])->name('registrations.notes');
    Route::delete('inactive-members', [AdminController::class, 'deleteInactiveMembers'])->name('deleteInactiveMembers');
});

require __DIR__ . '/auth.php';
