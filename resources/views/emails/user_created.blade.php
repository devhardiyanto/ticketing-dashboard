<x-mail::message>
# Welcome to {{ config('app.name') }}

Your account has been created successfully.

**Email:** {{ $email }}
**Password:** {{ $password }}

Please login and change your password immediately.

<x-mail::button :url="route('login')">
Login
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
