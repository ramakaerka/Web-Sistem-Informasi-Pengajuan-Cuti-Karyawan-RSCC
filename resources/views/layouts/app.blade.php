<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard Admin Cuti</title>

    @livewireStyles
    
    
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="{{ asset('laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="stylesheet" href="{{ asset('status.css') }}">
    <link rel="stylesheet" href="{{ asset('suratCuti.css') }}">
    <link rel="stylesheet" href="{{ asset('addProfile.css') }}">
    <link rel="stylesheet" href="{{ asset('grafik.css') }}">
    <link rel="stylesheet" href="{{ asset('tabelDataCutiLivewire.css') }}">
    <link rel="stylesheet" href="{{ asset('modalCuti.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free-6.6.0-web/css/all.css') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('toasts.css') }}">

    @livewireScripts
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @vite(['resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>

    <div class="container-fluid vh-100 p-0">
        <div class="wrapper">
            @include('assets.sidebar',['key',''])
            <div class="main">

                {{ $slot }}
                
                {{-- <script>
                    document.addEventListener('livewire:init', () => {
                        console.log('Livewire components:', Livewire.all())
                    })
                </script> --}}
            </div>
        </div>
    </div>

    
    
    
    
</body>
</html>