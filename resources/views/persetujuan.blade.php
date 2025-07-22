@extends('assets.mainDashboard')
@section('content')
    
<div class="container-fluid p-1 mt-3" style="width: 80%">
    <div class="row">
        <h3><strong>Cuti Pending</strong></h3>
    </div>

      @if ($role =='admin')
      <form method="GET" action="/admin/persetujuan" class="mb-3">
      @elseif ($role =='manager')
        <form method="GET" action="/manager/persetujuan" class="mb-3">
      @endif

      @if(in_array($jabatanUser, $filterDataManager))
          
        <div class="row g-3 width-100">
            <div class="col-md-2">
              <label for="">Unit</label>
                <select name="unit" class="form-select">
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit }}" {{ request('unit') == $unit ? 'selected' : '' }}>
                            {{ $unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
              <label for="">Jenis Cuti</label>
                <select name="jenis_cuti" class="form-select">
                    <option value="">Semua Cuti</option>
                    <option value="cuti_melahirkan">Cuti Melahirkan (3 bulan)</option>
                      <option value="cuti_sakit">Cuti Sakit (2 hari)</option>
                      <option value="cuti_menikah">Cuti Menikah (3 hari)</option>
                      <option value="cuti_panjang">Cuti Panjang (6 hari)</option>
                      <option value="cuti_tahunan">Cuti Tahunan (12 hari)</option>
                      <option value="cuti_kelahiran_anak">Cuti Kelahiran/Khitanan/Baptis Anak (2 hari)</option>
                      <option value="cuti_pernikahan_anak">Cuti Pernikahan Anak (2 hari)</option>
                      <option value="cuti_mati_sedarah">Cuti Kematian Suami/Istri/Anak/Saudara (2 hari)</option>
                      <option value="cuti_mati_klg_serumah">Cuti Kematian Anggota Keluarga Serumah (1 hari)</option>
                      <option value="cuti_mati_ortu">Cuti Kematian Orang Tua/Mertua (2 hari)</option>
                      <option value="cuti_lainnya">Lainnya (lihat catatan)</option>
                </select>
            </div>

            <div class="col-md-2">
              <label for="">Status</label>
                <select name="status_manager" class="form-select">
                    <option value="pending">Pending</option>
                    <option value="bawahan_langsung" selected>Cuti Pending Anda</option>
                    <option value="approved:Kepala Unit">Approved by Level 1 (Kepala Unit)</option>
                    <option value="approved:Kepala Seksi">Approved by Level 2 (Kepala Seksi)</option>
                    <option value="approved:Kepala Bagian">Approved by Level 3 (Kepala Bagian)</option>
                    <option value="approved:Direktur">Approved by Direktur</option>
                    <option value="approved:Admin">Approved by Admin</option>
                </select>
            </div>
            
            <div class="col-md-2">
              <label for="">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control" 
                      value="{{ request('tanggal_mulai') }}" placeholder="Dari Tanggal">
            </div>
            
            <div class="col-md-2">
              <label for="">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" 
                      value="{{ request('tanggal_selesai') }}" placeholder="Sampai Tanggal">
            </div>
            
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>
      @else
      @endif
      </form>
     
    <div class="row">
        <table class="table-approval">
            <thead>
              <tr>
                <th scope="col">No</th>
                <th scope="col">
                  Karyawan
                </th>
                <th scope="col">
                  Unit
                </th>
                <th scope="col">
                  <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'tanggal_mulai', 'sort_dir' => request('sort_dir') === 'asc' ? 'desc' : 'asc']) }}" style="color: black">
                    Tanggal Cuti
                    @if(request('sort_by') == 'tanggal_mulai')
                        <i class="fas fa-sort-{{ request('sort_dir') === 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                  </a>
                </th>
                <th scope="col">
                  <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'jenis_cuti', 'sort_dir' => request('sort_dir') === 'asc' ? 'desc' : 'asc']) }}" style="color: black">
                        Jenis Cuti
                        @if(request('sort_by') == 'jenis_cuti')
                            <i class="fas fa-sort-{{ request('sort_dir') === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                      </a>
                </th>
                <th scope="col">Alasan</th>
                <th scope="col">Status</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
                @foreach ($pendingCuti as $item => $c)
                <tr>
                  <th scope="row">{{ $pendingCuti->firstItem() + $item }}</th>
                  <td>
                      @if ($c->karyawan)
                      <p>Nama : {{ $c->karyawan->nama_lengkap ?? ''}}</p>
                      <p>NPK : {{ $c->karyawan->no_pokok ?? ''}}</p>
                      @elseif ($c->manager)
                      <p>Nama : {{ $c->manager->nama_lengkap }}</p>
                      <p>NPK : {{ $c->manager->no_pokok ?? ''}}</p>
                      @elseif($c->admin)
                      <p>Nama : {{ $c->admin->nama_lengkap ?? ''}}</p>
                      <p>NPK : {{ $c->admin->no_pokok ?? '' }}</p>
                      @endif
                  </td>
                  <td>
                    @if ($c->karyawan)
                    <p>Unit : {{ $c->karyawan->unit ?? ''}}</p>
                    <p>Jabatan : {{ $c->karyawan->jabatan ?? ''}}</p>
                    @elseif($c->manager)
                    <p>Unit : {{ $c->manager->unit ?? ''}}</p>
                    <p>Jabatan : {{ $c->manager->jabatan ?? ''}}</p>
                    @elseif($c->admin)
                      <p>Unit : {{ $c->admin->unit ?? ''}}</p>
                      <p>Jabatan : {{ $c->admin->jabatan ?? ''}}</p>
                    @endif
                  </td>
                  <td>
                      <p>Mulai : <Strong style="color: green">{{ $c->tanggal_mulai }}</Strong></p>
                      <p>Akhir : <strong style="color: rgb(221, 42, 42)">{{ $c->tanggal_selesai }}</strong></p>
                  </td>
                  <td>
                      <p>{{ $c->jenis_cuti }} : <strong>{{ $c->jumlah_hari }} hari</strong></p>
                      
                  </td>
                  <td>
                    <p>{{ $c->alasan }}</p>
                  </td>
                  <td>
                      <p>Manager : {{ $c->status_manager }}</p>
                      <p>Admin : {{ $c->status_admin }} </p>
                  </td>
                  <td>
                    
                      @if ($c->surat_keterangan)
                        @if ($role === 'manager')
                        <a href="/manager/persetujuan/download_suket/{{ $c->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                        @elseif ($role === 'admin')
                        <a href="/admin/laporan/download_suket/{{ $c->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                        @endif
                      @endif
                      @if ($c->jenis_cuti == 'cuti_sakit')
                      <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#modalEditCutiSakit_{{ $role }}{{ $c->id }}">Edit</button>
                      @elseif ($c->jenis_cuti == 'cuti_lainnya')
                      <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#modalEditCutiSakit_{{ $role }}{{ $c->id }}">Edit</button>
                      @else
                      <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#modalEdit_{{ $role }}{{ $c->id }}">Edit</button>
                      @endif
                      @if ($role == 'admin')
                      <a href="/admin/persetujuan/delete/{{ $c->id }}" class="btn btn-danger">Hapus</a>
                      @elseif ($role == 'manager')
                      <a href="/manager/persetujuan/delete/{{ $c->id }}" class="btn btn-danger">Hapus</a>
                      @elseif ($role == 'karyawan')
                      <a href="/karyawan/persetujuan/delete/{{ $c->id }}" class="btn btn-danger">Hapus</a>
                      @endif
                    
                  </td>
                </tr>
                {{-- Modal Edit --}}
                @if ($role === 'manager')
            <div class="modal fade" id="modalEdit_{{ $role }}{{ $c->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Status</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form action="/manager/persetujuan/{{ $c->id }}/proses" method="POST">
                    @csrf
                  <div class="modal-body">
                    <h4>Approve Cuti?</h4>
                    <select class="form-select" name="status_manager" id="status_manager{{ $c->id }}" required>
                      <option value="pending">Pending</option>
                      <option value="approved">Approve</option>
                      <option value="rejected">Reject</option>
                    </select>
                    <div class="mb-3" id="alasan_penolakan{{ $c->id }}" style="display: none;">
                      <span>Alasan penolakan</span>
                      <input class="form-control" type="text" name="alasan_penolakan" value="{{ $c->alasan_penolakan }}">
                    </div>
                  </div>
                  <div class="modal-footer">
                    @if ($c->surat_keterangan)
                        <a href="/manager/persetujuan/download_suket/{{ $c->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                      @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                </form>
                </div>
              </div>
            </div>
            <div class="modal fade" id="modalEditCutiSakit_{{ $role }}{{ $c->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Status</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form action="/manager/persetujuan/{{ $c->id }}/proses" method="POST">
                    @csrf
                  <div class="modal-body">
                    <h4>Lampiran Surat Keterangan Sakit Valid?</h4>
                    <select class="form-select" name="validitas_suket" id="validitas_suket{{ $c->id }}" required>
                      <option value="valid">Valid</option>
                      <option value="tdk_valid">Tidak Valid</option>
                    </select>
                    <div class="mb-3" id="alasan_penolakan{{ $c->id }}" style="display: none;">
                      <span>Alasan Tidak Valid</span>
                      <input class="form-control" type="text" name="alasan_penolakan" value="{{ $c->alasan_penolakan }}">
                    </div>
                  </div>
                  <div class="modal-footer">
                    @if ($c->surat_keterangan)
                        <a href="/manager/persetujuan/download_suket/{{ $c->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                      @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                </form>
                </div>
              </div>
            </div>
            @elseif ($role === 'admin')
            <div class="modal fade" id="modalEdit_{{ $role }}{{ $c->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Status</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form action="/admin/persetujuan/{{ $c->id }}/proses" method="POST">
                    @csrf
                  <div class="modal-body">
                    <h4>Approve Cuti?</h4>
                    <select class="form-select" name="status_admin" id="status_admin{{ $c->id }}" required>
                      <option value="pending">Pending</option>
                      <option value="approved">Approve</option>
                      <option value="rejected">Reject</option>
                    </select>
                    <div class="mb-3" id="alasan_penolakan{{ $c->id }}" style="display: none;">
                      <span>Alasan penolakan</span>
                      <input class="form-control" type="text" name="alasan_penolakan" value="{{ $c->alasan_penolakan }}">
                    </div>
                  </div>
                  <div class="modal-footer">
                    @if ($c->surat_keterangan)
                        <a href="/admin/laporan/download_suket/{{ $c->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                    @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                </form>
                </div>
              </div>
            </div>
            <div class="modal fade" id="modalEditCutiSakit_{{ $role }}{{ $c->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Status</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form action="/admin/persetujuan/{{ $c->id }}/proses" method="POST">
                    @csrf
                  <div class="modal-body">
                    <h4>Lampiran Surat Keterangan Sakit Valid?</h4>
                    <select class="form-select" name="validitas_suket" id="validitas_suket{{ $c->id }}" required>
                      <option value="valid">Valid</option>
                      <option value="tdk_valid">Tidak Valid</option>
                    </select>
                    <div class="mb-3" id="alasan_penolakan{{ $c->id }}" style="display: none;">
                      <span>Alasan Tidak Valid</span>
                      <input class="form-control" type="text" name="alasan_penolakan" value="{{ $c->alasan_penolakan }}">
                    </div>
                  </div>
                  <div class="modal-footer">
                    @if ($c->surat_keterangan)
                        <a href="/admin/persetujuan/download_suket/{{ $c->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                      @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                  
                </form>
                </div>
              </div>
            </div>
            @endif
            @endforeach
            
          </tbody>
        </table>
        <div class="d-flex flex-column justify-content-center">
            {{ $pendingCuti->links('pagination::bootstrap-5', ['showInfo' => false]) }}
        </div>
      </div>
    </div>
      
      
      
      
      
      @include('persetujuanApproved',compact('cutiApproved','role'))
      @include('persetujuanRejected', compact('cutiRejected','role'))

