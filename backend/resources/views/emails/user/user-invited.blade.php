@php /** @var \HiEvents\DomainObjects\UserDomainObject $invitedUser */ @endphp
@php /** @var string $inviteLink */ @endphp
@php /** @var string $appName */ @endphp
@php /** @var \HiEvents\Services\Domain\Email\DTO\UniversityEmailThemeDTO|null $theme */ @endphp
@php
    // Staff invitation: no school context, so it uses the shared Kamp fallback theme
    // (frontend/src/styles/universityThemes.json, _kampFallback). If that manifest is
    // unusable, render in ink rather than fail.
    $theme ??= new \HiEvents\Services\Domain\Email\DTO\UniversityEmailThemeDTO('#222222', '#222222', '#ffffff', '#ffffff', '#f4f5f7');
    $inviteHost = parse_url($inviteLink, PHP_URL_HOST) ?: config('app.name');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ __('You\'ve been invited to join :appName', ['appName' => $appName]) }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;color:#222222;font-family:'DM Sans',Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;table-layout:fixed;border-collapse:collapse;background-color:#f4f5f7;">
    <tr><td align="center" style="padding:12px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;table-layout:fixed;border-collapse:collapse;background-color:#ffffff;border:1px solid #d8dbe2;border-radius:10px;">
            <tr><td height="10" style="height:10px;line-height:10px;font-size:0;background-color:{{ $theme->primary }};color:{{ $theme->onPrimary }};border-radius:10px 10px 0 0;">&nbsp;</td></tr>
            <tr><td style="padding:28px 20px 32px;word-break:break-word;overflow-wrap:anywhere;">
                <p style="margin:0 0 24px;color:{{ $theme->primary }};font-size:14px;line-height:20px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">{{ $appName }}</p>
                <h1 style="margin:0 0 18px;color:#222222;font-size:28px;line-height:34px;font-weight:700;">{{ __('You’re invited to :appName', ['appName' => $appName]) }}</h1>
                <p style="margin:0 0 16px;color:#222222;font-size:17px;line-height:27px;">{{ __('Hi :name', ['name' => $invitedUser->getFirstName()]) }},</p>
                <p style="margin:0 0 28px;color:#222222;font-size:17px;line-height:27px;">{{ __('You’ve been added to the :appName team on :host, where we run registration and tickets. Accept the invitation to set up your account.', ['appName' => $appName, 'host' => $inviteHost]) }}</p>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:separate;"><tr><td align="center" bgcolor="{{ $theme->secondary }}" style="border-radius:999px;background-color:{{ $theme->secondary }};"><a href="{{ $inviteLink }}" style="display:block;width:100%;box-sizing:border-box;padding:15px 12px;color:{{ $theme->onSecondary }};font-size:16px;line-height:20px;font-weight:700;text-align:center;text-decoration:none;border-radius:999px;">{{ __('Accept invitation') }}</a></td></tr></table>
                <p style="margin:20px 0 0;color:#555b66;font-size:14px;line-height:21px;">{{ __('This invitation expires in 7 days. If the button doesn’t work, copy this link into your browser:') }}</p>
                <p style="margin:4px 0 0;color:#555b66;font-size:14px;line-height:21px;word-break:break-all;overflow-wrap:anywhere;"><a href="{{ $inviteLink }}" style="display:block;max-width:100%;color:{{ $theme->secondary }};text-decoration:underline;word-break:break-all;overflow-wrap:anywhere;">{{ $inviteLink }}</a></p>
                <p style="margin:20px 0 0;color:#555b66;font-size:14px;line-height:21px;">{{ __('Weren’t expecting this? You can ignore this email and nothing will change.') }}</p>
            </td></tr>
        </table>
        @include('emails.partials.footer')
    </td></tr>
</table>
</body>
</html>
