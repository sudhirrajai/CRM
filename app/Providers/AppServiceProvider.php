<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register model observers for marketing automation triggers
        \App\Models\Lead::observe(\App\Observers\LeadObserver::class);
        \App\Models\Client::observe(\App\Observers\ClientObserver::class);
        \App\Models\Invoice::observe(\App\Observers\InvoiceObserver::class);
        \App\Models\Ticket::observe(\App\Observers\TicketObserver::class);
        \App\Models\Project::observe(\App\Observers\ProjectObserver::class);

        // Load dynamic settings if table exists
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::all()->pluck('value', 'key');
                
                if (isset($settings['smtp_host'])) {
                    config([
                        'mail.mailers.smtp.host' => $settings['smtp_host'] ?? config('mail.mailers.smtp.host'),
                        'mail.mailers.smtp.port' => $settings['smtp_port'] ?? config('mail.mailers.smtp.port'),
                        'mail.mailers.smtp.username' => $settings['smtp_username'] ?? config('mail.mailers.smtp.username'),
                        'mail.mailers.smtp.password' => $settings['smtp_password'] ?? config('mail.mailers.smtp.password'),
                        'mail.mailers.smtp.encryption' => $settings['smtp_encryption'] ?? config('mail.mailers.smtp.encryption'),
                        'mail.from.address' => $settings['smtp_from_address'] ?? config('mail.from.address'),
                        'mail.from.name' => $settings['smtp_from_name'] ?? config('mail.from.name'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet during migrations
        }
    }
}
