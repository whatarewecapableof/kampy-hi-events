@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
{{-- One full-width pill, as in the university emails. Colour comes from the theme. --}}
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="button-cell button-cell-{{ $color }}" align="center">
            <a href="{{ $url }}" class="button button-{{ $color }}" target="_blank"
               rel="noopener">{{ $slot }}</a>
        </td>
    </tr>
</table>
