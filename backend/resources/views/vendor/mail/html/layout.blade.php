<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
</head>
<body>

{{-- Same shape as the standalone university emails: a light gray page, one fluid
     white card no wider than 600px with a theme-colour bar, and the footer below. --}}
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="wrapper-cell" align="center">
            <table class="card" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td class="card-bar" height="10">&nbsp;</td>
                </tr>

                {{ $header ?? '' }}

                <!-- Email Body -->
                <tr>
                    <td class="content-cell">
                        {{ Illuminate\Mail\Markdown::parse($slot) }}

                        {{ $subcopy ?? '' }}
                    </td>
                </tr>
            </table>

            {{ $footer ?? '' }}
        </td>
    </tr>
</table>
</body>
</html>
