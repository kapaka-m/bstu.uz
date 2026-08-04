<!DOCTYPE html>
<html lang="{{ str_starts_with($direction, 'rtl') ? 'ar' : 'en' }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template['subject'] }}</title>
</head>
<body style="box-sizing:border-box;background:#fafafa;color:#52525b;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;line-height:1.5;margin:0;padding:0;width:100%;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#fafafa;margin:0;padding:0;width:100%;">
        <tr>
            <td align="center" style="padding:25px 12px;text-align:center;">
                <a href="{{ $settings['brand_url'] }}" style="color:#18181b;display:inline-block;font-size:19px;font-weight:700;text-decoration:none;">
                    {{ $template['brand_name'] }}
                </a>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:0 12px;">
                <table width="570" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;border:1px solid #e4e4e7;border-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,.1);max-width:570px;width:100%;">
                    <tr>
                        <td style="padding:32px;text-align:{{ $direction === 'rtl' ? 'right' : 'left' }};">
                            <h1 style="color:#18181b;font-size:18px;font-weight:700;margin:0 0 16px;text-align:start;">{{ $template['greeting'] }}</h1>
                            <p style="font-size:16px;margin:0 0 24px;">{{ $template['intro'] }}</p>
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:30px auto;text-align:center;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}" target="_blank" rel="noopener" style="background:{{ $settings['button_color'] }};border:8px solid {{ $settings['button_color'] }};border-left-width:18px;border-right-width:18px;border-radius:4px;color:#ffffff;display:inline-block;font-size:15px;font-weight:700;text-decoration:none;">
                                            {{ $template['action_label'] }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="font-size:16px;margin:0 0 16px;">{{ $template['expiry_notice'] }}</p>
                            <p style="font-size:16px;margin:0 0 16px;">{{ $template['no_action_notice'] }}</p>
                            <p style="font-size:16px;margin:0 0 8px;">{{ $template['salutation'] }}<br>{{ $template['signature'] }}</p>
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-top:1px solid #e4e4e7;margin-top:25px;padding-top:25px;">
                                <tr>
                                    <td>
                                        <p style="font-size:14px;margin:0;word-break:break-word;">{{ $template['subcopy'] }} <a href="{{ $resetUrl }}" style="color:#18181b;word-break:break-all;">{{ $resetUrl }}</a></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" style="color:#a1a1aa;font-size:12px;padding:32px 12px;text-align:center;">
                {{ $template['footer'] }}
            </td>
        </tr>
    </table>
</body>
</html>
