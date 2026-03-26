<x-mail::message>
# Service Terminated

Dear {{ $clientName }},

Your hosting services for **{{ $domain }}** have been terminated.

@if($reason)
**Reason:** {{ $reason }}
@endif

**All associated data has been deleted from our servers.**

If you have any questions, please contact our support team.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
