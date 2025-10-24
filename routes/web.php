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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'customLogin'])->name('login.custom');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/admin', [AdminController::class, 'index'])->middleware('auth')->name('admin.dashboard');
Route::get('/admin/notices', [AdminController::class, 'notices'])->middleware('auth')->name('admin.notices');
Route::get('/admin/reports', [AdminController::class, 'reports'])->middleware('auth')->name('admin.reports');

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
// Customer Register module (internal tool) — keep distinct from auth register
Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
Route::get('/register/new', [RegisterController::class, 'new'])->name('register.new');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::get('/api/register/search', [RegisterController::class, 'search'])->name('register.search');
Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
Route::post('/api/customer/delete-multiple', [CustomerController::class, 'deleteMultiple'])->name('customer.deleteMultiple');
Route::get('/billing', [RecordController::class, 'billingManagement'])->middleware('auth')->name('billing.management');
Route::get('/records/billing', [RecordController::class, 'billing'])->middleware('auth')->name('records.billing');
Route::get('/records/billing/{id}/generate', [RecordController::class, 'generateBill'])->middleware('auth')->name('records.billing.generate');
Route::post('/records/billing/{id}/status', [RecordController::class, 'updateBillStatus'])->middleware('auth')->name('records.billing.status');
Route::get('/records/billing/{id}/print', [RecordController::class, 'printBill'])->middleware('auth')->name('records.billing.print');
Route::get('/records/payments', [RecordController::class, 'payments'])->middleware('auth')->name('records.payments');
Route::get('/records/reports', [RecordController::class, 'reports'])->middleware('auth')->name('records.reports');
Route::post('/reports', [ReportController::class, 'store'])->middleware('auth')->name('reports.store');
Route::get('/records/history', [RecordController::class, 'history'])->middleware('auth')->name('records.history');
Route::get('/api/records/history', [RecordController::class, 'historyApi'])->middleware('auth')->name('api.records.history');

// Notifications
Route::get('/api/notifications', [NotificationController::class, 'index'])->middleware('auth')->name('api.notifications.index');
Route::post('/api/notifications/read', [NotificationController::class, 'markRead'])->middleware('auth')->name('api.notifications.read');
Route::post('/api/notifications/broadcast', [NotificationController::class, 'broadcast'])->middleware('auth')->name('api.notifications.broadcast');
Route::get('/api/notifications/recent', [NotificationController::class, 'recent'])->middleware('auth')->name('api.notifications.recent');

// Billing compute API
Route::post('/api/billing/compute', [BillingController::class, 'compute'])->middleware('auth')->name('api.billing.compute');
Route::post('/api/billing/store', [BillingController::class, 'store'])->middleware('auth')->name('api.billing.store');
Route::get('/api/billing/payment-history', [BillingController::class, 'getPaymentHistory'])->middleware('auth')->name('api.billing.payment-history');

// Register existing customer attach endpoint
Route::post('/api/customer/attach', [CustomerController::class, 'attach'])->middleware('auth')->name('customer.attach');
Route::post('/api/customer/transfer', [CustomerController::class, 'transferOwnership'])->middleware('auth')->name('customer.transfer');
Route::post('/api/customer/reconnect', [CustomerController::class, 'reconnectService'])->middleware('auth')->name('customer.reconnect');
Route::get('/api/customer/next-account', [CustomerController::class, 'nextAccount'])->name('customer.nextAccount');
Route::get('/api/customer/find', [CustomerController::class, 'findByAccount'])->middleware('auth')->name('customer.findByAccount');
Route::get('/api/customer/search', [CustomerController::class, 'searchAccounts'])->middleware('auth')->name('customer.searchAccounts');
Route::post('/api/customer', [CustomerController::class, 'store'])->name('customer.store');

// Payment routes
Route::get('/payment', [PaymentController::class, 'index'])->middleware('auth')->name('payment.index');
Route::post('/api/payment/search-customer', [PaymentController::class, 'searchCustomer'])->middleware('auth')->name('api.payment.search-customer');
Route::post('/api/payment/process', [PaymentController::class, 'processPayment'])->middleware('auth')->name('api.payment.process');
Route::get('/payment/receipt/{paymentRecordId}', [PaymentController::class, 'getPaymentReceipt'])->middleware('auth')->name('payment.receipt');
Route::get('/payment/print/{paymentRecordId}', [PaymentController::class, 'printReceipt'])->middleware('auth')->name('payment.print');



// Enable built-in auth & email verification routes (required by profile page)
require __DIR__.'/auth.php';