<x-mail::message>
# Verify Your Email Address

Hello {{ $name }},

Thank you for registering! Please confirm your email address by clicking the button below:

<x-mail::button :url="$url">
Verify Email
</x-mail::button>

If you didn’t create an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
