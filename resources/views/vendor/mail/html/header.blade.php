@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php
    $settingLogo = \App\Models\Setting::getValue('app_logo', '/assets/images/vmcore.png');
    $logoSrc = asset($settingLogo); // Default fallback

    // Resolve absolute path for base64 encoding
    if (str_starts_with($settingLogo, '/storage/')) {
        $logoPath = storage_path('app/public/' . substr($settingLogo, 9));
    } elseif (str_starts_with($settingLogo, '/assets/')) {
        $logoPath = public_path(substr($settingLogo, 1));
    } else {
        $logoPath = public_path($settingLogo);
    }
    
    if (file_exists($logoPath)) {
        $mime = mime_content_type($logoPath) ?: 'image/png';
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:' . $mime . ';base64,' . $logoData;
    }
@endphp
<img src="{{ $logoSrc }}" class="logo" alt="{{ \App\Models\Setting::getValue('brand_name', config('app.name')) }} Logo" style="height: {{ \App\Models\Setting::getValue('app_logo_height', '48') }}px; max-width: 200px; object-fit: contain;">
</a>
</td>
</tr>