@if($role === 'manager')
<script>
  document.addEventListener('DOMContentLoaded', function() {
      @foreach ($pendingCuti as $c)
          const statusManager{{ $c->id }} = document.getElementById('status_manager{{ $c->id }}');
          const alasanPenolakan{{ $c->id }} = document.getElementById('alasan_penolakan{{ $c->id }}');

          statusManager{{ $c->id }}.addEventListener('change', function() {
              if (statusManager{{ $c->id }}.value === 'rejected') {
                  alasanPenolakan{{ $c->id }}.style.display = 'block';
              } else {
                  alasanPenolakan{{ $c->id }}.style.display = 'none';
              }
          });
      @endforeach
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      @foreach ($cutiApproved as $c)
          const statusManager{{ $c->id }} = document.getElementById('status_manager{{ $c->id }}');
          const alasanPenolakan{{ $c->id }} = document.getElementById('alasan_penolakan{{ $c->id }}');

          statusManager{{ $c->id }}.addEventListener('change', function() {
              if (statusManager{{ $c->id }}.value === 'rejected') {
                  alasanPenolakan{{ $c->id }}.style.display = 'block';
              } else {
                  alasanPenolakan{{ $c->id }}.style.display = 'none';
              }
          });
      @endforeach
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
      @foreach ($cutiRejected as $c)
          const statusManager{{ $c->id }} = document.getElementById('status_manager{{ $c->id }}');
          const alasanPenolakan{{ $c->id }} = document.getElementById('alasan_penolakan{{ $c->id }}');

          statusManager{{ $c->id }}.addEventListener('change', function() {
              if (statusManager{{ $c->id }}.value === 'rejected') {
                  alasanPenolakan{{ $c->id }}.style.display = 'block';
              } else {
                  alasanPenolakan{{ $c->id }}.style.display = 'none';
              }
          });
      @endforeach
  });
