<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LuxNest')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>

    <link rel="stylesheet" href="{{ asset_v('assets/css/auth.css') }}">
</head>
<body class="auth-body">

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('favicon.png') }}" alt="LuxNest" style="height:72px; width:auto; display:block; margin:0 auto;">
                </a>
            </div>

            @yield('content')
        </div>
    </div>

</body>
</html>
