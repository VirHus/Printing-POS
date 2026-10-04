<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | {{ $branding['system_name'] }}</title>
    @if($branding['favicon_path'])<link rel="icon" type="image/png" href="{{ asset('storage/'.$branding['favicon_path']) }}">@endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            background:
                linear-gradient(
                    135deg,
                    #eff6ff,
                    #f8fafc
                );
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 950px;
            min-height: 570px;
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow:
                0 20px 60px
                rgba(15, 23, 42, .12);
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .login-brand {
            background: #111827;
            color: #fff;
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            width: 65px;
            height: 65px;
            border-radius: 16px;
            background: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 25px;
        }

        .login-brand h1 {
            font-size: 34px;
            margin-bottom: 14px;
        }

        .login-brand p {
            color: #9ca3af;
            line-height: 1.7;
            font-size: 14px;
            max-width: 370px;
        }

        .features {
            margin-top: 30px;
        }

        .feature {
            display: flex;
            gap: 10px;
            margin-bottom: 13px;
            color: #d1d5db;
            font-size: 13px;
        }

        .feature span:first-child {
            color: #60a5fa;
        }

        .login-form {
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form h2 {
            font-size: 27px;
            color: #111827;
            margin-bottom: 8px;
        }

        .login-form .subtitle {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            font-size: 14px;
            outline: none;
        }

        input:focus {
            border-color: #2563eb;
            box-shadow:
                0 0 0 3px
                rgba(37,99,235,.1);
        }

        .login-button {
            width: 100%;
            border: 0;
            padding: 13px;
            border-radius: 9px;
            background: #2563eb;
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            margin-top: 5px;
        }

        .login-button:hover {
            background: #1d4ed8;
        }

        .error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 11px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 18px;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }

        @media (max-width: 700px) {

            .login-container {
                grid-template-columns: 1fr;
            }

            .login-brand {
                display: none;
            }

            .login-form {
                padding: 35px 25px;
            }

        }

        body { padding: clamp(12px, 3vw, 28px); background: radial-gradient(circle at 15% 15%, #123a43 0, #07111f 38%, #050c16 100%); color: #e5edf5; }
        .login-container { max-width: 980px; min-height: 580px; background: #0c1828; border: 1px solid #2a4055; box-shadow: 0 28px 80px rgba(0,0,0,.4); }
        .login-brand { padding: clamp(36px, 5vw, 58px); background: linear-gradient(145deg, #172b3d, #0b1b2b); }
        .login-form { padding: clamp(30px, 5vw, 58px); }
        .logo { background: linear-gradient(135deg, #14b8a6, #087875); }
        .feature span:first-child { color: #5eead4; }
        .login-form h2 { color: #f8fafc; }
        .login-form .subtitle, .footer { color: #8298ad; }
        label { color: #cbd5e1; }
        input { background: #101d2e; border-color: #34465c; color: #f8fafc; }
        input::placeholder { color: #64748b; }
        input:focus { border-color: #14b8a6; box-shadow: 0 0 0 3px rgba(20,184,166,.14); }
        .login-button { background: #078b89; }
        .login-button:hover { background: #0a9f9b; }
        .error { background: rgba(244,63,94,.12); border-color: rgba(244,63,94,.35); color: #fda4af; }
        @media (max-width: 700px) { .login-container { min-height: auto; border-radius: 16px; } .login-form { padding: 30px 22px; } }
    </style>

    @include('settings._theme', ['branding' => $branding])
    <style>.login-cover { width: 100%; max-height: 180px; object-fit: cover; border-radius: 12px; margin-bottom: 24px; } .logo { overflow: hidden; }</style>
</head>

<body class="glass-theme">

<div class="login-container">

    <div class="login-brand">
        @if($branding['login_image_path'])<img src="{{ asset('storage/'.$branding['login_image_path']) }}" alt="{{ $branding['system_name'] }}" class="login-cover">@endif

        <div class="logo">
            @if($branding['logo_path'])<img src="{{ asset('storage/'.$branding['logo_path']) }}" alt="{{ $branding['system_name'] }} logo">@else 🖨️ @endif
        </div>

        <h1>{{ $branding['system_name'] }}</h1>

        <p>
            {{ $branding['login_description'] }}
        </p>

        <div class="features">

            <div class="feature">
                <span>✓</span>
                <span>Fast sales processing</span>
            </div>

            <div class="feature">
                <span>✓</span>
                <span>Service and price management</span>
            </div>

            <div class="feature">
                <span>✓</span>
                <span>Transaction history</span>
            </div>

            <div class="feature">
                <span>✓</span>
                <span>Sales reports</span>
            </div>

        </div>

    </div>


    <div class="login-form">

        <h2>Welcome back</h2>

        <p class="subtitle">
            Sign in to continue to your POS system.
        </p>


        @if($errors->any())

            <div class="error">
                {{ $errors->first() }}
            </div>

        @endif


        <form
            method="POST"
            action="{{ route('login.process') }}"
        >

            @csrf


            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    required
                    autofocus
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-button"
            >
                Sign In
            </button>

        </form>


        <div class="footer">
            {{ $branding['system_name'] }} {{ $branding['tagline'] }}
        </div>

    </div>

</div>

</body>
</html>
