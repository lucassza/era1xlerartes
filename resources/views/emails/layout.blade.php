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
        body { margin: 0; padding: 0; background-color: #fcfafa; font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #2f2f35; }
        .wrapper { width: 100%; padding: 32px 0; background: #fcfafa; }
        .container { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 18px rgba(101, 0, 16, 0.06); }
        .header { background: #650010; padding: 28px 32px; text-align: center; }
        .header img { max-height: 72px; border-radius: 8px; }
        .header h1 { color: #ffffff; margin: 12px 0 0; font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 700; }
        .body { padding: 32px; line-height: 1.7; font-size: 15px; }
        .body h1, .body h2, .body h3 { font-family: 'Poppins', sans-serif; color: #2f2f35; }
        .body a { color: #650010; text-decoration: underline; }
        .button { display: inline-block; background: #650010; color: #ffffff !important; padding: 12px 22px; border-radius: 12px; font-weight: 700; text-decoration: none; margin: 16px 0; }
        .button:hover { background: #820018; }
        .meta { background: #f3f3f4; border-left: 4px solid #e95d78; padding: 14px 18px; margin: 18px 0; border-radius: 6px; font-size: 14px; color: #2f2f35; }
        .footer { padding: 18px 32px; text-align: center; font-size: 12px; color: #666666; background: #f3f3f4; border-top: 1px solid #ececec; }
        .footer a { color: #666666; text-decoration: underline; }
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
                <img src="{{ asset('images/brand/logo-fundo-branco.png') }}" alt="{{ config('app.name') }}">
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
