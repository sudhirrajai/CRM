@props(['url'])
<tr>
<td class="header" style="background-color: #0f172a; padding: 28px 0 22px 0; border-radius: 12px 12px 0 0;">
<a href="{{ $url }}" style="display: inline-block;">
@php
    // Use absolute URL for broad email-client compatibility (Gmail, Outlook).
    $logoSrc = url('assets/images/vmcore-light.png');
@endphp
<img src="{{ $logoSrc }}" class="logo" alt="{{ config('app.name') }} Logo" style="height: 48px; max-width: 220px; display: block;">
</a>
</td>
</tr>
