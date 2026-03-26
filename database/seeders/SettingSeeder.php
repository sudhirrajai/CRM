<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'smtp_host', 'value' => 'localhost', 'group' => 'smtp'],
            ['key' => 'smtp_port', 'value' => '1025', 'group' => 'smtp'],
            ['key' => 'smtp_username', 'value' => null, 'group' => 'smtp'],
            ['key' => 'smtp_password', 'value' => null, 'group' => 'smtp'],
            ['key' => 'smtp_encryption', 'value' => null, 'group' => 'smtp'],
            ['key' => 'smtp_from_address', 'value' => 'admin@vmcore.com', 'group' => 'smtp'],
            ['key' => 'smtp_from_name', 'value' => 'VMCore CRM', 'group' => 'smtp'],
            ['key' => 'invoice_due_reminder_days', 'value' => '3', 'group' => 'invoices'],
            ['key' => 'app_logo', 'value' => '/assets/images/vmcore.png', 'group' => 'general'],
            ['key' => 'menu_logo', 'value' => '/assets/images/vmcore.png', 'group' => 'general'],
            ['key' => 'pdf_logo', 'value' => '/assets/images/vmcore.png', 'group' => 'general'],
            ['key' => 'brand_name', 'value' => 'VMCore CRM', 'group' => 'general'],
            ['key' => 'contact_phone', 'value' => '+91 999 999 9999', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'support@vmcore.in', 'group' => 'general'],
            ['key' => 'contact_address', 'value' => '123 VMCore Plaza, Mumbai, MH 400001', 'group' => 'general'],
            ['key' => 'app_logo_height', 'value' => '80', 'group' => 'general'],
            ['key' => 'menu_logo_height', 'value' => '40', 'group' => 'general'],
            ['key' => 'pdf_logo_height', 'value' => '50', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
