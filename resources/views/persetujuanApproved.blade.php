<div class="container-fluid p-1 mt-3" style="width: 80%">
    <div class="row">
        <h3><strong>Cuti Approved</strong></h3>
    </div>
    
    <div class="row">
        <table class="table-approval">
            <thead>
              <tr>
                <th scope="col">Id</th>
                <th scope="col">Karyawan</th>
                <th scope="col">Unit</th>
                <th scope="col">Cuti</th>
                <th scope="col">Jumlah Cuti</th>
                <th scope="col">Alasan</th>
                <th scope="col">Status</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($cutiApproved as $item => $c)
              <tr>
                <th scope="row">{{ $cutiApproved->firstItem() + $item }}</th>
                <td>
                    @if ($c->karyawan)
                    <p>Nama : {{ $c->karyawan->nama_lengkap ?? ''}}</p>
                    <p>NPK : {{ $c->karyawan->no_pokok ?? ''}}</p>
                    @elseif ($c->manager)
                    <p>Nama : {{ $c->manager->nama_lengkap ?? ''}}</p>
                    <p>NPK: {{ $c->manager->no_pokok ?? ''}}</p>
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
                    <p>Mulai : <strong style="color: green">{{ $c->tanggal_mulai }}</strong></p>
                    <p>Akhir : <strong style="color: rgb(212, 33, 33)">{{ $c->tanggal_selesai }}</strong></p>
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
                      @if ($role === 'admin')
                        <a href="/admin/laporan/download_suket/{{ $c->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                        @elseif ($role === 'manager')
                        <a href="/manager/persetujuan/download_suket/{{ $c->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                        @endif
                        @endif
                      @if ($role =='admin')
                      <a href="/admin/persetujuan/delete/{{ $c->id }}" class="btn btn-danger">Hapus</a>
                      @elseif ($role =='manager')
                      <a href="/manager/persetujuan/delete/{{ $c->id }}" class="btn btn-danger">Hapus</a>
                      @endif
                    <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#modalEdit_{{ $role }}{{ $c->id }}">Edit</button>
                  
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
          @elseif($role === 'admin') 
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
          @endif
              @endforeach
            </tbody>
          </table>
          
          <div class="d-flex flex-column justify-content-center">
            {{ $cutiApproved->links('pagination::bootstrap-5', ['showInfo' => false]) }}
        </div>
        </div>
      
</div>

