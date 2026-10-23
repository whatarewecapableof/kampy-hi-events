{{--
    Kamp Love email footer for standalone (non-Markdown) emails. It sits below the
    card and carries the same lines as the shared Markdown layout footer
    (vendor/mail/html/message.blade.php): APP_EMAIL_FOOTER_TEXT when configured,
    otherwise the copyright line, and then the Hi.Events licence attribution.

    The attribution stays OUTSIDE the footer-text conditional, as in the shared
    layout, so configuring footer text can never remove it. Do not hide it, shrink
    it below 13px, or remove it without a licensing decision.
--}}
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;table-layout:fixed;border-collapse:collapse;">
    <tr><td align="center" style="padding:20px 20px 8px;color:#555b66;font-family:'DM Sans',Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;text-align:center;word-break:break-word;overflow-wrap:anywhere;">
        @if($kampEmailFooterText = config('app.email_footer_text'))
            <p style="margin:0 0 4px;color:#555b66;font-size:13px;line-height:20px;">{{ $kampEmailFooterText }}</p>
        @else
            <p style="margin:0 0 4px;color:#555b66;font-size:13px;line-height:20px;">© {{ date('Y') }} {{ config('app.name') }}</p>
        @endif
        <p style="margin:0;color:#555b66;font-size:13px;line-height:20px;">Powered by <a href="https://hi.events?utm_source=app-email-footer" style="color:#555b66;text-decoration:underline;">Hi.Events</a></p>
    </td></tr>
</table>
