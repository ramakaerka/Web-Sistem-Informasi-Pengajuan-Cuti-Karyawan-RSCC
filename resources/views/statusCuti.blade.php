@extends('assets.mainDashboard')
@section('content')
<div class="container-status">
   
    <div class="item">
        <div class="container p-3 mt-3">
        <div class="row">
            <div class="col-9">
            <h3><strong>Status Cuti : {{ Auth::user()->name }}</strong></h3>
            </div>
            
        </div>
        
        <div class="row">
            <table class="table-approval">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Jenis Cuti</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Jumlah Hari</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($statusCuti as $item =>$status)
                    <tr>
                        <th scope="row">{{ $statusCuti->firstItem() + $item}}</th>
                    @if ($status->admin)
                        <td>
                            <p>{{ $status->jenis_cuti ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $status->tanggal_mulai ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $status->tanggal_selesai ?? '' }}</p>
                        </td>
                        <td>
                            <p>{{ $status->jumlah_hari ?? '' }}</p>
                        </td>
                        <td>
                            @if ($status->status_manager && $status->status_admin == 'approved:Direktur')
                                <button class="btn-status-approved" disabled>Approved</button>
                            @elseif ($status->status_manager == 'approved:bySistem' && $status->status_admin == 'approved:bySistem')
                                <button class="btn-status-approved" disabled>Approved</button>
                            @elseif ($status->status_manager == 'pending' && $status->status_admin == 'pending')
                                <button class="btn-status-pending" disabled>Pending</button>
                            @elseif ($status->status_manager && $status->status_admin == 'pending')
                                <button class="btn-status-processing" disabled>Processing</button>
                            @elseif ($status->status_manager && $status->status_admin == 'rejected:Direktur')
                            <button class="btn-status-rejected" disabled>Rejected : Direktur</button>
                            @endif
                        </td>
                        <td>
                                <button class="btn btn-primary view-detail" data-id="{{ $status->id }}" type="button" data-bs-toggle="modal" ><i class="fa-solid fa-eye"></i> View</button>
                                <a href="/admin/statusCuti/download_pdf/{{ $status->id }}" class="btn btn-warning">Download PDF</a>
                                <a href="/admin/persetujuan/delete/{{ $status->id }}" class="btn btn-danger">Hapus</a>
                        </td>
                        <div class="modal" id="detailModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h1 class="modal-title fs-5" id="exampleModalLabel">View Status Cuti</h1>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                
                                <div class="modal-body">
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
                                            Tanggal Disetujui : <strong id="tanggal_disetujui"></strong>
                                        </div>
                                        <div class="pemohon">
                                            Tanggal Pengajuan Cuti : <strong id="tglPengajuan"></strong>
                                        </div>
                                        
                                    </div>
                                    
                                    <div class="isi-ttd">
                                        <div class="ttd-pemohon">
                                            Pemohon,
                                            <hr>
                                            <img src="" id="ttd_pemohon" alt="">
                                            <hr>
                                            ({{ Auth::user()->admin->nama_lengkap }})
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
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Close</button>
                                  <a href="/admin/statusCuti/download_pdf/{{ $status->id }}" class="btn btn-warning">Download PDF</a>
                                </div>
                          
                              </div>
                            </div>
                          </div>
                          <script>
                            $(document).ready(function() {
                                $('.view-detail').click(function() {
                                    let cutiId = $(this).data('id');
                            
                                    $.ajax({
                                        url: "{{ route('admin.status') }}?action=detail&id=" + cutiId,
                                        type: "GET",
                                        success: function(response) {
                                            $('#nama').text(response.data.nama_lengkap);
                                            $('#unit').text(response.data.unit);
                                            $('#no_pokok').text(response.data.no_pokok);
                                            $('#jabatan').text(response.data.jabatan);
                                            $('#alamat').text(response.data.alamat);
                                            $('#jenisCuti').text(response.data.jenis_cuti);
                                            $('#tanggal_mulai').text(response.data.tanggal_mulai);
                                            $('#tanggal_selesai').text(response.data.tanggal_selesai);
                                            $('#tanggal_pengajuan').text(response.data.tanggal_pengajuan);
                                            $('#tglPengajuan').text(response.data.tanggal_pengajuan);
                                            $('#tanggal_disetujui').text(response.data.tanggal_disetujui);
                                            $('#alasan').text(response.data.alasan);
                                            $('#jumlah_hari').text(response.data.jumlah_hari);
                                            $('#sisa_cuti').text(response.data.sisa_cuti);
                                            $('#alasan_penolakan').text(response.data.alasan_penolakan);
                                            $('#nama_kabag').text(response.data.nama_kabag);
                                            $('#nama_direktur').text(response.data.nama_direktur);

                                            let ttdPathKabag = "/storage/" + response.data.ttd_kabag; 
                                            $('#ttd_kabag').attr("src", ttdPathKabag).css({
                                                "width": "100px",  
                                                "height": "50px"
                                            });
                                            let ttdPathDirektur = "/storage/" + response.data.ttd_direktur; 
                                            $('#ttd_direktur').attr("src", ttdPathDirektur).css({
                                                "width": "100px",  
                                                "height": "50px"
                                            });
                                            let ttdPathPemohon = "/storage/" + response.data.ttd_pemohon; 
                                            $('#ttd_pemohon').attr("src", ttdPathPemohon).css({
                                                "width": "100px",  
                                                "height": "50px"
                                            });
                                            $('#detailModal').modal('show'); // Tampilkan modal
                                        },
                                        error: function(xhr) {
                                            console.log(xhr);
                                        }
                                    });
                                });
                            });
                            </script>
                          
                    @elseif ($status->manager)
                    <td>
                        <p>{{ $status->jenis_cuti ?? '' }}</p>
                    </td>
                    <td>
                        <p>{{ $status->tanggal_mulai ?? '' }}</p>
                    </td>
                    <td>
                        <p>{{ $status->tanggal_selesai ?? '' }}</p>
                    </td>
                    <td>
                        <p>{{ $status->jumlah_hari ?? '' }}</p>
                    </td>
                    <td>
                        @if ($status->status_admin == 'approved:Admin')
                            <button class="btn-status-approved" disabled>Approved</button>
                        @elseif ($status->status_manager == 'approved:bySistem' && $status->status_admin == 'approved:bySistem')
                            <button class="btn-status-approved" disabled>Approved</button>
                        @elseif ($status->status_manager == 'pending' && $status->status_admin == 'pending')
                            <button class="btn-status-pending" disabled>Pending</button>
                        @elseif (in_array($status->status_manager, $status_manager_apr) && $status->status_admin == 'pending')
                            <button class="btn-status-processing" disabled>Processing</button>
                        @elseif (in_array($status->status_manager, $status_manager_rej) || $status->status_admin == 'pending')
                        <button class="btn-status-rejected" disabled>Rejected</button>
                        @endif
                    </td>
                    <td>
                            <button class="btn btn-primary view-detail" data-id="{{ $status->id }}" type="button" data-bs-toggle="modal"><i class="fa-solid fa-eye"></i> View</button>
                            <a href="/manager/statusCuti/download_pdf/{{ $status->id }}" class="btn btn-warning">Download PDF</a>
                            <a href="/manager/persetujuan/delete/{{ $status->id }}" class="btn btn-danger">Hapus</a>
                    </td>
                    <div class="modal" id="detailModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h1 class="modal-title fs-5" id="exampleModalLabel">View Status Cuti</h1>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            
                            <div class="modal-body">
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
                                        Tanggal Disetujui : <strong id="tanggal_disetujui"></strong>
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
                                    <div id="alasan_penolakan"></div>
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
                        </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Close</button>
                              <a href="/manager/statusCuti/download_pdf/{{ $status->id }}" class="btn btn-warning">Download PDF</a>
                            </div>
                      
                          </div>
                        </div>
                      </div>
                      <script>
                        $(document).ready(function() {
                            $('.view-detail').click(function() {
                                let cutiId = $(this).data('id');
                        
                                $.ajax({
                                    url: "{{ route('manager.status') }}?action=detail&id=" + cutiId,
                                    type: "GET",
                                    success: function(response) {
                                        $('#nama').text(response.data.nama_lengkap);
                                        $('#unit').text(response.data.unit);
                                        $('#no_pokok').text(response.data.no_pokok);
                                        $('#jabatan').text(response.data.jabatan);
                                        $('#alamat').text(response.data.alamat);
                                        $('#jenisCuti').text(response.data.jenis_cuti);
                                        $('#tanggal_mulai').text(response.data.tanggal_mulai);
                                        $('#tanggal_selesai').text(response.data.tanggal_selesai);
                                        $('#tanggal_pengajuan').text(response.data.tanggal_pengajuan);
                                        $('#tglPengajuan').text(response.data.tanggal_pengajuan);
                                        $('#tanggal_disetujui').text(response.data.tanggal_disetujui);
                                        $('#alasan').text(response.data.alasan);
                                        $('#jumlah_hari').text(response.data.jumlah_hari);
                                        $('#sisa_cuti').text(response.data.sisa_cuti);
                                        $('#alasan_penolakan').text(response.data.alasan_penolakan);
                                        $('#nama_atasan').text(response.data.nama_atasan);
                                        $('#nama_kasi').text(response.data.nama_kasi);
                                        $('#nama_kabag').text(response.data.nama_kabag);
                                        $('#nama_admin').text(response.data.nama_admin);
                                        $('#nama_direktur').text(response.data.nama_direktur);
                                        $('#nama_pt').text(response.data.nama_pt);

                                        let ttdPathKabag = "/storage/" + response.data.ttd_kabag; 
                                        $('#ttd_kabag').attr("src", ttdPathKabag).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        let ttdPathKasi = "/storage/" + response.data.ttd_kasi; 
                                        $('#ttd_kasi').attr("src", ttdPathKasi).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        let ttdPathAdmin = "/storage/" + response.data.ttd_admin; 
                                        $('#ttd_admin').attr("src", ttdPathAdmin).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        let ttdPathPemohon = "/storage/" + response.data.ttd_pemohon; 
                                        $('#ttd_pemohon').attr("src", ttdPathPemohon).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        let ttdPathDirektur = "/storage/" + response.data.ttd_direktur; 
                                        $('#ttd_direktur').attr("src", ttdPathDirektur).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        let ttdPathAtasan = "/storage/" + response.data.ttd_atasan; 
                                        $('#ttd_atasan').attr("src", ttdPathAtasan).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        let ttdPathPT = "/storage/" + response.data.ttd_pt; 
                                        $('#ttd_pt').attr("src", ttdPathPT).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        $('#detailModal').modal('show'); 
                                    },
                                    error: function(xhr) {
                                        console.log(xhr);
                                    }
                                });
                            });
                        });
                        </script>
                    @elseif ($status->karyawan)
                    <td>
                        <p>{{ $status->jenis_cuti ?? '' }}</p>
                    </td>
                    <td>
                        <p>{{ $status->tanggal_mulai ?? '' }}</p>
                    </td>
                    <td>
                        <p>{{ $status->tanggal_selesai ?? '' }}</p>
                    </td>
                    <td>
                        <p>{{ $status->jumlah_hari ?? '' }}</p>
                    </td>
                    <td>
                        @if ($status->status_admin == 'approved:Admin')
                            <button class="btn-status-approved" disabled>Approved</button>
                        @elseif ($status->status_manager == 'approved:bySistem' && $status->status_admin == 'approved:bySistem')
                                <button class="btn-status-approved" disabled>Approved</button>
                        @elseif ($status->status_manager == 'pending' && $status->status_admin == 'pending')
                            <button class="btn-status-pending" disabled>Pending</button>
                        @elseif (in_array($status->status_manager , $status_manager_apr) && $status->status_admin == 'pending')
                            <button class="btn-status-processing" disabled>Processing</button>
                        @elseif (in_array($status->status_manager, $status_manager_rej) || $status->status_admin == 'rejected:Admin')
                        <button class="btn-status-rejected" disabled>Rejected</button>
                        @endif
                    </td>
                    <td>
                            <button class="btn btn-primary view-detail" data-id="{{ $status->id }}" type="button" data-bs-toggle="modal" ><i class="fa-solid fa-eye"></i> View</button>
                            <a href="/karyawan/statusCuti/download_pdf/{{ $status->id }}" class="btn btn-warning">Download PDF</a>
                            <a href="/karyawan/persetujuan/delete/{{ $status->id }}" class="btn btn-danger">Hapus</a>
                    </td>
                    <div class="modal" id="detailModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h1 class="modal-title fs-5" id="exampleModalLabel">View Status Cuti</h1>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            
                            <div class="modal-body">
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
                                        ({{ Auth::user()->karyawan->nama_lengkap }})
                                    </div>
                                </div>
                                <hr>
                                <div class="alasan-penolakan">
                                    Alasan pembatalan/penundaan cuti tahunan / cuti panjang <strong>(diisi oleh atasan / atasan tidak langsung ybs)</strong>
                                    <div id="alasan_penolakan"></div>
                                    ...............................................................................................................
                                </div>
                                <hr>
                              </div>

                              @if ($aliasJabatan == 'karyawan')

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
                              @endif 
    
                            </div>
                        </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Close</button>
                              <a href="/karyawan/statusCuti/download_pdf/{{ $status->id }}" class="btn btn-warning">Download PDF</a>
                            </div>
                      
                          </div>
                        </div>
                      </div>
                      
                      <script>
                        $(document).ready(function() {
                            $('.view-detail').click(function() {
                                let cutiId = $(this).data('id');
                        
                                $.ajax({
                                    url: "{{ route('karyawan.status') }}?action=detail&id=" + cutiId,
                                    type: "GET",
                                    success: function(response) {
                                        $('#nama').text(response.data.nama_lengkap);
                                        $('#unit').text(response.data.unit);
                                        $('#no_pokok').text(response.data.no_pokok);
                                        $('#jabatan').text(response.data.jabatan);
                                        $('#alamat').text(response.data.alamat);
                                        $('#jenisCuti').text(response.data.jenis_cuti);
                                        $('#tanggal_mulai').text(response.data.tanggal_mulai);
                                        $('#tanggal_selesai').text(response.data.tanggal_selesai);
                                        $('#tanggal_pengajuan').text(response.data.tanggal_pengajuan);
                                        $('#tglPengajuan').text(response.data.tanggal_pengajuan);
                                        $('#tglDisetujui').text(response.data.tanggal_disetujui);
                                        $('#alasan').text(response.data.alasan);
                                        $('#jumlah_hari').text(response.data.jumlah_hari);
                                        $('#sisa_cuti').text(response.data.sisa_cuti);
                                        $('#alasan_penolakan').text(response.data.alasan_penolakan);
                                        $('#nama_atasan').text(response.data.nama_atasan);
                                        $('#nama_kasi').text(response.data.nama_kasi);
                                        $('#nama_kabag').text(response.data.nama_kabag);
                                        $('#nama_admin').text(response.data.nama_admin);

                                        let ttdPathAtasan = "/storage/" + response.data.ttd_atasan; 
                                        $('#ttd_atasan').attr("src", ttdPathAtasan).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });

                                        let ttdPathKabag = "/storage/" + response.data.ttd_kabag; 
                                        $('#ttd_kabag').attr("src", ttdPathKabag).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        let ttdPathKasi = "/storage/" + response.data.ttd_kasi; 
                                        $('#ttd_kasi').attr("src", ttdPathKasi).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        let ttdPathAdmin = "/storage/" + response.data.ttd_admin; 
                                        $('#ttd_admin').attr("src", ttdPathAdmin).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        let ttdPathPemohon = "/storage/" + response.data.ttd_pemohon; 
                                        $('#ttd_pemohon').attr("src", ttdPathPemohon).css({
                                            "width": "100px",  
                                            "height": "50px"
                                        });
                                        

                                        $('#detailModal').modal('show'); 
                                    },
                                    error: function(xhr) {
                                        console.log(xhr);
                                    }
                                });
                            });
                        });
                        </script>
                    @endif
                    </tr>
                        
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
  </div>
                    @if ($role === 'karyawan')    
                      <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            if (!window.Echo){
                                console.error('Echo not initialized');
                                return;
                            }
                            console.log('Echo initialized');
                            const karyawanId = {{ auth()->user()->karyawan->id ?? 'null' }};
                            const userId = {{ auth()->id() ?? 'null' }};
                            if (!userId) {
                                console.error('Error: User not authenticated');
                            return;
                            }
                            if (!karyawanId) {
                                console.error('Error: User not authenticated');
                            return;
                            }

                            window.Echo.channel(`karyawan.proses_cuti.${karyawanId}`)
                                .listen('CutiDisetujui', (data) => {
                                    console.log('Cuti disetujui:', data);
                                    toastr.success(`Cuti Anda disetujui! Mulai: ${data.tanggal_mulai}`);
                                })
                                .error((err) => {
                                    console.error('Channel error : ', err);
                                });
                        });
                      </script>
                    @elseif ($role === 'manager')
                      <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            if (!window.Echo){
                                console.error('Echo not initialized');
                                return;
                            }
                            console.log('Echo initialized');
                            const managerId = {{ auth()->user()->manager->id ?? 'null' }};
                            const userId = {{ auth()->id() ?? 'null' }};
                            if (!userId) {
                                console.error('Error: User not authenticated');
                            return;
                            }
                            if (!managerId) {
                                console.error('Error: Manager not authenticated');
                            return;
                            }

                            window.Echo.channel(`manager.proses_cuti.${managerId}`)
                                .listen('CutiDisetujui', (data) => {
                                    console.log('Cuti disetujui:', data);
                                    toastr.success(`Cuti Anda disetujui! Mulai: ${data.tanggal_mulai}`);
                                })
                                .error((err) => {
                                    console.error('Channel error : ', err);
                                });
                        });
                      </script>
                    @elseif ($role === 'admin')
                      <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            if (!window.Echo){
                                console.error('Echo not initialized');
                                return;
                            }
                            console.log('Echo initialized');
                            const adminId = {{ auth()->user()->admin->id ?? 'null' }};
                            const userId = {{ auth()->id() ?? 'null' }};
                            if (!userId) {
                                console.error('Error: User not authenticated');
                            return;
                            }
                            if (!adminId) {
                                console.error('Error: User not authenticated');
                            return;
                            }

                            window.Echo.channel(`admin.proses_cuti.${adminId}`)
                                .listen('CutiDisetujui', (data) => {
                                    console.log('Cuti disetujui:', data);
                                    toastr.success(`Cuti Anda disetujui! Mulai: ${data.tanggal_mulai}`);
                                })
                                .error((err) => {
                                    console.error('Channel error : ', err);
                                });
                        });
                      </script>
                    @endif
  
@endsection