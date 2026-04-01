<x-mail::message>
# Ticket Assigned

A support ticket from **{{ $client_name }}** has been assigned to you for care.

<x-mail::panel>
**Subject:** {{ $subject }}
</x-mail::panel>

<x-mail::button :url="$url">
Manage Ticket
</x-mail::button>

Please review the ticket and provide an update at your earliest convenience.

Thanks,<br>
**{{ config('app.name') }} System**
</x-mail::message>
