@props([
'url',
'color' => 'primary',
'align' => 'center',
])

<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <a href="{{ $url }}" class="button" target="_blank"
                            style="background-color:#e11d48; color:white; padding:12px 24px; border-radius:6px; font-weight:600; text-decoration:none; display:inline-block;">
                            {{ $slot }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
