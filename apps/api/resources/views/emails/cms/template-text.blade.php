{{ $template['brand_name'] }}: {{ $settings['brand_url'] }}

# {{ $template['greeting'] }}

{{ $template['intro'] }}

{{ $template['expiry_notice'] }}

@if(!empty($ctaUrl) && !empty($template['action_label']))
{{ $template['action_label'] }}: {{ $ctaUrl }}
@endif

{{ $template['no_action_notice'] }}

{{ $template['salutation'] }}
{{ $template['signature'] }}

{{ $template['subcopy'] }}
@if(!empty($ctaUrl))
{{ $ctaUrl }}
@endif

{{ $template['footer'] }}
