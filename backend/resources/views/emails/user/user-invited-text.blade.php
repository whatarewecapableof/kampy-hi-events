@php $inviteHost = parse_url($inviteLink, PHP_URL_HOST) ?: config('app.name'); @endphp
{!! $appName !!}

{!! __('You’re invited to :appName', ['appName' => $appName]) !!}

{!! __('Hi :name', ['name' => $invitedUser->getFirstName()]) !!},

{!! __('You’ve been added to the :appName team on :host, where we run registration and tickets. Accept the invitation to set up your account.', ['appName' => $appName, 'host' => $inviteHost]) !!}

{!! __('Accept invitation') !!}:
{!! $inviteLink !!}

{!! __('This invitation expires in 7 days.') !!}

{!! __('Weren’t expecting this? You can ignore this email and nothing will change.') !!}

@include('emails.partials.footer-text')
