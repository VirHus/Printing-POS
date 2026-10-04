@extends('layouts.app')

@section('title', 'Edit User | Printing POS')

@section('page-title', 'Users')

@section('content')

<!-- User editing form -->
<head>

    <title>Edit User - Printing POS</title>

    <style>

        body {
            margin: 0;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
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

        body { background: #07111f; color: #dce7f1; }
        .container { width: 100%; max-width: 680px; margin: 0 auto; padding: 0; }
        .card { padding: clamp(20px, 4vw, 32px); background: #0c1828; border: 1px solid #2a4055; border-radius: 16px; box-shadow: 0 12px 35px rgba(0,0,0,.16); }
        .card h1 { margin: 0 0 24px; font-size: 20px; letter-spacing: -.02em; }
        .form-group { margin-bottom: 20px; }
        input, select { padding: 12px 13px; border-radius: 8px; }
        .actions { flex-wrap: wrap; gap: 10px; margin-top: 24px; }
        .btn { min-height: 40px; padding: 10px 16px; border-radius: 8px; font-weight: 600; }
        h1, label { color: #f8fafc; }
        input, select { background: #101d2e; border-color: #34465c; color: #f8fafc; }
        .note { color: #8298ad; }
        .btn { background: #078b89; } .btn-gray { background: #202f44; color: #dce7f1; }
        .errors { background: rgba(244,63,94,.12); color: #fda4af; }
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
                    @if(auth()->user()->isSuperadmin())<option value="Superadmin" @selected(old('role', $user->role) === 'Superadmin')>Superadmin</option>@endif

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


            <div class="form-group">
                <label for="store_id">Assigned store</label>
                <select id="store_id" name="store_id" required>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" @selected(old('store_id', $user->store_id) == $store->id)>{{ $store->name }}{{ $store->is_active ? '' : ' (Inactive)' }}</option>
                    @endforeach
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
@endsection
