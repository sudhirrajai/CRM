<x-mail::message>
# New Reply Added

**{{ $user_name }}** has added a new reply to the ticket: **{{ $subject }}**.

<x-mail::panel>
{{ $content }}
</x-mail::panel>

<x-mail::button :url="$url">
View Conversation
</x-mail::button>

If you have any further questions, please don't hesitate to reply to this email or update the ticket.

Thanks,<br>
**{{ config('app.name') }} Support**
</x-mail::message>
