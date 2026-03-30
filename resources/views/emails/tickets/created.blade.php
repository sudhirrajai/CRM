<x-mail::message>
# New Ticket Created

A new support ticket has been created by **{{ $client_name }}**.

**Subject:** {{ $subject }}

<x-mail::button :url="$url">
View Ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
