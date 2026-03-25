<x-mail::message>
# Welcome back, {{ $clientName }}

Your access to the client portal has been set up or updated. You can use the credentials below to log in and view your projects, invoices, and more.

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
