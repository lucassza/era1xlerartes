@php
    use Illuminate\Support\Facades\Config;
@endphp
@props(['title' => null, 'preview' => null])
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #fffafa; font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #1f0c0c; }
        .wrapper { width: 100%; padding: 32px 0; background: linear-gradient(180deg, #fffafa 0%, #fdf2f3 100%); }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 18px rgba(212, 32, 32, 0.06); }
        .header { background: linear-gradient(135deg, #d42020 0%, #e6287e 100%); padding: 28px 32px; text-align: center; }
        .header img { max-height: 56px; }
        .header h1 { color: #ffffff; margin: 12px 0 0; font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 700; }
        .body { padding: 32px; line-height: 1.7; font-size: 15px; }
        .body h1, .body h2, .body h3 { font-family: 'Poppins', sans-serif; color: #1f0c0c; }
        .body a { color: #d42020; text-decoration: underline; }
        .button { display: inline-block; background: #d42020; color: #ffffff !important; padding: 12px 22px; border-radius: 12px; font-weight: 700; text-decoration: none; margin: 16px 0; }
        .button:hover { background: #b81818; }
        .meta { background: #fdf2f3; border-left: 4px solid #e6287e; padding: 14px 18px; margin: 18px 0; border-radius: 6px; font-size: 14px; color: #1f0c0c; }
        .footer { padding: 18px 32px; text-align: center; font-size: 12px; color: #7a5555; background: #fdf2f3; border-top: 1px solid #f5d8d8; }
        .footer a { color: #7a5555; text-decoration: underline; }
        @media only screen and (max-width: 600px) {
            .container { border-radius: 0; }
            .body, .header, .footer { padding-left: 20px; padding-right: 20px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <img src="{{ asset('images/brand/logo-peq.png') }}" alt="{{ config('app.name') }}">
                @if ($title)
                    <h1>{{ $title }}</h1>
                @endif
            </div>
            <div class="body">
                {{ $slot }}
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} {{ config('app.name') }} ·
                <a href="{{ route('home') }}">{{ route('home') }}</a>
            </div>
        </div>
    </div>
</body>
</html>
