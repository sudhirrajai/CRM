<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Mail;
use App\Mail\SmtpTestMail;
use Exception;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        
        return Inertia::render('Settings/Index', [
            'settings' => $settings
        ]);
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|string',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string',
            'smtp_from_address' => 'nullable|email',
            'smtp_from_name' => 'nullable|string',
            'invoice_30_day_reminder' => 'nullable|boolean',
            'invoice_15_day_reminder' => 'nullable|boolean',
            'invoice_due_date_reminder' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            $group = str_starts_with($key, 'smtp_') ? 'smtp' : (str_starts_with($key, 'invoice_') ? 'invoices' : 'general');
            Setting::setValue($key, $value, $group);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Test SMTP credentials.
     */
    public function testSmtp(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|string',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'smtp_encryption' => 'nullable|string',
            'smtp_from_address' => 'required|email',
            'smtp_from_name' => 'required|string',
            'test_email' => 'required|email',
        ]);

        try {
            // Backup original config
            $originalConfig = config('mail.mailers.smtp');
            $originalFrom = config('mail.from');

            // Set temporary config for testing
            config([
                'mail.mailers.smtp.host' => $request->smtp_host,
                'mail.mailers.smtp.port' => $request->smtp_port,
                'mail.mailers.smtp.username' => $request->smtp_username,
                'mail.mailers.smtp.password' => $request->smtp_password,
                'mail.mailers.smtp.encryption' => $request->smtp_encryption ?: null,
                'mail.from.address' => $request->smtp_from_address,
                'mail.from.name' => $request->smtp_from_name,
            ]);

            // Attempt to send test mail
            Mail::to($request->test_email)->send(new SmtpTestMail());

            // Restore config
            config(['mail.mailers.smtp' => $originalConfig]);
            config(['mail.from' => $originalFrom]);

            return redirect()->back()->with('success', 'Test email sent successfully! Please check your inbox.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'SMTP Test Failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload the app logo.
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'required|string|in:app_logo,menu_logo,pdf_logo',
        ]);

        if ($request->hasFile('logo')) {
            $type = $request->input('type');
            $path = $request->file('logo')->store('logos', 'public');
            $url = Storage::url($path);
            
            Setting::setValue($type, $url, 'general');
            
            return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $type)) . ' updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload logo.');
    }
}