</script>

@elseif ($role ==='admin')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        @foreach ($pendingCuti as $c)
            const statusAdmin{{ $c->id }} = document.getElementById('status_admin{{ $c->id }}');
            const alasanPenolakan{{ $c->id }} = document.getElementById('alasan_penolakan{{ $c->id }}');
  
            statusAdmin{{ $c->id }}.addEventListener('change', function() {
                if (statusAdmin{{ $c->id }}.value === 'rejected') {
                    alasanPenolakan{{ $c->id }}.style.display = 'block';
                } else {
                    alasanPenolakan{{ $c->id }}.style.display = 'none';
                }
            });

            const validitasSuket{{ $c->id }} = document.getElementById('validitas_suket{{ $c->id }}');
            validitasSuket{{ $c->id }}.addEventListener('change', function() {
                if (validitasSuket{{ $c->id }}.value === 'tdk_valid') {
                    alasanPenolakan{{ $c->id }}.style.display = 'block';
                } else {
                    alasanPenolakan{{ $c->id }}.style.display = 'none';
                }
            });
        @endforeach
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        @foreach ($cutiApproved as $c)
            const statusAdmin{{ $c->id }} = document.getElementById('status_admin{{ $c->id }}');
            const alasanPenolakan{{ $c->id }} = document.getElementById('alasan_penolakan{{ $c->id }}');
  
            statusAdmin{{ $c->id }}.addEventListener('change', function() {
                if (statusAdmin{{ $c->id }}.value === 'rejected') {
                    alasanPenolakan{{ $c->id }}.style.display = 'block';
                } else {
                    alasanPenolakan{{ $c->id }}.style.display = 'none';
                }
            });
        @endforeach
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        @foreach ($cutiRejected as $c)
            const statusAdmin{{ $c->id }} = document.getElementById('status_admin{{ $c->id }}');
            const alasanPenolakan{{ $c->id }} = document.getElementById('alasan_penolakan{{ $c->id }}');
  
            statusAdmin{{ $c->id }}.addEventListener('change', function() {
                if (statusAdmin{{ $c->id }}.value === 'rejected') {
                    alasanPenolakan{{ $c->id }}.style.display = 'block';
                } else {
                    alasanPenolakan{{ $c->id }}.style.display = 'none';
                }
            });
        @endforeach
    });
  </script>
@endif
  <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.Echo) {
            console.error('Echo not initialized!');
            return;
        }
        console.log('Echo initialized');

        window.Echo.channel('admin.proses_cuti')
            .listen('CutiDiajukan', (data) => {
                toastr.success(`Pengajuan dari: ${data.nama_lengkap}`);
                console.log('Pengajuan dari :', data);
            })
            .error((err) => {
                console.error('Channel error:', err);
            });
    });
  </script>
  
@endsection