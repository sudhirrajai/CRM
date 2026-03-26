<x-mail::message>
# Service Cancelled

Dear {{ $clientName }},

Your hosting services for **{{ $domain }}** have been cancelled.

@if($reason)
**Reason:** {{ $reason }}
@endif

If you have any questions, please contact our support team.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
