<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('login.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <title>Login</title>
</head>
<body>
    <div class="body"> 
    <div class="container-fluid">
        <div class="wrapper">
            <div class="main">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if ($message=Session::get('login failed'))
        <script>
            Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "{{ $message }}",
            });
        </script>
    @endif
    @if ($message=Session::get('role failed'))
        <script>
            Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "{{ $message }}",
            });
        </script>
    @endif
    @if ($message=Session::get('logout success'))
        <script>
            Swal.fire({
            icon: "success",
            text: "{{ $message }}",
            });
        </script>
    @endif
    @if ($message=Session::get('reset success'))
        <script>
            Swal.fire({
            icon: "success",
            text: "{{ $message }}",
            });
        </script>
    @endif
    </div>
</body>
</html>