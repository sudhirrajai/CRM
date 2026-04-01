<x-mail::message>
# Service Cancellation Confirmation

Dear {{ $clientName }},

Your hosting services for **{{ $domain }}** have been cancelled.

@if($reason)
<x-mail::panel>
**Cancellation Reason:** {{ $reason }}
</x-mail::panel>
@endif

We are sorry to see you go. If you have any feedback or questions, please reach out to our support team.

Thanks,<br>
**{{ config('app.name') }} Team**
</x-mail::message>
