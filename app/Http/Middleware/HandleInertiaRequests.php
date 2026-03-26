<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user() ? $request->user()->getRoleNames() : [],
                'permissions' => $request->user() ? $request->user()->getAllPermissions()->pluck('name') : [],
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'appName' => \App\Models\Setting::getValue('brand_name', config('app.name')),
            'appLogo' => \App\Models\Setting::getValue('app_logo', '/assets/images/vmcore.png'),
            'menuLogo' => \App\Models\Setting::getValue('menu_logo', '/assets/images/vmcore.png'),
            'pdfLogo' => \App\Models\Setting::getValue('pdf_logo', '/assets/images/vmcore.png'),
            'contactPhone' => \App\Models\Setting::getValue('contact_phone', '+91 999 999 9999'),
            'contactEmail' => \App\Models\Setting::getValue('contact_email', 'support@vmcore.in'),
            'contactAddress' => \App\Models\Setting::getValue('contact_address', '123 VMCore Plaza, Mumbai, MH 400001'),
            'appLogoHeight' => \App\Models\Setting::getValue('app_logo_height', '80'),
            'menuLogoHeight' => \App\Models\Setting::getValue('menu_logo_height', '40'),
            'pdfLogoHeight' => \App\Models\Setting::getValue('pdf_logo_height', '50'),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
