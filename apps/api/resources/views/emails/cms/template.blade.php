<!DOCTYPE html>
<html lang="{{ $direction === 'rtl' ? 'ar' : 'en' }}" dir="{{ $direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template['subject'] }}</title>
</head>
<body style="box-sizing:border-box;background:#f4f7fb;color:#334155;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;line-height:1.6;margin:0;padding:0;width:100%;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f4f7fb;margin:0;padding:0;width:100%;">
        <tr>
            <td align="center" style="padding:32px 12px 18px;text-align:center;">
                <a href="{{ $settings['brand_url'] }}" style="color:#002b6b;display:inline-block;font-size:20px;font-weight:800;letter-spacing:.02em;text-decoration:none;">
                    {{ $template['brand_name'] }}
                </a>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:0 12px;">
                <table width="620" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;border:1px solid #e7edf5;border-radius:18px;box-shadow:0 18px 40px rgba(15,23,42,.08);max-width:620px;overflow:hidden;width:100%;">
                    <tr>
                        <td style="background:{{ $settings['accent_color'] }};padding:28px 32px;text-align:{{ $direction === 'rtl' ? 'right' : 'left' }};">
                            <p style="color:#0d6efd;font-size:12px;font-weight:800;letter-spacing:.08em;margin:0 0 10px;text-transform:uppercase;">{{ $template['brand_name'] }}</p>
                            <h1 style="color:#002b6b;font-size:26px;font-weight:900;line-height:1.25;margin:0;text-align:start;">{{ $template['greeting'] }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;text-align:{{ $direction === 'rtl' ? 'right' : 'left' }};">
                            @if(!empty($template['intro']))
                                <p style="font-size:16px;margin:0 0 18px;">{{ $template['intro'] }}</p>
                            @endif
                            @if(!empty($template['expiry_notice']))
                                <p style="background:#f8fafc;border:1px solid #e7edf5;border-radius:14px;color:#334155;font-size:15px;margin:0 0 22px;padding:16px 18px;">{{ $template['expiry_notice'] }}</p>
                            @endif
                            @if(!empty($ctaUrl) && !empty($template['action_label']))
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:28px auto;text-align:center;">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $ctaUrl }}" target="_blank" rel="noopener" style="background:{{ $settings['button_color'] }};border-radius:12px;color:#ffffff;display:inline-block;font-size:15px;font-weight:800;padding:13px 24px;text-decoration:none;">
                                                {{ $template['action_label'] }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                            @if(!empty($template['no_action_notice']))
                                <p style="font-size:15px;margin:0 0 20px;">{{ $template['no_action_notice'] }}</p>
                            @endif
                            <p style="font-size:15px;margin:0;">{{ $template['salutation'] }}<br><strong style="color:#002b6b;">{{ $template['signature'] }}</strong></p>
                            @if(!empty($template['subcopy']))
                                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-top:1px solid #e7edf5;margin-top:28px;padding-top:22px;">
                                    <tr>
                                        <td>
                                            <p style="color:#64748b;font-size:13px;margin:0;word-break:break-word;">{{ $template['subcopy'] }}</p>
                                            @if(!empty($ctaUrl))
                                                <p style="font-size:12px;margin:10px 0 0;word-break:break-all;"><a href="{{ $ctaUrl }}" style="color:#0d6efd;">{{ $ctaUrl }}</a></p>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" style="color:#94a3b8;font-size:12px;padding:26px 12px 36px;text-align:center;">
                {{ $template['footer'] }}
            </td>
        </tr>
    </table>
</body>
</html>
