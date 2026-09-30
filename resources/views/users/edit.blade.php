@extends('layouts.app')

@section('title', 'Services | Printing POS')

@section('page-title', 'Services')

@section('content')

<!-- your existing Products page content here -->

@endsection
<head>

    <title>Edit User - Printing POS</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .btn {
            padding: 10px 15px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-gray {
            background: #6b7280;
        }

        .errors {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .note {
            color: #666;
            font-size: 13px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>Edit User</h1>

        @if ($errors->any())

            <div class="errors">

                <ul>

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
            method="POST"
            action="{{ route('users.update', $user) }}"
        >

            @csrf

            @method('PUT')


            <div class="form-group">

                <label>Name</label>

                <input
                    type="text"
                    name="name"
                    value="{{ old(
                        'name',
                        $user->name
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    value="{{ old(
                        'email',
                        $user->email
                    ) }}"
                    required
                >

            </div>


            <div class="form-group">

                <label>Role</label>

                <select name="role" required>

                    <option
                        value="Cashier"
                        @selected($user->role === 'Cashier')
                    >
                        Cashier
                    </option>

                    <option
                        value="Administrator"
                        @selected(
                            $user->role === 'Administrator'
                        )
                    >
                        Administrator
                    </option>

                </select>

            </div>


            <hr>

            <p class="note">
                Leave the password fields empty if you
                do not want to change the password.
            </p>


            <div class="form-group">

                <label>New Password</label>

                <input
                    type="password"
                    name="password"
                >

            </div>


            <div class="form-group">

                <label>Confirm New Password</label>

                <input
                    type="password"
                    name="password_confirmation"
                >

            </div>


            <div class="actions">

                <button
                    type="submit"
                    class="btn"
                >
                    Update User
                </button>

                <a
                    href="{{ route('users.index') }}"
                    class="btn btn-gray"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>