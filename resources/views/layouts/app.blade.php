<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AI Sales Agent')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        @include('partials.sidebar')

        <div class="main-shell">
            <header class="topbar">
                <div>
                    <div class="topbar-title">@yield('title', 'AI Sales Agent')</div>
                    <div class="topbar-subtitle">@yield('subtitle', 'Zestminds Outreach OS')</div>
                </div>

                <div class="d-none d-md-flex align-items-center gap-2">
                    <span class="badge text-bg-light border">Local V1</span>
                    <span class="badge text-bg-warning">Human Review Required</span>
                </div>
            </header>

            <main class="page-content">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Fix these:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            @include('partials.footer')
        </div>
    </div>
</body>
</html>