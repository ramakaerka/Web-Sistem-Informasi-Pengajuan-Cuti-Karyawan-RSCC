@foreach ($approvedByKasiKeperawatan as $item => $c)
                <tr>
                  <th scope="row">{{ $approvedByKasiKeperawatan->firstItem() + $item }}</th>
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
                      <p>Mulai : <strong>{{ $c->tanggal_mulai }}</strong></p>
                      <p>Akhir : <strong>{{ $c->tanggal_selesai }}</strong></p>
                  </td>
                  <td>
                      <p>{{ $c->jenis_cuti }} : <strong>{{ $c->jumlah_hari }} hari</strong></p>
                  </td>
                  <td>
                      <p>Manager : {{ $c->status_manager }}</p>
                      <p>Admin : {{ $c->status_admin }} </p>
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#modalEdit_{{ $role }}{{ $c->id }}">Edit</button>
                    </div>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                </form>
                </div>
              </div>
            </div>
            @endif
@endforeach