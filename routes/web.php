<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StaffController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/halle-register', [RegistrationController::class, 'create'])->name('register');
Route::post('/halle-register', [RegistrationController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('register.store');
Route::get('/verify/{token}', [RegistrationController::class, 'verify'])->name('verify');

// Angepasste Dashboard-Weiterleitung basierend auf der Rolle
Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->is_admin) {
        return redirect()->route('admin.index');
    }
    
    return redirect()->route('staff');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';

// Alle geschützten Routen (erfordern Login)
Route::middleware(['auth'])->group(function () {
    
    // ==========================================
    // BEREICH FÜR ALLE (Staff & Admin)
    // ==========================================
    Route::get('/staff', [StaffController::class, 'index'])->name('staff');
    Route::post('/staff/check-in/{registration}', [StaffController::class, 'checkin'])->name('staff.checkin');
    Route::post('/staff/import-members', [StaffController::class, 'importMembers'])->name('staff.importMembers');
    Route::post('/staff/kulanz/{registration}', [StaffController::class, 'grantKulanz'])->name('staff.kulanz');
    Route::post('/staff/{registration}/kulanz-checkin', [StaffController::class, 'kulanzCheckin'])->name('staff.kulanz-checkin');
    Route::post('/staff/{registration}/parent-consent', [StaffController::class, 'confirmParentConsent'])->name('staff.parent-consent');
    Route::post('staff/checkout-all', [StaffController::class, 'checkoutAll'])->name('staff.checkout-all');
    Route::post('/verify/{token}/checkin', [RegistrationController::class, 'checkin'])->name('verify.checkin');
    

    // ==========================================
    // EXKLUSIVER ADMIN-BEREICH
    // ==========================================
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/import-members', [AdminController::class, 'importMembers'])->name('importMembers');
        Route::get('/export-checkins', [AdminController::class, 'exportCheckins'])->name('exportCheckins');
        Route::delete('/registrations/{registration}', [AdminController::class, 'destroyRegistration'])->name('registrations.destroy');
    });
});