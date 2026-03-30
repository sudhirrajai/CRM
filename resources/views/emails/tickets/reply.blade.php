<x-mail::message>
# New Reply Added

**{{ $user_name }}** has added a new reply to the ticket: **{{ $subject }}**.

---
{{ $content }}
---

<x-mail::button :url="$url">
View Conversation
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
