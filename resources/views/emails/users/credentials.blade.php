<x-mail::message>
# Welcome to {{ config('app.name') }}, {{ $userName }}

An account has been created for you. You can now access the CRM using the credentials below.

<x-mail::panel>
**Login URL:** [{{ $loginUrl }}]({{ $loginUrl }})<br>
**Email:** {{ $email }}<br>
**Password:** `{{ $password }}`
</x-mail::panel>

<x-mail::button :url="$loginUrl">
Log In Now
</x-mail::button>

We strongly recommend logging in and changing your password as soon as possible for security purposes.

Thanks,<br>
**{{ config('app.name') }} Team**
</x-mail::message>
