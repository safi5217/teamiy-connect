<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign in') &middot; Teamiy Connect</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .auth-alert {
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            line-height: 1.45;
            margin-bottom: 18px;
            padding: 11px 13px;
        }

        .auth-alert.success {
            background: #e7f6ec;
            border: 1px solid #c8ebd2;
            color: #1a7f44;
        }

        .auth-alert.danger {
            background: #fdecec;
            border: 1px solid #f6d4d4;
            color: #c0392b;
        }

        .auth-actions {
            margin-top: 18px;
            text-align: center;
        }
    </style>
</head>

<body data-page="login">
    <div class="login" id="login">
        <div class="login-panel">
            <div class="blob1"></div>
            <div class="blob2"></div>
            <div class="login-brand">
                <div class="login-mark">T</div>
                <span style="font-weight:800;font-size:19px;letter-spacing:0">Teamiy Connect</span>
            </div>
            <div class="login-hero">
                <h1>Your whole workday, in one calm place.</h1>
                <p>Leave, attendance, projects, assets, meetings and TADA - everything an employee needs, without the
                    spreadsheet chaos.</p>
                <div class="login-stats">
                    <div>
                        <div class="n">10</div>
                        <div class="l">Modules</div>
                    </div>
                    <div>
                        <div class="n">1 min</div>
                        <div class="l">To check in</div>
                    </div>
                    <div>
                        <div class="n">24/7</div>
                        <div class="l">Self-service</div>
                    </div>
                </div>
            </div>
            <div class="login-foot">&copy; {{ date('Y') }} Teamiy Connect &middot; v1.0</div>
        </div>
        @yield('content')
    </div>
    <script>
        document.querySelectorAll('.toggle-password').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.target || 'password');
                const isPassword = input?.getAttribute('type') === 'password';

                input?.setAttribute('type', isPassword ? 'text' : 'password');
                button.classList.toggle('fa-eye', !isPassword);
                button.classList.toggle('fa-eye-slash', isPassword);
            });
        });
    </script>
</body>

</html>
