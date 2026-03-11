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
use App\Http\Controllers\Admin\MeterController;
use App\Http\Controllers\Admin\MeterServiceTicketController;
use App\Http\Controllers\Staff\MeterTicketController as StaffMeterTicketController;
use App\Http\Controllers\Staff\CustomerIssueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StaffProgressController;
use App\Http\Controllers\BillEventController;
use App\Http\Controllers\ConnectionsController;
use App\Http\Controllers\ApplicationsController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'customLogin'])->name('login.custom')->middleware(['throttle:10,1', 'track.violations']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/dashboard/stats', [AdminController::class, 'dashboardStats'])->name('admin.dashboard.stats');
    Route::get('/admin/dashboard/insights', [AdminController::class, 'dashboardInsightsData'])->name('admin.dashboard.insights');
    Route::get('/admin/notices', [AdminController::class, 'notices'])->name('admin.notices');
    Route::get('/admin/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::post('/admin/reports/{report}/priority', [AdminController::class, 'updateReportPriority'])->name('admin.reports.priority');
    Route::post('/admin/reports/{report}/status', [AdminController::class, 'updateReportStatus'])->name('admin.reports.status');
    Route::get('/admin/reports/revenue', [AdminController::class, 'revenue'])->name('admin.reports.revenue');
    Route::get('/admin/customers', [AdminController::class, 'customers'])->name('admin.customers');
    Route::get('/admin/meters', [MeterController::class, 'index'])->name('admin.meters');
    Route::get('/admin/activity-log', [AdminController::class, 'activityLog'])->name('admin.activity-log');
    Route::post('/admin/billing/{id}/archive', [RecordController::class, 'archive'])->name('admin.billing.archive');
    Route::get('/admin/billing/archived', [AdminController::class, 'archivedBilling'])->name('admin.billing.archived');

    Route::post('/admin/meters', [MeterController::class, 'store'])->name('admin.meters.store');
    Route::patch('/admin/meters/{meter}', [MeterController::class, 'update'])->name('admin.meters.update');
    Route::delete('/admin/meters/{meter}', [MeterController::class, 'destroy'])->name('admin.meters.destroy');
    Route::post('/admin/meters/{meter}/assign', [MeterController::class, 'assign'])->name('admin.meters.assign');
    Route::post('/admin/meters/{meter}/unassign', [MeterController::class, 'unassign'])->name('admin.meters.unassign');
    Route::post('/admin/customers/{customer}/transfer-meter', [AdminController::class, 'transferMeterOwnership'])->name('admin.customers.transfer-meter');
    Route::get('/admin/meters/api', [MeterController::class, 'apiIndex'])->name('admin.meters.api');
    Route::get('/admin/meters/current', [MeterController::class, 'apiCurrentByAccount'])->name('admin.meters.current');
    Route::get('/admin/meters/export', [MeterController::class, 'export'])->name('admin.meters.export');
    Route::get('/admin/meter-service-tickets', [MeterServiceTicketController::class, 'index'])->name('admin.meter-service-tickets.index');
    Route::post('/admin/meter-service-tickets', [MeterServiceTicketController::class, 'store'])->name('admin.meter-service-tickets.store');
    Route::put('/admin/meter-service-tickets/{ticket}', [MeterServiceTicketController::class, 'update'])->name('admin.meter-service-tickets.update');

    Route::get('/staff/meter-tickets', [StaffMeterTicketController::class, 'index'])->name('staff.meter-tickets.index');
    Route::put('/staff/meter-tickets/{ticket}', [StaffMeterTicketController::class, 'update'])->name('staff.meter-tickets.update');

    Route::get('/staff/customer-issues', [CustomerIssueController::class, 'index'])->name('staff.customer-issues.index');
    Route::get('/api/staff/customer-issues/accounts', [CustomerIssueController::class, 'searchAccounts'])->name('api.staff.customer-issues.search');
    Route::get('/api/staff/customer-issues/snapshot', [CustomerIssueController::class, 'accountSnapshot'])->name('api.staff.customer-issues.snapshot');
    Route::post('/api/staff/customer-issues', [CustomerIssueController::class, 'store'])->name('api.staff.customer-issues.store');

    // Billing Management
    Route::get('/admin/billing', [AdminController::class, 'billing'])->name('admin.billing');
});

