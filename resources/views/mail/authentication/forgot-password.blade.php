<x-mail::message>
# Password Reset Code

Hello {{ $name }},

You recently requested to reset your password.

Here is your password reset code:

<x-mail::panel>
<strong>{{ $code }}</strong>
</x-mail::panel>

This code will expire in 60 minutes.

If you didn’t request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
