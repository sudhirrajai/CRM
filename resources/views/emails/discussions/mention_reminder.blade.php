<x-mail::message>
# Hello {{ $user->name }},

You were mentioned by **{{ $discussion->user->name }}** in the project **{{ $discussion->project->name }}**.

**Message:**
> {{ $discussion->message }}

You are receiving this email because you haven't seen this message in the last 30 minutes.

<x-mail::button :url="route('projects.show', $discussion->project_id)">
View Discussion
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
