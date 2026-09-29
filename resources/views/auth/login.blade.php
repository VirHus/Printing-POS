<!DOCTYPE html>
<html>
<head>

    <title>Login - Printing POS</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-card {
            background: white;
            padding: 35px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, .1);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;

            border: 1px solid #ccc;
            border-radius: 6px;

            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .btn {
            width: 100%;
            padding: 12px;

            border: none;
            border-radius: 6px;

            background: #2563eb;
            color: white;

            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;

            padding: 10px;
            border-radius: 6px;

            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="login-container">

    <div class="login-card">

        <div class="logo">

            <h1>Printing POS</h1>

            <p>
                Point of Sale System
            </p>

        </div>


        @if ($errors->any())

            <div class="error">

                {{ $errors->first() }}

            </div>

        @endif


        <form
            method="POST"
            action="{{ route('login') }}"
        >

            @csrf


            <div class="form-group">

                <label for="email">
                    Email
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
                class="btn"
            >
                Login
            </button>

        </form>

    </div>

</div>

</body>
</html>