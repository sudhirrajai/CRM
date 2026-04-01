@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php
    $logoPath = public_path('assets/images/vmcore.png');
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;
    } else {
        $logoSrc = asset('assets/images/vmcore.png'); // Fallback
    }
@endphp
<img src="{{ $logoSrc }}" class="logo" alt="{{ config('app.name') }} Logo" style="height: 48px; max-width: 200px;">
</a>
</td>
</tr>
