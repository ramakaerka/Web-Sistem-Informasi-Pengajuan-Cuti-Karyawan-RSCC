<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('approval.css') }}">
</head>
<body>
@if ($cuti->karyawan)
<table class="table-approval">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Jenis Cuti</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Jumlah Hari</th>
                        <th scope="col">Alasan</th>
                        <th scope="col">Sisa Cuti</th>
                    </tr>
                </thead>
                <tbody>
                        <td>
                            <p>{{ $cuti->karyawan->nama_lengkap }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->jenis_cuti ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->tanggal_mulai ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->tanggal_selesai ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->jumlah_hari ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->alasan ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->karyawan->sisa_cuti + $cuti->karyawan->sisa_cuti_sebelumnya }}</p>
                        </td>
                </tbody>
    </table>
@elseif ($cuti->manager)
    <table class="table-approval">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Jenis Cuti</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Jumlah Hari</th>
                        <th scope="col">Alasan</th>
                        <th scope="col">Sisa Cuti</th>
                    </tr>
                </thead>
                <tbody>
                        <td>
                            <p>{{ $cuti->manager->nama_lengkap }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->jenis_cuti ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->tanggal_mulai ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->tanggal_selesai ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->jumlah_hari ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->alasan ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->manager->sisa_cuti + $cuti->manager->sisa_cuti_sebelumnya }}</p>
                        </td>
                </tbody>
    </table>
@elseif ($cuti->admin)
<table class="table-approval">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Jenis Cuti</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Jumlah Hari</th>
                        <th scope="col">Alasan</th>
                        <th scope="col">Sisa Cuti</th>
                    </tr>
                </thead>
                <tbody>
                        <td>
                            <p>{{ $cuti->admin->nama_lengkap }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->jenis_cuti ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->tanggal_mulai ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->tanggal_selesai ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->jumlah_hari ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->alasan ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $cuti->admin->sisa_cuti + $cuti->admin->sisa_cuti_sebelumnya }}</p>
                        </td>
                </tbody>
    </table>
@endif
    
</body>
</html>