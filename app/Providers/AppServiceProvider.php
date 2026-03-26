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

        // Load dynamic settings if table exists
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::all()->pluck('value', 'key');
                
                if (isset($settings['smtp_host'])) {
                    config([
                        'mail.mailers.smtp.host' => $settings['smtp_host'],
                        'mail.mailers.smtp.port' => $settings['smtp_port'],
                        'mail.mailers.smtp.username' => $settings['smtp_username'],
                        'mail.mailers.smtp.password' => $settings['smtp_password'],
                        'mail.mailers.smtp.encryption' => $settings['smtp_encryption'],
                        'mail.from.address' => $settings['smtp_from_address'],
                        'mail.from.name' => $settings['smtp_from_name'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet during migrations
        }
    }
}
