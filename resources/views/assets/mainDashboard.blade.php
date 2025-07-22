<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sistem Pengajuan Cuti</title>


    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="{{ asset('datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="stylesheet" href="{{ asset('approval.css') }}">
    <link rel="stylesheet" href="{{ asset('status.css') }}">
    <link rel="stylesheet" href="{{ asset('suratCuti.css') }}">
    <link rel="stylesheet" href="{{ asset('addProfile.css') }}">
    <link rel="stylesheet" href="{{ asset('signaturepad.css') }}">
    <link rel="stylesheet" href="{{ asset('tanggalCutiSelected.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free-6.6.0-web/css/all.css') }}">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('toasts.css') }}">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @vite(['resources/js/app.js'])
    
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
   
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
  {{-- <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
    rel="stylesheet"
  /> --}}
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
  />
  
  {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />  --}}
  
</head>
<body>
    
    <div class="container-fluid vh-100 p-0">
    <div class="wrapper">
        @include('assets.sidebar',['key',''])
        <div class="main">
            @yield('content')
            
        </div>
    </div>
</div>
</body>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    
    @if ($message=Session::get('login success'))
<script>
    Swal.fire({
    icon: "success",
        text: "{{ 'Selamat datang '. $message .', anda telah berhasil login!' }}",
    });
</script>
@endif
@if ($message=Session::get('pengajuan success'))
<script>
    Swal.fire({
    icon: "success",
    text: "{{ $message }}",
    });
</script>
@endif
@if ($message=Session::get('profile success'))
<script>
    Swal.fire({
    icon: "success",
    text: "{{ $message }}",
    });
</script>
@endif
@if ($message=Session::get('approve success'))
<script>
    Swal.fire({
    icon: "success",
    text: "{{ $message }}",
    });
</script>
@endif
@if ($message=Session::get('no changes'))
        <script>
            Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "{{ $message }}",
            });
        </script>
    @endif
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</body>
</html>