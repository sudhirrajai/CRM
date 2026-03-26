<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
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
        
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/logo', [\App\Http\Controllers\SettingController::class, 'uploadLogo'])->name('settings.logo');
        Route::post('/settings/test-smtp', [\App\Http\Controllers\SettingController::class, 'testSmtp'])->name('settings.test-smtp');
        Route::post('/invoices/{invoice}/send-suspension', [\App\Http\Controllers\InvoiceController::class, 'sendSuspensionNotification'])->name('invoices.send-suspension');
        Route::get('/reports/balance-sheet', [\App\Http\Controllers\ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
    });

    // Shared Routes (Accessible by Clients too, but filtered in controller)
    Route::resource('clients', \App\Http\Controllers\ClientController::class)->only(['index', 'show']);
    Route::resource('projects', \App\Http\Controllers\ProjectController::class)->only(['index', 'show']);
    Route::resource('orders', \App\Http\Controllers\OrderController::class)->only(['index', 'show']);
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class)->only(['index', 'show']);
    Route::resource('hostings', \App\Http\Controllers\ClientHostingController::class)->only(['index', 'show']);
    

});

require __DIR__.'/auth.php';
