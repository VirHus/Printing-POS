@extends('layouts.app')

@section('title', 'Services | Printing POS')

@section('page-title', 'Services')

@section('content')

<!-- your existing Products page content here -->

@endsection
<head>

    <title>User Management - Printing POS</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .navbar {
            background: #1f2937;
            color: white;
            padding: 15px 25px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 18px;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0,0,0,.08);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f1f3f5;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;

            background: #2563eb;
            color: white;

            border: none;
            border-radius: 6px;

            text-decoration: none;
            cursor: pointer;
        }

        .btn-green {
            background: #16a34a;
        }

        .btn-red {
            background: #dc2626;
        }

        .btn-gray {
            background: #6b7280;
        }

        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .success {
            background: #dcfce7;
            color: #166534;

            padding: 12px;
            border-radius: 6px;

            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;

            padding: 12px;
            border-radius: 6px;

            margin-bottom: 20px;
        }

        .badge {
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
        }

        .active {
            background: #dcfce7;
            color: #166534;
        }

        .inactive {
            background: #fee2e2;
            color: #991b1b;
        }

    </style>

</head>

<body>

<div class="navbar">

    <h2>Printing POS</h2>

    <div class="nav-links">

        <a href="{{ route('dashboard') }}">
            Dashboard
        </a>

        <a href="{{ route('pos.index') }}">
            POS
        </a>

        <a href="{{ route('products.index') }}">
            Services
        </a>

        <a href="{{ route('sales.index') }}">
            Sales
        </a>

        <a href="{{ route('users.index') }}">
            Users
        </a>

    </div>

</div>


<div class="container">

    <div class="card">

        <div class="header">

            <h1>User Management</h1>

            <a
                href="{{ route('users.create') }}"
                class="btn"
            >
                + Add User
            </a>

        </div>


        @if (session('success'))

            <div class="success">
                {{ session('success') }}
            </div>

        @endif


        @if ($errors->any())

            <div class="error">
                {{ $errors->first() }}
            </div>

        @endif


        <table>

            <thead>

                <tr>

                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

                @forelse ($users as $user)

                    <tr>

                        <td>
                            {{ $user->name }}
                        </td>

                        <td>
                            {{ $user->email }}
                        </td>

                        <td>
                            {{ $user->role }}
                        </td>

                        <td>

                            @if ($user->is_active)

                                <span class="badge active">
                                    Active
                                </span>

                            @else

                                <span class="badge inactive">
                                    Inactive
                                </span>

                            @endif

                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="{{ route(
                                        'users.edit',
                                        $user
                                    ) }}"
                                    class="btn"
                                >
                                    Edit
                                </a>


                                @if ($user->id !== auth()->id())

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'users.toggle',
                                            $user
                                        ) }}"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-gray"
                                        >
                                            {{ $user->is_active
                                                ? 'Deactivate'
                                                : 'Activate' }}
                                        </button>

                                    </form>


                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'users.destroy',
                                            $user
                                        ) }}"
                                        onsubmit="return confirm(
                                            'Delete this user?'
                                        );"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-red"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5">
                            No users found.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</body>
</html>