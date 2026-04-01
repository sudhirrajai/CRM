<x-mail::message>
# New Ticket Created

A new support ticket has been created by **{{ $client_name }}**.

<x-mail::panel>
**Subject:** {{ $subject }}
</x-mail::panel>

<x-mail::button :url="$url">
View Ticket Details
</x-mail::button>

Our support team will review this ticket and get back to you shortly.

Thanks,<br>
**{{ config('app.name') }} Support**
</x-mail::message>
