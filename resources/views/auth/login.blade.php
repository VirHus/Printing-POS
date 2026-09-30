<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Printing POS</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
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

    </style>

</head>

<body>

<div class="login-container">

    <div class="login-brand">

        <div class="logo">
            🖨️
        </div>

        <h1>Printing POS</h1>

        <p>
            A simple and modern point-of-sale
            management system for printing,
            photocopy, scanning, and rush ID services.
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
            Printing POS Management System
        </div>

    </div>

</div>

</body>
</html>