<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\StaffPortalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BillingController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'customLogin'])->name('login.custom');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/admin/dashboard', function () {
    return "Admin Dashboard";
})->name('admin.dashboard')->middleware('auth');

// Use StaffPortalController for the dashboard (staff portal)
Route::get('/dashboard', [StaffPortalController::class, 'index'])->name('dashboard')->middleware(['auth', 'verified']);

// Optional: Redirect /staff-portal to /dashboard to maintain existing links
Route::get('/staff-portal', function () {
    return redirect()->route('dashboard');
})->name('staff-portal')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes for sidebar links with folder structure
Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
Route::get('/register/new', [RegisterController::class, 'new'])->name('register.new');
Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
Route::get('/records/billing', [RecordController::class, 'billing'])->name('records.billing');
Route::get('/records/payments', [RecordController::class, 'payments'])->name('records.payments');
Route::get('/records/reports', [RecordController::class, 'reports'])->name('records.reports');
Route::post('/reports', [ReportController::class, 'store'])->middleware('auth')->name('reports.store');
Route::get('/records/history', [RecordController::class, 'history'])->name('records.history');

// Billing compute API
Route::post('/api/billing/compute', [BillingController::class, 'compute'])->middleware('auth')->name('api.billing.compute');
Route::post('/api/billing/store', [BillingController::class, 'store'])->middleware('auth')->name('api.billing.store');

// Register existing customer attach endpoint
Route::post('/api/customer/attach', [CustomerController::class, 'attach'])->middleware('auth')->name('customer.attach');
Route::get('/api/customer/next-account', [CustomerController::class, 'nextAccount'])->name('customer.nextAccount');
Route::get('/api/customer/find', [CustomerController::class, 'findByAccount'])->middleware('auth')->name('customer.findByAccount');
Route::post('/api/customer', [CustomerController::class, 'store'])->name('customer.store');



// Comment out if custom login works without conflicts
// require __DIR__.'/auth.php';