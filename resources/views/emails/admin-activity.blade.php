<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;color:#333;line-height:1.6;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;">
        <div style="background:#FF9900;padding:20px;text-align:center;color:#ffffff;font-size:20px;font-weight:600;">
            Lome+ · Administration
        </div>
        <div style="padding:30px;">
            <h1 style="font-size:20px;margin:0 0 20px 0;color:#333;">{{ $heading }}</h1>

            <table style="width:100%;border-collapse:collapse;font-size:15px;">
                @foreach ($details as $label => $value)
                    <tr>
                        <td style="padding:6px 10px 6px 0;color:#777;vertical-align:top;white-space:nowrap;">{{ $label }}</td>
                        <td style="padding:6px 0;color:#333;">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>

            @if ($bodyText)
                <div style="margin-top:20px;padding:15px;background:#fff8ec;border-left:4px solid #FF9900;border-radius:4px;font-size:15px;">
                    {!! nl2br(e($bodyText)) !!}
                </div>
            @endif

            <div style="text-align:center;margin-top:30px;">
                <a href="{{ $actionUrl }}" style="display:inline-block;background:#FF9900;color:#ffffff;padding:12px 28px;text-decoration:none;border-radius:6px;font-weight:600;">
                    {{ $actionLabel }}
                </a>
            </div>
        </div>
        <div style="padding:15px;text-align:center;font-size:12px;color:#999;background:#fafafa;">
            Notification automatique de Lome+ — {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>
