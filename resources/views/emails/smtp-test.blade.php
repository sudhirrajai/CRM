<x-mail::message>
# SMTP Test Successful

Congratulations! Your SMTP settings have been configured correctly.

<x-mail::panel>
This is an automated test email sent from **{{ config('app.name') }}** to verify your mail delivery configuration.
</x-mail::panel>

You can now start using transactional emails across the system.

Thanks,<br>
**{{ config('app.name') }} System**
</x-mail::message>
