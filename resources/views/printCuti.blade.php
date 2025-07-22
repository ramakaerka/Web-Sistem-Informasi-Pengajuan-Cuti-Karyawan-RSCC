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
                  <div>Nama : <strong id="nama"></strong></div>
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
                  Tanggal Pengajuan Cuti : <strong id="tglPengajuan"></strong>
                  <hr>
                  Tanggal Disetujui : <strong id="tglDisetujui"></strong>
              </div>
              <div class="pemohon">
              </div>
              
          </div>
          
          <div class="isi-ttd">
              <div class="ttd-pemohon">
                  Pemohon,
                  <hr>
                  <img id="ttd_pemohon" src="" alt="Tanda Tangan Pemohon"></img>
                  <hr>
                  ({{ Auth::user()->name }})
              </div>
          </div>
          <hr>
          <div class="alasan-penolakan">
              Alasan pembatalan/penundaan cuti tahunan / cuti panjang <strong>(diisi oleh atasan / atasan tidak langsung ybs)</strong>
              <p id="alasan_penolakan"></p>
              ...............................................................................................................
          </div>
          <hr>
        </div>

        @if ($aliasJabatan == 'Kepala Unit')
          <div class="footer-ttd">
              <div class="atasan-kasi">
                  Kepala Seksi
                  <hr>
                  <img id="ttd_kasi" src="" alt="Tanda Tangan Kasi">
                  <hr>
                  <div id="nama_kasi"></div>
              </div>
              <div class="atasan-kabag">
                  Kepala Bagian
                  <hr>
                  <img id="ttd_kabag" src="" alt="Tanda Tangan Kabag">
                  <hr>
                  <div id="nama_kabag"></div>
              </div>
              <div class="hr">
                  HRD
                  <hr>
                  <img id="ttd_admin" src="" alt="Tanda Tangan Admin">
                  <hr>
                  <div id="nama_admin"></div>
              </div>
          </div>
        @elseif ($aliasJabatan == 'Karyawan')
          <div class="footer-ttd">
              <div class="atasan-langsung">
                  Kepala Unit
                  <hr>
                  <img id="ttd_atasan" src="" alt="Tanda Tangan Atasan">
                  <hr>
                  <div id="nama_atasan"></div>
              </div>
              <div class="atasan-kasi">
                  Kepala Seksi
                  <hr>
                  <img id="ttd_kasi" src="" alt="Tanda Tangan Kasi">
                  <hr>
                  <div id="nama_kasi"></div>
              </div>
              <div class="atasan-kabag">
                  Kepala Bagian
                  <hr>
                  <img id="ttd_kabag" src="" alt="Tanda Tangan Kabag">
                  <hr>
                  <div id="nama_kabag"></div>
              </div>
              <div class="hr">
                  HRD
                  <hr>
                  <img id="ttd_admin" src="" alt="Tanda Tangan Admin">
                  <hr>
                  <div id="nama_admin"></div>
              </div>
          </div>
        @elseif ($aliasJabatan == 'Kepala Seksi')
          <div class="footer-ttd">
              <div class="atasan-kabag">
                  Kepala Bagian
                  <hr>
                  <img id="ttd_kabag" src="" alt="Tanda Tangan Kabag">
                  <hr>
                  <div id="nama_kabag"></div>
              </div>
              <div class="hr">
                  HRD
                  <hr>
                  <img id="ttd_admin" src="" alt="Tanda Tangan Admin">
                  <hr>
                  <div id="nama_admin"></div>
              </div>
          </div>
        @elseif ($aliasJabatan == 'Kepala Bagian')
          <div class="footer-ttd">
              <div class="atasan-direktur">
                  Kepala Bagian
                  <hr>
                  <img id="ttd_direktur" src="" alt="Tanda Tangan Direktur">
                  <hr>
                  <div id="nama_direktur"></div>
              </div>
              <div class="hr">
                  HRD
                  <hr>
                  <img id="ttd_admin" src="" alt="Tanda Tangan Admin">
                  <hr>
                  <div id="nama_admin"></div>
              </div>
          </div>
        @elseif ($aliasJabatan == 'Kepala Bagian')
          <div class="footer-ttd">
              <div class="atasan-direktur">
                  Kepala Bagian
                  <hr>
                  <img id="ttd_direktur" src="" alt="Tanda Tangan Direktur">
                  <hr>
                  <div id="nama_direktur"></div>
              </div>
              <div class="hr">
                  HRD
                  <hr>
                  <img id="ttd_admin" src="" alt="Tanda Tangan Admin">
                  <hr>
                  <div id="nama_admin"></div>
              </div>
          </div>
        @elseif ($aliasJabatan == 'HRD' && 'Kepala Unit Teknologi Informasi')
          <div class="footer-ttd">
              <div class="atasan-direktur">
                  Kepala Bagian
                  <hr>
                  <img id="ttd_direktur" src="" alt="Tanda Tangan Direktur">
                  <hr>
                  <div id="nama_direktur"></div>
              </div>
          </div>
        @elseif ($aliasJabatan == 'Direktur')
          <div class="footer-ttd">
              <div class="atasan-pt">
                  PT
                  <hr>
                  <img id="ttd_pt" src="" alt="Tanda Tangan PT">
                  <hr>
                  <div id="nama_pt"></div>
              </div>
          </div>
        @endif

      </div>
</body>
</html>