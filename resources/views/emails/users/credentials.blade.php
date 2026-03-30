<x-mail::message>
# Welcome, {{ $userName }}

An account has been created or updated for you. You can use the credentials below to log in to the CRM.

**Login URL:** <a href="{{ $loginUrl }}">{{ $loginUrl }}</a><br>
**Email:** {{ $email }}<br>
**Password:** {{ $password }}

<x-mail::button :url="$loginUrl">
Log In Now
</x-mail::button>

We strongly recommend logging in and changing your password as soon as possible.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
