{{-- Plain-text twin of emails/partials/footer.blade.php. Keep the attribution outside the conditional. --}}
@if($kampEmailFooterText = config('app.email_footer_text'))
{!! $kampEmailFooterText !!}
@else
© {{ date('Y') }} {!! config('app.name') !!}
@endif
Powered by Hi.Events (https://hi.events)
