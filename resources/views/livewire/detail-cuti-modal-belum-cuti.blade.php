@if($showModalBelumCuti)
<div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Karyawan Yang Belum Ambil Cuti</h5>
                <button wire:click="closeModalBelumCuti" class="close">&times;</button>
            </div>
            <div class="modal-body">
                @if(count($jumlahBelumCuti) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Karyawan</th>
                                <th>Unit</th>  
                                <th>Sisa Cuti Tahunan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jumlahBelumCuti as $index => $cuti)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                @if ($cuti)
                                    <td>{{ $cuti->nama_lengkap ?? '-' }}</td>
                                    <td>{{ $cuti->unit ?? '-' }}</td>
                                    <td>{{ $cuti->sisa_cuti }} hari</td>
                                   
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info">
                    Tidak ada data Karyawan yang belum mengambil cuti.
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button wire:click="closeModalBelumCuti" class="btn btn-secondary">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endif