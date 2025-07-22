@extends('assets.mainDashboard')
@section('content')
    <div class="container p-3 mt-3">
        <div class="row">
            <div class="col-8">
                <h3><strong>Formulir Pengajuan Cuti</strong></h3>
            </div>
            <div class="col-4">
                <div class="box-sisacuti">
                    @if ($role == 'manager')
                    <p>Sisa Cuti : {{ $user->manager->sisa_cuti + $user->manager->sisa_cuti_sebelumnya }}</p>
                    @elseif ($role == 'admin')
                    <p>Sisa Cuti : {{ $user->admin->sisa_cuti + $user->admin->sisa_cuti_sebelumnya}}</p>
                    @elseif ($role == 'karyawan')
                    <p>Sisa Cuti : {{ $user->karyawan->sisa_cuti + $user->karyawan->sisa_cuti_sebelumnya}}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error )
                    <li>
                        {{ $error }}
                    </li>
                    @endforeach
                </ul>
            </div>
                
            @endif
            <form action="/{{ $role }}/pengajuan/store" method="post" class="form border" enctype="multipart/form-data">
                @csrf
                <div class="mb-3 mt-3">
                    <label for="nama" class="form-label"><strong>Jenis Cuti</strong></label>
                    <select class="form-select" name="jenis_cuti" id="jenis_cuti" required>
                        <option value="cuti_melahirkan">Cuti Melahirkan (3 bulan)</option>
                        <option value="cuti_menikah">Cuti Menikah (3 hari)</option>
                        <option value="cuti_panjang">Cuti Panjang (6 hari)</option>
                        <option value="cuti_tahunan">Cuti Tahunan (12 hari)</option>
                        <option value="cuti_kelahiran_anak">Cuti Kelahiran/Khitanan/Baptis Anak (2 hari)</option>
                        <option value="cuti_pernikahan_anak">Cuti Pernikahan Anak (2 hari)</option>
                        <option value="cuti_mati_sedarah">Cuti Kematian Suami/Istri/Anak/Saudara (2 hari)</option>
                        <option value="cuti_mati_klg_serumah">Cuti Kematian Anggota Keluarga Serumah (1 hari)</option>
                        <option value="cuti_mati_ortu">Cuti Kematian Orang Tua/Mertua (2 hari)</option>
                        <option value="cuti_sakit">Cuti Sakit</option>
                        <option value="cuti_lainnya">Lainnya (lihat catatan)</option>
                      </select>
                </div>
                <div class="mb-3">
                    <table style="width: 100%">
                        <tr>
                            <td style="width: 50%">

                                <label for="tanggal_mulai" class="form-label"><strong>Tanggal Mulai Cuti</strong></label>
                                <input type="text"  id="tanggal_mulai" name="tanggal_mulai" readonly>
                                <input type="hidden" id="selected_dates_array" name="selected_dates_array">
                            </td>
                            <td>
                            <div class="tanggal-selesai-wrapper mb-0">
                                <label for="tgl_selesai" class="form-label"><strong>Tanggal Selesai Cuti</strong></label>
                                <input type="text"  id="tanggal_selesai" name="tanggal_selesai" readonly>
                            </div>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="mb-3">
                    <label for="alasan" class="form-label"><strong>Alasan Cuti</strong></label>
                    <textarea class="form-control" id="alasan" name="alasan" rows="3" required></textarea>
                </div>
                <div class="mb-3" id="lampiran" style="display: none;">
                    <label for="file"><strong>Upload Surat Keterangan Sakit/ Surat Keterangan lainnya</strong></label>
                    <input type="file" class="form-control" accept="image/png, image/jpeg, image/jpg" id="surat_keterangan" name="surat_keterangan">
                </div>
                <button type="submit" class="btn btn-primary mb-3">Ajukan Cuti</button>
            </form>
           
        </div>
        
                <div class="accordion mt-4 mt-4" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Aturan Cuti
                        </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 20%">No</th>
                                        <th scope="col">Aturan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">1</th>
                                        <td>Mark</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">2</th>
                                        <td>Jacob</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">3</th>
                                        <td>Larry the Bird</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
    </div>
        
        <script src="{{ asset('jenisCuti.js') }}"></script>
        <script src="{{ asset('script.js') }}"></script>
@endsection