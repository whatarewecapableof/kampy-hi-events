@php /** @var \HiEvents\DomainObjects\UserDomainObject $user */ @endphp
@php /** @var string $link */ @endphp

<x-mail::message>
# {{ __('Reset your password') }}

{{ __('Hello') }},

{{ __('You have requested to reset your password for your account on :appName.', ['appName' => config('app.name')]) }}

<x-mail::button :url="$link">
{{ __('Reset Password') }}
</x-mail::button>

<p class="sub">{{ __('If the button doesn’t work, copy this link into your browser:') }}<br><a href="{{ $link }}" class="break-all">{{ $link }}</a></p>

<p class="sub">{{ __('If you did not request a password reset, please ignore this email or reply to let us know.') }}</p>
</x-mail::message>
