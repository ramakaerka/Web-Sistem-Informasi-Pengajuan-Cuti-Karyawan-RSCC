@if($showModal)
<div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Cuti Approved</h5>
                <button wire:click="closeModal" class="close">&times;</button>
            </div>
            <div class="modal-body">
                @if(count($jumlahApprovedDetail) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Karyawan</th>
                                <th>Unit</th>
                                <th>Tanggal Mulai</th>
                                <th>Durasi</th>
                                <th>Sisa Cuti Tahunan</th>
                                <th>Alasan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jumlahApprovedDetail as $index => $cuti)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                @if ($cuti->karyawan)
                                    <td>{{ $cuti->karyawan->nama_lengkap ?? '-' }}</td>
                                    <td>{{ $cuti->karyawan->unit ?? '-' }}</td>
                                    <td>{{ $cuti->tanggal_mulai ?? '' }}</td>
                                    <td>{{ $cuti->jumlah_hari }} hari</td>
                                    <td>{{ $cuti->karyawan->sisa_cuti + $cuti->karyawan->sisa_cuti_sebelumnya }} hari</td>
                                    <td>{{ $cuti->alasan }}</td>
                                    <td>
                                        @if ($cuti->surat_keterangan)
                                            <a href="/admin/laporan/download_suket/{{ $cuti->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                                        @endif
                                        <button wire:click="downloadPdf({{ $cuti->id }})" 
                                        class="btn btn-sm btn-primary"><i class="fas fa-download"></i> PDF
                                        </button>
                                    </td>
                                @elseif ($cuti->manager)
                                <td>{{ $cuti->manager->nama_lengkap ?? '-' }}</td>
                                    <td>{{ $cuti->manager->unit ?? '-' }}</td>
                                    <td>{{ $cuti->tanggal_mulai ?? '' }}</td>
                                    <td>{{ $cuti->jumlah_hari }} hari</td>
                                    <td>{{ $cuti->manager->sisa_cuti + $cuti->manager->sisa_cuti_sebelumnya }} hari</td>
                                    <td>{{ $cuti->alasan }}</td>
                                    <td>
                                        @if ($cuti->surat_keterangan)
                                            <a href="/admin/laporan/download_suket/{{ $cuti->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                                        @endif
                                        <button wire:click="downloadPdf({{ $cuti->id }})" 
                                        class="btn btn-sm btn-primary"><i class="fas fa-download"></i> PDF
                                        </button>
                                    </td>
                                @elseif ($cuti->admin)
                                <td>{{ $cuti->admin->nama_lengkap ?? '-' }}</td>
                                    <td>{{ $cuti->admin->unit ?? '-' }}</td>
                                    <td>{{ $cuti->tanggal_mulai ?? '' }}</td>
                                    <td>{{ $cuti->jumlah_hari }} hari</td>
                                    <td>{{ $cuti->admin->sisa_cuti + $cuti->admin->sisa_cuti_sebelumnya }} hari</td>
                                    <td>{{ $cuti->alasan }}</td>
                                    <td>
                                        @if ($cuti->surat_keterangan)
                                            <a href="/admin/laporan/download_suket/{{ $cuti->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                                        @endif
                                        <button wire:click="downloadPdf({{ $cuti->id }})" 
                                        class="btn btn-sm btn-primary"><i class="fas fa-download"></i> PDF
                                        </button>
                                    </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info">
                    Tidak ada data cuti approved dengan filter saat ini.
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button wire:click="closeModal" class="btn btn-secondary">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endif