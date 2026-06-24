<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LuxNest Admin')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>

    <link rel="stylesheet" href="{{ asset_v('assets/css/admin-dashboard.css') }}">

    <style>
        html, body { height: 100%; }
    </style>

    @stack('styles')
</head>
<body>

    @yield('content')

    <script>
        const ADMIN_BASE = '{{ url("/admin/api") }}';
        const MEMBER_BASE = '{{ url("/me/api") }}';
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const USER_ROLE = '{{ auth()->user()->role ?? "" }}';
    </script>

    @stack('scripts')
</body>
</html>
