<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__utilities">
                <h1 class="header__logo">Atte</h1>
                @yield('header__nav')
            </div>
        </div>
    </header>

    <main>
        <div class="main">
            @yield('content')
        </div>
    </main>

    <footer class="footer">
        <div class="footer__inner">
            <small>&copy; Atte, inc.</small>
        </div>
    </footer>

    @yield('scripts')
</body>

</html>