// All authenticated routes consolidated into a single group
Route::middleware('auth')->group(function () {
    // Staff portal / dashboard
    Route::get('/dashboard', [StaffPortalController::class, 'index'])->name('dashboard');
    Route::get('/staff/activity-log', [StaffPortalController::class, 'activityLog'])->name('staff.activity-log');

    // Redirect legacy URL
    Route::get('/staff-portal', function () {
        return redirect()->route('dashboard');
    })->name('staff-portal');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update')->middleware('throttle:5,1');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:10,1');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('throttle:5,1');

    // Staff Bill Generation
    Route::view('/bill-generation', 'billing-generation.index')->name('billing.generation');

    // Billing & Records
    Route::get('/billing', [RecordController::class, 'billingManagement'])->name('billing.management');
    Route::get('/records/billing', [RecordController::class, 'billing'])->name('records.billing');
    Route::get('/records/billing/archived', [RecordController::class, 'archivedBilling'])->name('records.billing.archived');
    Route::get('/records/billing/archived/export', [RecordController::class, 'exportArchivedBilling'])->name('records.billing.export-archived');
    Route::post('/records/billing/{id}/archive', [RecordController::class, 'archive'])->name('records.billing.archive');
    Route::post('/records/billing/{id}/restore', [RecordController::class, 'restore'])->name('records.billing.restore');
    Route::delete('/records/billing/{id}/force', [RecordController::class, 'forceDelete'])->name('records.billing.force');
    Route::get('/records/billing/{id}/generate', [RecordController::class, 'generateBill'])->name('records.billing.generate');
    Route::post('/records/billing/{id}/status', [RecordController::class, 'updateBillStatus'])->name('records.billing.status');
    Route::get('/records/billing/{id}/print', [RecordController::class, 'printBill'])->name('records.billing.print');
    Route::get('/records/billing/{id}/pdf', [RecordController::class, 'downloadBillPdf'])->name('records.billing.pdf');
    Route::post('/records/billing/bulk-generate', [RecordController::class, 'bulkGenerate'])->name('records.billing.bulk-generate');
    Route::post('/records/billing/bulk-archive', [RecordController::class, 'bulkArchive'])->name('records.billing.bulk-archive');
    Route::get('/records/billing/print-batch', [RecordController::class, 'printBatch'])->name('records.billing.print-batch');
    Route::get('/records/payments', [RecordController::class, 'payments'])->name('records.payments');
    Route::get('/records/reports', [RecordController::class, 'reports'])->name('records.reports');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/records/history', [RecordController::class, 'history'])->name('records.history');
    Route::get('/api/records/history', [RecordController::class, 'historyApi'])->name('api.records.history');
    Route::get('/api/records/billing-stats', [RecordController::class, 'billingStats'])->name('api.records.billing-stats');
    Route::get('/api/records/customers', [RecordController::class, 'billingCustomerSearch'])->name('api.records.customers');

    // Notifications
    Route::get('/api/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('/api/notifications/read', [NotificationController::class, 'markRead'])->name('api.notifications.read');
    Route::post('/api/notifications/broadcast', [NotificationController::class, 'broadcast'])->name('api.notifications.broadcast');
    Route::get('/api/notifications/recent', [NotificationController::class, 'recent'])->name('api.notifications.recent');

    // Billing compute API (throttled to prevent DoS during peak billing periods)
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/api/billing/compute', [BillingController::class, 'compute'])->name('api.billing.compute');
        Route::post('/api/billing/store', [BillingController::class, 'store'])->name('api.billing.store');
        Route::post('/api/billing/generate', [BillEventController::class, 'generate'])->name('api.billing.generate');
        Route::post('/api/billing/deliver', [BillEventController::class, 'deliver'])->name('api.billing.deliver');
    });
    Route::get('/api/billing/status', [BillingController::class, 'status'])->name('api.billing.status');
    Route::get('/api/billing/payment-history', [BillingController::class, 'getPaymentHistory'])->name('api.billing.payment-history');
    // Billing events (generate, deliver) — moved inside throttle group above

    // Staff progress API
    Route::get('/api/staff/progress/today', [StaffProgressController::class, 'today'])->name('api.staff.progress.today');
    Route::put('/api/staff/progress/today', [StaffProgressController::class, 'updateToday'])->name('api.staff.progress.update');
    Route::get('/api/staff/progress/breakdown', [StaffProgressController::class, 'breakdown'])->name('api.staff.progress.breakdown');
    Route::post('/api/staff/progress/reset', [StaffProgressController::class, 'resetToday'])->name('api.staff.progress.reset');

    // Customer API
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/api/customer/attach', [CustomerController::class, 'attach'])->name('customer.attach');
        Route::post('/api/customer/transfer', [CustomerController::class, 'transferOwnership'])->name('customer.transfer');
        Route::post('/api/customer/reconnect', [CustomerController::class, 'reconnectService'])->name('customer.reconnect');
        Route::post('/api/customer/{id}/request-reconnect', [CustomerController::class, 'requestReconnect'])->name('customer.requestReconnect');
        Route::post('/api/customer', [CustomerController::class, 'store'])->name('customer.store');
        Route::patch('/api/customer/{id}', [CustomerController::class, 'update'])->name('customer.update');
        Route::put('/api/customer/{id}/verify', [CustomerController::class, 'verify'])->name('customer.verify');
    });
    Route::get('/api/customer/find', [CustomerController::class, 'findByAccount'])->name('customer.findByAccount');
    Route::get('/api/customer/search', [CustomerController::class, 'searchAccounts'])->name('customer.searchAccounts');
    Route::get('/api/customer/{id}', [CustomerController::class, 'show'])->name('customer.show');
    Route::get('/api/customer/duplicates', [CustomerController::class, 'duplicates'])->name('customer.duplicates');

    // Applications scoring and decisions
    Route::get('/admin/applicants', [ApplicationsController::class, 'index'])->name('admin.applicants.index');
    Route::get('/admin/applicants/{id}', [ApplicationsController::class, 'show'])->name('admin.applicants.show');
    Route::get('/api/applications/latest', [ApplicationsController::class, 'latest'])->name('api.applications.latest');
    Route::post('/api/applications/{id}/score', [ApplicationsController::class, 'score'])->name('api.applications.score');
    Route::put('/api/applications/{id}/approve', [ApplicationsController::class, 'approve'])->name('api.applications.approve');
    Route::put('/api/applications/{id}/reject', [ApplicationsController::class, 'reject'])->name('api.applications.reject');
    Route::put('/api/applications/{id}/inspect', [ApplicationsController::class, 'inspect'])->name('api.applications.inspect');
    Route::put('/api/applications/{id}/schedule', [ApplicationsController::class, 'schedule'])->name('api.applications.schedule');
    Route::put('/api/applications/{id}/installed', [ApplicationsController::class, 'installed'])->name('api.applications.installed');

    // Connections workflow
    Route::get('/api/connections', [ConnectionsController::class, 'index'])->name('connections.index');
    Route::get('/api/connections/{id}', [ConnectionsController::class, 'show'])->name('connections.show');
    Route::post('/api/connections', [ConnectionsController::class, 'store'])->name('connections.store');
    Route::put('/api/connections/{id}/inspection', [ConnectionsController::class, 'inspection'])->name('connections.inspection');
    Route::put('/api/connections/{id}/approve', [ConnectionsController::class, 'approve'])->name('connections.approve');
    Route::put('/api/connections/{id}/assess', [ConnectionsController::class, 'assess'])->name('connections.assess');
    Route::put('/api/connections/{id}/pay', [ConnectionsController::class, 'pay'])->name('connections.pay');
    Route::put('/api/connections/{id}/schedule', [ConnectionsController::class, 'schedule'])->name('connections.schedule');
    Route::put('/api/connections/{id}/install', [ConnectionsController::class, 'install'])->name('connections.install');

    // Payment routes
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::get('/api/payment/quick-search', [PaymentController::class, 'quickSearch'])->name('api.payment.quick-search');
    Route::post('/api/payment/search-customer', [PaymentController::class, 'searchCustomer'])->name('api.payment.search-customer');
    Route::post('/api/payment/process', [PaymentController::class, 'processPayment'])->name('api.payment.process')->middleware('throttle:30,1');
    Route::get('/payment/receipt/{paymentRecordId}', [PaymentController::class, 'getPaymentReceipt'])->name('payment.receipt');
    Route::get('/payment/print/{paymentRecordId}', [PaymentController::class, 'printReceipt'])->name('payment.print');
});

// Customer Register module
// /register and /api/register/search expose customer data — auth required
Route::get('/register', [RegisterController::class, 'index'])->middleware('auth')->name('register.index');
Route::get('/api/register/search', [RegisterController::class, 'search'])->middleware('auth')->name('register.search');
// Public-facing application form for prospective customers
Route::get('/register/new', [RegisterController::class, 'new'])->name('register.new');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::get('/customer', [CustomerController::class, 'index'])->middleware('auth')->name('customer.index');
Route::post('/api/customer/delete-multiple', [CustomerController::class, 'deleteMultiple'])->middleware('auth')->name('customer.deleteMultiple');
Route::get('/api/customer/next-account', [CustomerController::class, 'nextAccount'])->middleware('auth')->name('customer.nextAccount');


// Enable built-in auth & email verification routes (required by profile page)
require __DIR__.'/auth.php';