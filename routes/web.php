<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
});

Route::get('/dashboard', function (\App\Services\DashboardService $dashboardService) {
    return Inertia::render('Dashboard', [
        'analytics' => $dashboardService->getAnalytics()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Management Routes (Admin/Staff only)
    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('clients', \App\Http\Controllers\ClientController::class)->except(['index', 'show']);
        Route::resource('projects', \App\Http\Controllers\ProjectController::class)->except(['index', 'show']);
        Route::resource('orders', \App\Http\Controllers\OrderController::class)->except(['index', 'show']);
        Route::resource('invoices', \App\Http\Controllers\InvoiceController::class)->except(['index', 'show']);
        Route::resource('servers', \App\Http\Controllers\ServerController::class);
        Route::resource('hostings', \App\Http\Controllers\ClientHostingController::class)->except(['index', 'show']);
        Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class);
        Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
        Route::resource('users', \App\Http\Controllers\UserController::class);
        
        Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
        Route::put('/roles/{role}', [\App\Http\Controllers\RoleController::class, 'update'])->name('roles.update');
        
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/logo', [\App\Http\Controllers\SettingController::class, 'uploadLogo'])->name('settings.logo');
        Route::post('/settings/test-smtp', [\App\Http\Controllers\SettingController::class, 'testSmtp'])->name('settings.test-smtp');
        Route::post('/invoices/{invoice}/send-suspension', [\App\Http\Controllers\InvoiceController::class, 'sendSuspensionNotification'])->name('invoices.send-suspension');
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
            Route::get('/balance-sheet', [\App\Http\Controllers\ReportController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/profit-loss', [\App\Http\Controllers\ReportController::class, 'profitLoss'])->name('profit-loss');
            Route::get('/projects', [\App\Http\Controllers\ReportController::class, 'projects'])->name('projects');
            Route::get('/clients', [\App\Http\Controllers\ReportController::class, 'clients'])->name('clients');
            Route::get('/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('export');
        });
    });

    // Shared Routes (Accessible by Clients too, but filtered in controller)
    Route::resource('clients', \App\Http\Controllers\ClientController::class)->only(['index', 'show']);
    Route::resource('projects', \App\Http\Controllers\ProjectController::class)->only(['index', 'show']);
    Route::resource('orders', \App\Http\Controllers\OrderController::class)->only(['index', 'show']);
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class)->only(['index', 'show']);
    Route::resource('hostings', \App\Http\Controllers\ClientHostingController::class)->only(['index', 'show']);
    
    // Invoices PDF
    Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/view-pdf', [\App\Http\Controllers\InvoiceController::class, 'viewPdf'])->name('invoices.view-pdf');
    
    // Project Discussions
    Route::get('/projects/{project}/discussions', [\App\Http\Controllers\ProjectDiscussionController::class, 'index'])->name('projects.discussions.index');
    Route::post('/projects/{project}/discussions', [\App\Http\Controllers\ProjectDiscussionController::class, 'store'])->name('projects.discussions.store');
    Route::put('/projects/{project}/discussions/{discussion}', [\App\Http\Controllers\ProjectDiscussionController::class, 'update'])->name('projects.discussions.update');
    Route::delete('/projects/{project}/discussions/{discussion}', [\App\Http\Controllers\ProjectDiscussionController::class, 'destroy'])->name('projects.discussions.destroy');
    Route::get('/projects/{project}/attachments/{attachment}/download', [\App\Http\Controllers\ProjectDiscussionController::class, 'downloadAttachment'])->name('projects.discussions.download');
    
    // Project Change Requests
    Route::post('/projects/{project}/change-requests', [\App\Http\Controllers\ChangeRequestController::class, 'store'])->name('projects.change-requests.store');
    Route::put('/change-requests/{changeRequest}', [\App\Http\Controllers\ChangeRequestController::class, 'update'])->name('change-requests.update');
    Route::delete('/change-requests/{changeRequest}', [\App\Http\Controllers\ChangeRequestController::class, 'destroy'])->name('change-requests.destroy');
});

require __DIR__.'/auth.php';
