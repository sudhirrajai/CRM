<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProjectFileController;

Route::get('/', function () {
    return Inertia::render('Landing');
});

Route::get('/terms-and-conditions', function () {
    return Inertia::render('TermsAndConditions');
})->name('terms');

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

        // Leads
        Route::resource('leads', \App\Http\Controllers\LeadController::class);
        Route::put('/leads/{lead}/update-stage', [\App\Http\Controllers\LeadController::class, 'updateStage'])->name('leads.update-stage');
        Route::post('/leads/{lead}/convert', [\App\Http\Controllers\LeadController::class, 'convert'])->name('leads.convert');
        Route::post('/leads/{lead}/activities', [\App\Http\Controllers\LeadController::class, 'addActivity'])->name('leads.activities.store');
        Route::get('/leads-export', [\App\Http\Controllers\LeadController::class, 'export'])->name('leads.export');
        Route::post('/leads-import', [\App\Http\Controllers\LeadController::class, 'import'])->name('leads.import');

        // Pipeline Stage Management
        Route::get('/pipeline-stages', [\App\Http\Controllers\LeadPipelineStageController::class, 'index'])->name('pipeline-stages.index');
        Route::post('/pipeline-stages', [\App\Http\Controllers\LeadPipelineStageController::class, 'store'])->name('pipeline-stages.store');
        Route::put('/pipeline-stages/{stage}', [\App\Http\Controllers\LeadPipelineStageController::class, 'update'])->name('pipeline-stages.update');
        Route::delete('/pipeline-stages/{stage}', [\App\Http\Controllers\LeadPipelineStageController::class, 'destroy'])->name('pipeline-stages.destroy');
        Route::post('/pipeline-stages/reorder', [\App\Http\Controllers\LeadPipelineStageController::class, 'reorder'])->name('pipeline-stages.reorder');

        // Lead Getter
        Route::prefix('lead-getter')->name('lead-getter.')->group(function () {
            Route::get('/', [\App\Http\Controllers\LeadGetterController::class, 'index'])->name('index');
            Route::post('/groups', [\App\Http\Controllers\LeadGetterController::class, 'storeGroup'])->name('groups.store');
            Route::put('/groups/{group}', [\App\Http\Controllers\LeadGetterController::class, 'updateGroup'])->name('groups.update');
            Route::delete('/groups/{group}', [\App\Http\Controllers\LeadGetterController::class, 'destroyGroup'])->name('groups.destroy');
            Route::get('/groups/{group}', [\App\Http\Controllers\LeadGetterController::class, 'showGroup'])->name('groups.show');
            Route::post('/groups/{group}/tasks', [\App\Http\Controllers\LeadGetterController::class, 'storeTask'])->name('tasks.store');
            Route::get('/tasks/{task}', [\App\Http\Controllers\LeadGetterController::class, 'showTask'])->name('tasks.show');
            Route::delete('/tasks/{task}', [\App\Http\Controllers\LeadGetterController::class, 'destroyTask'])->name('tasks.destroy');
            Route::post('/results/{result}/qualify', [\App\Http\Controllers\LeadGetterController::class, 'qualifyResult'])->name('results.qualify');
            Route::post('/results/bulk-qualify', [\App\Http\Controllers\LeadGetterController::class, 'bulkQualify'])->name('results.bulk-qualify');
            Route::post('/results/{result}/disqualify', [\App\Http\Controllers\LeadGetterController::class, 'disqualifyResult'])->name('results.disqualify');
            Route::get('/tasks/{task}/export', [\App\Http\Controllers\LeadGetterController::class, 'exportResults'])->name('tasks.export');
        });

        // Secrets Vault
        Route::prefix('vault')->name('vault.')->middleware('can:secrets.view')->group(function () {
            Route::get('/', [\App\Http\Controllers\SecretVaultController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\SecretVaultController::class, 'store'])->name('store')->middleware('can:secrets.create');
            Route::put('/{secret}', [\App\Http\Controllers\SecretVaultController::class, 'update'])->name('update')->middleware('can:secrets.edit');
            Route::delete('/{secret}', [\App\Http\Controllers\SecretVaultController::class, 'destroy'])->name('destroy')->middleware('can:secrets.delete');
            Route::get('/{secret}/decrypt', [\App\Http\Controllers\SecretVaultController::class, 'decrypt'])->name('decrypt');
            Route::post('/{secret}/favorite', [\App\Http\Controllers\SecretVaultController::class, 'toggleFavorite'])->name('favorite');
            Route::post('/categories', [\App\Http\Controllers\SecretVaultController::class, 'storeCategory'])->name('categories.store')->middleware('can:secrets.create');
            Route::put('/categories/{category}', [\App\Http\Controllers\SecretVaultController::class, 'updateCategory'])->name('categories.update')->middleware('can:secrets.edit');
            Route::delete('/categories/{category}', [\App\Http\Controllers\SecretVaultController::class, 'destroyCategory'])->name('categories.destroy')->middleware('can:secrets.delete');
        });
        
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

        // Marketing
        Route::prefix('marketing')->name('marketing.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Marketing\DashboardController::class, 'index'])->name('dashboard');
            Route::resource('templates', \App\Http\Controllers\Marketing\EmailTemplateController::class);
            Route::post('templates/{template}/duplicate', [\App\Http\Controllers\Marketing\EmailTemplateController::class, 'duplicate'])->name('templates.duplicate');
            Route::get('templates/{template}/preview', [\App\Http\Controllers\Marketing\EmailTemplateController::class, 'preview'])->name('templates.preview');
            Route::resource('campaigns', \App\Http\Controllers\Marketing\CampaignController::class);
            Route::post('campaigns/{campaign}/send', [\App\Http\Controllers\Marketing\CampaignController::class, 'sendNow'])->name('campaigns.send');
            Route::post('campaigns/{campaign}/schedule', [\App\Http\Controllers\Marketing\CampaignController::class, 'schedule'])->name('campaigns.schedule');
            Route::post('campaigns/{campaign}/pause', [\App\Http\Controllers\Marketing\CampaignController::class, 'pause'])->name('campaigns.pause');
            Route::get('campaigns/{campaign}/analytics', [\App\Http\Controllers\Marketing\CampaignController::class, 'analytics'])->name('campaigns.analytics');
            Route::resource('lists', \App\Http\Controllers\Marketing\MailingListController::class);
            Route::post('lists/{list}/sync', [\App\Http\Controllers\Marketing\MailingListController::class, 'syncSubscribers'])->name('lists.sync');
            Route::post('lists/{list}/import', [\App\Http\Controllers\Marketing\MailingListController::class, 'importSubscribers'])->name('lists.import');
            Route::delete('lists/{list}/subscribers/{subscriber}', [\App\Http\Controllers\Marketing\MailingListController::class, 'removeSubscriber'])->name('lists.subscribers.remove');
            Route::resource('automations', \App\Http\Controllers\Marketing\AutomationController::class);
            Route::post('automations/{automation}/activate', [\App\Http\Controllers\Marketing\AutomationController::class, 'activate'])->name('automations.activate');
            Route::post('automations/{automation}/deactivate', [\App\Http\Controllers\Marketing\AutomationController::class, 'deactivate'])->name('automations.deactivate');
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
    Route::get('/discussions', [\App\Http\Controllers\ProjectDiscussionController::class, 'allDiscussions'])->name('discussions.index');
    Route::get('/projects/{project}/discussions', [\App\Http\Controllers\ProjectDiscussionController::class, 'index'])->name('projects.discussions.index');
    Route::post('/projects/{project}/discussions', [\App\Http\Controllers\ProjectDiscussionController::class, 'store'])->name('projects.discussions.store');
    Route::put('/projects/{project}/discussions/{discussion}', [\App\Http\Controllers\ProjectDiscussionController::class, 'update'])->name('projects.discussions.update');
    Route::delete('/projects/{project}/discussions/{discussion}', [\App\Http\Controllers\ProjectDiscussionController::class, 'destroy'])->name('projects.discussions.destroy');
    Route::post('/projects/{project}/discussions/read', [\App\Http\Controllers\ProjectDiscussionController::class, 'markAsRead'])->name('projects.discussions.read');
    Route::get('/projects/{project}/discussions/members', [\App\Http\Controllers\ProjectDiscussionController::class, 'projectMembers'])->name('projects.discussions.members');
    Route::get('/projects/{project}/discussions/available-staff', [\App\Http\Controllers\ProjectDiscussionController::class, 'availableStaff'])->name('projects.discussions.available-staff');
    Route::post('/projects/{project}/discussions/members', [\App\Http\Controllers\ProjectDiscussionController::class, 'assignMember'])->name('projects.discussions.assign');
    Route::delete('/projects/{project}/discussions/members/{user}', [\App\Http\Controllers\ProjectDiscussionController::class, 'unassignMember'])->name('projects.discussions.unassign');
    Route::get('/projects/{project}/attachments/{attachment}/download', [\App\Http\Controllers\ProjectDiscussionController::class, 'downloadAttachment'])->name('projects.discussions.download');
    
    // Group Discussions
    Route::get('/discussion-groups/users', [\App\Http\Controllers\DiscussionGroupController::class, 'allUsers'])->name('discussion-groups.users');
    Route::post('/discussion-groups', [\App\Http\Controllers\DiscussionGroupController::class, 'store'])->name('discussion-groups.store');
    Route::put('/discussion-groups/{group}', [\App\Http\Controllers\DiscussionGroupController::class, 'update'])->name('discussion-groups.update');
    Route::delete('/discussion-groups/{group}', [\App\Http\Controllers\DiscussionGroupController::class, 'destroy'])->name('discussion-groups.destroy');
    Route::get('/groups/{group}/discussions', [\App\Http\Controllers\DiscussionGroupController::class, 'index'])->name('groups.discussions.index');
    Route::post('/groups/{group}/discussions', [\App\Http\Controllers\DiscussionGroupController::class, 'storeMessage'])->name('groups.discussions.store');
    Route::put('/groups/{group}/discussions/{discussion}', [\App\Http\Controllers\DiscussionGroupController::class, 'updateMessage'])->name('groups.discussions.update');
    Route::delete('/groups/{group}/discussions/{discussion}', [\App\Http\Controllers\DiscussionGroupController::class, 'destroyMessage'])->name('groups.discussions.destroy');
    Route::post('/groups/{group}/discussions/read', [\App\Http\Controllers\DiscussionGroupController::class, 'markAsRead'])->name('groups.discussions.read');
    Route::get('/groups/{group}/discussions/available-members', [\App\Http\Controllers\DiscussionGroupController::class, 'availableMembers'])->name('groups.discussions.available-members');
    Route::post('/groups/{group}/discussions/members', [\App\Http\Controllers\DiscussionGroupController::class, 'assignMember'])->name('groups.discussions.assign');
    Route::delete('/groups/{group}/discussions/members/{user}', [\App\Http\Controllers\DiscussionGroupController::class, 'unassignMember'])->name('groups.discussions.unassign');
    Route::get('/groups/{group}/attachments/{attachment}/download', [\App\Http\Controllers\DiscussionGroupController::class, 'downloadAttachment'])->name('groups.discussions.download');
    
    // Project Change Requests
    Route::post('/projects/{project}/change-requests', [\App\Http\Controllers\ChangeRequestController::class, 'store'])->name('projects.change-requests.store');
    Route::put('/change-requests/{changeRequest}', [\App\Http\Controllers\ChangeRequestController::class, 'update'])->name('change-requests.update');
    Route::delete('/change-requests/{changeRequest}', [\App\Http\Controllers\ChangeRequestController::class, 'destroy'])->name('change-requests.destroy');

    // Project Files
    Route::get('/projects/{project}/files', [ProjectFileController::class, 'index'])->name('projects.files.index');
    Route::post('/projects/{project}/files', [ProjectFileController::class, 'upload'])->name('projects.files.upload');
    Route::get('/files/{file}/download', [ProjectFileController::class, 'download'])->name('projects.files.download');
    Route::delete('/files/{file}', [ProjectFileController::class, 'destroy'])->name('projects.files.destroy');
    Route::post('/files/{file}/share', [ProjectFileController::class, 'createShareLink'])->name('projects.files.share');
    Route::delete('/files/{file}/share', [ProjectFileController::class, 'revokeShareLink'])->name('projects.files.revoke-share');

    // Support Tickets
    Route::resource('tickets', \App\Http\Controllers\TicketController::class);
    Route::post('/tickets/{ticket}/assign', [\App\Http\Controllers\TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/status', [\App\Http\Controllers\TicketController::class, 'updateStatus'])->name('tickets.update-status');
    Route::get('/tickets/{ticket}/attachments/{attachment}/download', [\App\Http\Controllers\TicketController::class, 'downloadAttachment'])->name('tickets.attachments.download');
});


// Public Shared Files
Route::get('/shared-files/{token}', [ProjectFileController::class, 'publicDownload'])->name('public.files.download');

// Public Marketing Tracking (no auth)
Route::prefix('mkt')->group(function () {
    Route::get('/o/{recipientId}', [\App\Http\Controllers\Marketing\TrackingController::class, 'trackOpen'])->name('marketing.track.open');
    Route::get('/c/{recipientId}/{linkHash}', [\App\Http\Controllers\Marketing\TrackingController::class, 'trackClick'])->name('marketing.track.click');
    Route::get('/u/{subscriberId}/{token}', [\App\Http\Controllers\Marketing\TrackingController::class, 'unsubscribe'])->name('marketing.unsubscribe');
});

require __DIR__.'/auth.php';

