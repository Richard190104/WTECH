<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>@yield('title', 'ElectroHub Admin')</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="shop-page">
    <div class="page-shell admin-page-shell">
        <header class="admin-topbar">
            <div class="container-xl admin-topbar-inner">
                <a href="{{ route('admin.products.index') }}" class="admin-brand">ElectroHub Admin</a>
                <div class="admin-topbar-actions">
                    @yield('admin-topbar-actions')
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-right-from-bracket"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="container-xl py-4">
            @yield('content')
        </main>
    </div>

    <script defer src="{{ asset('js/admin-product-images.js') }}"></script>
</body>
</html>