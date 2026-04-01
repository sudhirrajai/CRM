<x-mail::message>
# Service Termination Notice

Dear {{ $clientName }},

Your hosting services for **{{ $domain }}** have been officially terminated.

@if($reason)
<x-mail::panel>
**Termination Reason:** {{ $reason }}
</x-mail::panel>
@endif

**IMPORTANT:** All associated data has been permanently deleted from our servers as per our retention policy.

If you have any questions or believe this was done in error, please contact our support team immediately.

Thanks,<br>
**{{ config('app.name') }} Infrastructure**
</x-mail::message>
