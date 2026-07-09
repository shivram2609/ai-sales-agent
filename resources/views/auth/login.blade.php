<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - AI Sales Agent</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
        }
        .login-shell {
            min-height: 100vh;
            display: flex;
        }
        .login-brand-panel {
            width: 40%;
            min-width: 320px;
            background: var(--zm-sidebar);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px;
        }
        .login-brand-panel .brand-title {
            font-size: 28px;
            font-weight: 800;
        }
        .login-brand-panel .brand-subtitle {
            margin-top: 6px;
            color: var(--zm-sidebar-muted);
            font-size: 14px;
        }
        .login-brand-panel .brand-rule {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--zm-sidebar-muted);
            font-size: 13px;
            line-height: 1.6;
        }
        .login-form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--zm-bg);
            padding: 24px;
        }
        .login-card {
            width: 100%;
            max-width: 380px;
        }
        .login-card .card-header {
            font-weight: 800;
            font-size: 18px;
        }
        @media (max-width: 768px) {
            .login-brand-panel {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="login-brand-panel">
            <div class="brand-title">AI Sales Agent</div>
            <div class="brand-subtitle">Zestminds Outreach OS</div>
            <div class="brand-rule">
                <strong>Rule:</strong><br>
                AI prepares. Human approves. No auto-spam.
            </div>
        </div>

        <div class="login-form-panel">
            <div class="login-card card">
                <div class="card-header">Log in</div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger py-2">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.attempt') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label small">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Log in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
