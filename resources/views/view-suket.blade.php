<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet">
    <title>Surat Cuti</title>
</head>
<body>
    @if ($surat_keterangan)
        
        <div class="container-fluid table-approval" style="margin-top: 20px; margin-left:700px; padding: 30px; justify-content:center; align-items:center;">
            <div class="text-center align-middle">
                <h1><strong>Surat Keterangan</strong></h1 class="text-center">
                <img src="{{ asset('storage/'.$cuti->surat_keterangan) }}" class="rounded mx-auto d-block" width="500" height="650" alt="foto_suket">
            </div>
        </div>
    @else
        <div class="container-fluid table-approval" style="margin top: 20px; padding:20px; justify-content:center; align-items:center">
            <div class="text-center align-middle">
                <h1><strong>Surat Keterangan Kosong</strong></h1>
            </div>
        </div>
    @endif
    
</body>
</html>