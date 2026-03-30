<x-mail::message>
# Ticket Assigned

A support ticket from **{{ $client_name }}** has been assigned to you.

**Subject:** {{ $subject }}

<x-mail::button :url="$url">
View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
