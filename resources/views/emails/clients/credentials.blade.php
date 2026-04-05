<x-mail::message>
# Welcome back, {{ $clientName }}

Your access to the client portal has been set up or updated. You can use the credentials below to log in and manage your projects, invoices, and more.

<x-mail::panel>
**Portal URL:** [{{ $loginUrl }}]({{ $loginUrl }})<br>
**Email:** {{ $email }}<br>
**Password:** `{{ $password }}`
</x-mail::panel>

<x-mail::button :url="$loginUrl">
Log In to Client Portal
</x-mail::button>

We strongly recommend logging in and changing your password as soon as possible.

Thanks,<br>
**{{ config('app.name') }} Team**
</x-mail::message>
