<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir Permohonan Cuti</title>
    <style type="text/css">
        /* Gaya Dasar */
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 10px;
        }
        
        /* Header Instansi */
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 110px;
            vertical-align: top;
            padding-right: 15px;
        }
        .logo-cell img {
            width: 100%;
            height: auto;
        }
        .instansi-cell {
            vertical-align: middle;
        }
        .instansi-name {
            font-weight: bold;
            margin: 0;
        }
        .alamat {
            font-size: 10pt;
            margin: 5px 0 0 0;
        }
        
        /* Judul Form */
        .form-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 20px 0;
            font-size: 12pt;
        }
        
        /* Data Utama */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        .label-cell {
            width: 40%;
            font-weight: bold;
        }
        
        /* Garis Pembatas */
        .divider {
            border-top: 2px solid #000;
            margin: 15px 0;
        }
        
        /* Tanda Tangan */
        .signature-table {
            width: 100%;
            margin-top: 50px;
        }
        .signature-table td {
            vertical-align: bottom;
            padding: 0 10px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin: 60px auto 5px;
        }
    </style>
</head>
<body>
    <!-- Header dengan Tabel -->
    @if ($cuti->admin)
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="<?php echo public_path('images/logo.png'); ?>" alt="Logo">
            </td>
            <td class="instansi-cell">
                <h3 class="instansi-name">PT. KARYA MITRA PRATAMA</h3>
                <h2 class="instansi-name">RUMAH SAKIT CONDONG CATUR</h2>
                <p class="alamat">
                    Jl. Manggis No.6 Gempol, Condongcatur, Depok, Sleman, Yogyakarta | 
                    Telp (0274) 887494, 4463083 | Email : rscc_yogya@yahoo.co.id | 
                    Website : www.rs-condongcatur.com
                </p>
            </td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <!-- Judul Form -->
    <div class="form-title">FORMULIR PERMOHONAN CUTI</div>
    
    <!-- Data Pemohon -->
    <table class="data-table">
        <tr>
            <td style="width:50%">
                Kepada Yth. Bagian Kepegawaian & Diklat<br>
                Mohon diberikan izin tidak masuk kerja karena : <strong>{{ $cuti->jenis_cuti }}</strong>
            </td>
            <td style="width: 50%">
                <div>Nama : <strong>{{ $cuti->admin->nama_lengkap }}</strong></div>
                <div>Unit : <strong>{{ $cuti->admin->unit }}</strong></div>
                <div>Jabatan : <strong>{{ $cuti->admin->jabatan }}</strong></div>
                <div>No Pokok Karyawan : <strong>{{ $cuti->admin->no_pokok }}</strong></div>
                <div>Alamat : <strong>{{ $cuti->admin->alamat }}</strong></div>
            </td>
        </tr>
        
    </table>
    
    <div class="divider"></div>
    
    <!-- Detail Cuti -->
    <table class="data-table">
        <tr>
            <td style="width: 50%">
                <div>Tanggal Mulai</div>
                <div>Tanggal Selesai</div>
                <div>Periode Cuti (dalam tahun)</div>
                <div>Sisa Cuti Tahun Ini yang Masih Berlaku</div>
                <div>Alasan Cuti</div>
            </td>
            <td style="width: 50%">
                <div><strong>:  {{ $cuti->tanggal_mulai }}</strong></div>
                <div><strong>:  {{ $cuti->tanggal_selesai }}</strong></div>
                <div><strong>:  {{ $cuti->jumlah_hari }}</strong></div>
                <div><strong>:  {{ $cuti->admin->sisa_cuti + $cuti->admin->sisa_cuti_sebelumnya }}</strong></div>
                <div><strong>:  {{ $cuti->alasan }}</strong></div>
            </td>
        </tr>
        
    </table>
    
    @if ($alasanPenolakan)
    <div class="divider"></div>
    
    <table class="data-table" style="margin-bottom: 10px">
        <tr>
            <td>
                
                <div><strong>Alasan penolakan : {{ $alasanPenolakan }}</strong></div>
            </td>
        </tr>
    </table>
    @endif

    <div class="divider" style="margin-bottom: 0"></div>
    <!-- Tanda Tangan -->
    <table class="signature-table" style="margin-top: 10px;">
        <tr>
            <td>
                <div style="font-size: 0.7rem">Tanggal Pengajuan Cuti : <strong>{{ $cuti->tanggal_pengajuan }}</strong></div>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="width:20%;">
                <div style="margin-top: 10px; text-align: center; font-size: 0.7rem;">Pemohon,</div>

                <div class="container-ttd" style="text-align:center; margin-top: 10px;">
                    <img src="{{ $ttd_pemohon }}" style="width: 100px;height:50px; justify-content: center; text-align: center ; align-items: center;">
                </div>
                <div class="signature-line" style="margin-top: 10px"></div>
                <div style="text-align: center; font-size: 0.7rem">{{ $cuti->admin->nama_lengkap }}</div>
            </td>
        </tr>
    </table>

    <div class="divider" style="margin-top: 10px; border: none; border-top: 1px dashed #999;"></div>

    <table style="margin-top:10px; width: 100%;">
        <tr>
            @if ($ttd_kabag)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Kepala Bagian {{ $cuti->approval_kabag->unit }}</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_kabag }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_kabag->nama_lengkap }}</div>
                </div>
            </td>
            @endif
            @if ($ttd_admin)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Admin (Kasie {{ $cuti->approval_admin->unit }})</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_admin }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_admin->nama_lengkap }}</div>
                </div>
            </td>
            @endif
            @if ($ttd_direktur)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Direktur</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_direktur }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_direktur->nama_lengkap }}</div>
                </div>
            </td>
            @endif
        </tr>
    </table>
    @elseif ($cuti->manager)
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="<?php echo public_path('images/logo.png'); ?>" alt="Logo">
            </td>
            <td class="instansi-cell">
                <h3 class="instansi-name">PT. KARYA MITRA PRATAMA</h3>
                <h2 class="instansi-name">RUMAH SAKIT CONDONG CATUR</h2>
                <p class="alamat">
                    Jl. Manggis No.6 Gempol, Condongcatur, Depok, Sleman, Yogyakarta | 
                    Telp (0274) 887494, 4463083 | Email : rscc_yogya@yahoo.co.id | 
                    Website : www.rs-condongcatur.com
                </p>
            </td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <!-- Judul Form -->
    <div class="form-title">FORMULIR PERMOHONAN CUTI</div>
    
    <!-- Data Pemohon -->
    <table class="data-table">
        <tr>
            <td style="width:50%">
                Kepada Yth. Bagian Kepegawaian & Diklat<br>
                Mohon diberikan izin tidak masuk kerja karena : <strong>{{ $cuti->jenis_cuti }}</strong>
            </td>
            <td style="width: 50%">
                <div>Nama : <strong>{{ $cuti->manager->nama_lengkap }}</strong></div>
                <div>Unit : <strong>{{ $cuti->manager->unit }}</strong></div>
                <div>Jabatan : <strong>{{ $cuti->manager->jabatan }}</strong></div>
                <div>No Pokok Karyawan : <strong>{{ $cuti->manager->no_pokok }}</strong></div>
                <div>Alamat : <strong>{{ $cuti->manager->alamat }}</strong></div>
            </td>
        </tr>
        
    </table>
    
    <div class="divider"></div>
    
    <!-- Detail Cuti -->
    <table class="data-table">
        <tr>
            <td style="width: 50%">
                <div>Tanggal Mulai</div>
                <div>Tanggal Selesai</div>
                <div>Periode Cuti (dalam tahun)</div>
                <div>Sisa Cuti Tahun Ini yang Masih Berlaku</div>
                <div>Alasan Cuti</div>
            </td>
            <td style="width: 50%">
                <div><strong>:  {{ $cuti->tanggal_mulai }}</strong></div>
                <div><strong>:  {{ $cuti->tanggal_selesai }}</strong></div>
                <div><strong>:  {{ $cuti->jumlah_hari }}</strong></div>
                <div><strong>:  {{ $cuti->manager->sisa_cuti + $cuti->manager->sisa_cuti_sebelumnya }}</strong></div>
                <div><strong>:  {{ $cuti->alasan }}</strong></div>
            </td>
        </tr>
        
    </table>
    
    @if ($alasanPenolakan)
    <div class="divider"></div>
    
    <table class="data-table" style="margin-bottom: 10px">
        <tr>
            <td>
                
                <div><strong>Alasan penolakan : {{ $alasanPenolakan }}</strong></div>
            </td>
        </tr>
    </table>
    @endif

    <div class="divider" style="margin-bottom: 0"></div>
    <!-- Tanda Tangan -->
    <table class="signature-table" style="margin-top: 10px;">
        <tr>
            <td>
                <div style="font-size: 0.7rem">Tanggal Pengajuan Cuti : <strong>{{ $cuti->tanggal_pengajuan }}</strong></div>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="width:20%;">
                <div style="margin-top: 10px; text-align: center; font-size: 0.7rem;">Pemohon,</div>

                <div class="container-ttd" style="text-align:center; margin-top: 10px;">
                    <img src="{{ $ttd_pemohon }}" style="width: 100px;height:50px; justify-content: center; text-align: center ; align-items: center;">
                </div>
                <div class="signature-line" style="margin-top: 10px"></div>
                <div style="text-align: center; font-size: 0.7rem">{{ $cuti->manager->nama_lengkap }}</div>
            </td>
        </tr>
    </table>

    <div class="divider" style="margin-top: 10px; border: none; border-top: 1px dashed #999;"></div>

    <table style="margin-top:10px; width: 100%;">
        <tr>
            @if ($ttd_kasi)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Kepala Seksi</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_kasi }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_kasi->nama_lengkap }}</div>
                </div>
            </td>
            @endif
            @if ($ttd_kabag)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Kepala Bagian {{ $cuti->approval_kabag->unit }}</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_kabag }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_kabag->nama_lengkap }}</div>
                </div>
            </td>
            @endif
            @if ($ttd_admin)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Admin (Kasie {{ $cuti->approval_admin->unit }})</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_admin }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_admin->nama_lengkap }}</div>
                </div>
            </td>
            @endif
            @if ($ttd_direktur)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Direktur</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_direktur }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_direktur->nama_lengkap }}</div>
                </div>
            </td>
            @endif
        </tr>
    </table>
    @elseif ($cuti->karyawan)
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="<?php echo public_path('images/logo.png'); ?>" alt="Logo">
            </td>
            <td class="instansi-cell">
                <h3 class="instansi-name">PT. KARYA MITRA PRATAMA</h3>
                <h2 class="instansi-name">RUMAH SAKIT CONDONG CATUR</h2>
                <p class="alamat">
                    Jl. Manggis No.6 Gempol, Condongcatur, Depok, Sleman, Yogyakarta | 
                    Telp (0274) 887494, 4463083 | Email : rscc_yogya@yahoo.co.id | 
                    Website : www.rs-condongcatur.com
                </p>
            </td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <!-- Judul Form -->
    <div class="form-title">FORMULIR PERMOHONAN CUTI</div>
    
    <!-- Data Pemohon -->
    <table class="data-table">
        <tr>
            <td style="width:50%">
                Kepada Yth. Bagian Kepegawaian & Diklat<br>
                Mohon diberikan izin tidak masuk kerja karena : <strong>{{ $cuti->jenis_cuti }}</strong>
            </td>
            <td style="width: 50%">
                <div>Nama : <strong>{{ $cuti->karyawan->nama_lengkap }}</strong></div>
                <div>Unit : <strong>{{ $cuti->karyawan->unit }}</strong></div>
                <div>Jabatan : <strong>{{ $cuti->karyawan->jabatan }}</strong></div>
                <div>No Pokok Karyawan : <strong>{{ $cuti->karyawan->no_pokok }}</strong></div>
                <div>Alamat : <strong>{{ $cuti->karyawan->alamat }}</strong></div>
            </td>
        </tr>
        
    </table>
    
    <div class="divider"></div>
    
    <!-- Detail Cuti -->
    <table class="data-table">
        <tr>
            <td style="width: 50%">
                <div>Tanggal Mulai</div>
                <div>Tanggal Selesai</div>
                <div>Periode Cuti (dalam tahun)</div>
                <div>Sisa Cuti Tahun Ini yang Masih Berlaku</div>
                <div>Alasan Cuti</div>
            </td>
            <td style="width: 50%">
                <div><strong>:  {{ $cuti->tanggal_mulai }}</strong></div>
                <div><strong>:  {{ $cuti->tanggal_selesai }}</strong></div>
                <div><strong>:  {{ $cuti->jumlah_hari }}</strong></div>
                <div><strong>:  {{ $cuti->karyawan->sisa_cuti + $cuti->karyawan->sisa_cuti_sebelumnya }}</strong></div>
                <div><strong>:  {{ $cuti->alasan }}</strong></div>
            </td>
        </tr>
        
    </table>
    
    @if ($alasanPenolakan)
    <div class="divider"></div>
    
    <table class="data-table" style="margin-bottom: 10px">
        <tr>
            <td>
                
                <div><strong>Alasan penolakan : {{ $alasanPenolakan }}</strong></div>
            </td>
        </tr>
    </table>
    @endif

    <div class="divider" style="margin-bottom: 0"></div>
    <!-- Tanda Tangan -->
    <table class="signature-table" style="margin-top: 10px;">
        <tr>
            <td>
                <div style="font-size: 0.7rem">Tanggal Pengajuan Cuti : <strong>{{ $cuti->tanggal_pengajuan }}</strong></div>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="width:20%;">
                <div style="margin-top: 10px; text-align: center; font-size: 0.7rem;">Pemohon,</div>

                <div class="container-ttd" style="text-align:center; margin-top: 10px;">
                    <img src="{{ $ttd_pemohon }}" style="width: 100px;height:50px; justify-content: center; text-align: center ; align-items: center;">
                </div>
                <div class="signature-line" style="margin-top: 10px"></div>
                <div style="text-align: center; font-size: 0.7rem">{{ $cuti->karyawan->nama_lengkap }}</div>
            </td>
        </tr>
    </table>

    <div class="divider" style="margin-top: 10px; border: none; border-top: 1px dashed #999;"></div>

    <table style="margin-top:10px; width: 100%;">
        <tr>
            @if ($ttd_kanit)
            <td style="width: auto">
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Kepala Unit</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_kanit }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_atasan->nama_lengkap }}</div>
                </div>
            </td>
            @endif
            @if ($ttd_kanit_admin)
            <td style="width: auto">
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Kepala Unit</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_kanit_admin }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_atasan_admin->nama_lengkap }}</div>
                </div>
            </td>
            @endif
            @if ($ttd_kasi)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Kepala Seksi</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_kasi }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_kasi->nama_lengkap }}</div>
                </div>
            </td>
            @endif
            @if ($ttd_kabag)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Kepala Bagian</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_kabag }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_kabag->nama_lengkap }}</div>
                </div>
            </td>
            @endif
            @if ($ttd_admin)
            <td>
                <div style="margin-top: 0; text-align: center; font-size: 0.7rem;">Admin (Kasie SDM)</div>
                <div class="container-ttd" style="text-align: center; margin-top: 10px;">
                    <img src="{{ $ttd_admin }}" style="width: 100px;height:50px; justify-content: center; text-align: center; align-items: center; margin-bottom: 0; margin-top: 0">
                    <div class="signature-line" style="margin-top: 10px"></div>
                    <div style="font-size: 0.7rem;">{{ $cuti->approval_admin->nama_lengkap }}</div>
                </div>
            </td>
            @endif
        </tr>
    </table>
    @endif
    
</body>
</html>