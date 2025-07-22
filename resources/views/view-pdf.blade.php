<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('suratCuti.css') }}">
    <title>Surat Cuti</title>
</head>
<body>
    
    <div class="modal-body">
        <a href="/admin/statusCuti/download_pdf/{{ $cuti->id }}">Download</a>
                                    <div class="grip-wrapper">
                                  <div class="kop-surat">
                                    <div class="kop-container">
                                        <div class="logo">
                                            <img src="{{ asset('images/logo.png') }}" alt="Logo Instansi">
                                        </div>
                                        <div class="kop-text">
                                            <h3>PT. KARYA MITRA PRATAMA</h3>
                                            <h2>RUMAH SAKIT CONDONG CATUR</h2>
                                            <p>Jl. Manggis No.6 Gempol, Condongcatur, Depok, Sleman, Yogyakarta | Telp (0274) 887494, 4463083 | Email : rscc_yogya@yahoo.co.id | Website : www.rs-condongcatur.com</p>
                                        </div>
                                    </div>
                                    <hr>
                                  </div>
                                

                                  <div class="isi-surat">
                                    <div class="judul">
                                        <h3>Formulir Permohonan Cuti</h3>
                                    </div>
                                    <div class="isi-container">
                                        <div class="yth">
                                            <p>Kepada Yth. Bagian Kepegawaian & Diklat</p>
                                            <div class="izin">Mohon diberikan izin tidak masuk kerja karena : <strong id="jenisCuti"></strong></div>
                                        </div>
                                        
                                            
                                        
                                        <div class="profil">
                                            <div>Nama : <strong id="nama">{{ $cuti->admin->nama_lengkap }}</strong></div>
                                            <div>Unit : <strong id="unit"></strong></div>
                                            <div>Jabatan : <strong id="jabatan"></strong></div>
                                            <div>No Pokok Karyawan : <strong id="no_pokok"></strong></div>
                                            <div>Alamat : <strong id="alamat"></strong></div>
                                            
                                        </div>
                                        
                                    </div>
                                    <hr>
                                    <div class="isi-alasan-container">
                                        <div class="alasan">
                                            <p>1. Tanggal Mulai</p>
                                            <p>2. Tanggal Selesai</p>
                                            <p>3. Periode Cuti (dalam tahun)</p>
                                            <p>4. Tanggal Pengambilan Cuti</p>
                                            <p>5. Sisa Cuti Tahun Ini yang Masih Berlaku</p>
                                            <p>6. Alasan Cuti Mendadak</p>
                                        </div>
                                        <div class="detail">
                                            <p>: <strong id="tanggal_mulai"></strong></p>
                                            <p>: <strong id="tanggal_selesai"></strong></p>
                                            <p>: <strong id="jumlah_hari"></strong></p>
                                            <p>: <strong id="tanggal_pengajuan"></strong></p>
                                            <p>: <strong id="sisa_cuti"></strong></p>
                                            <p>: <strong id="alasan"></strong></p>
                                        </div>
                                    </div>
                                    
                                    <div class="isi-tgl-pengajuan">
                                        <div class="tgl-setuju">
                                            Tanggal Disetujui : <strong id="tanggal_disetujui"></strong>
                                        </div>
                                        <div class="pemohon">
                                            Tanggal Pengajuan Cuti : <strong id="tglPengajuan"></strong>
                                        </div>
                                        
                                    </div>
                                    
                                    @if ($ttdBase64)
                                        
                                    <div class="isi-ttd">
                                        <div class="ttd-pemohon">
                                            Pemohon,
                                            <hr>
                                            <img src="{{ $ttdBase64 }}" style="width: 100px;height:50px">
                                            <hr>
                                            ({{ Auth::user()->admin->nama_lengkap }})
                                        </div>
                                    </div>
                                    @endif
                                    <hr>
                                    <div class="alasan-penolakan">
                                        Alasan pembatalan/penundaan cuti tahunan / cuti panjang <strong>(diisi oleh atasan / atasan tidak langsung ybs)</strong>
                                        <p id="alasan_penolakan"></p>
                                        ...............................................................................................................
                                    </div>
                                    <hr>
                                  </div>

                                <div class="footer-ttd">
                                    <div class="atasan-kabag">
                                        Kepala Bagian
                                        <hr>
                                        <img src="" id="ttd_kabag" alt="ttd kabag">
                                        <hr>
                                        <div id="nama_kabag"></div>
                                    </div>
                                    <div class="atasan-direktur">
                                        Direktur
                                        <hr>
                                        <img src="" id="ttd_direktur" alt="ttd direktur">
                                        <hr>
                                        <div id="nama_direktur"></div>
                                    </div>
                                </div>

                                </div>
                            </div>
</body>
</html>