@extends('assets.mainDashboard')
@section('content')
<div class="container-fluid p-0">

        <h6 class="note">Selamat Datang di Sistem Pengajuan Cuti. Langkah pertama, silahkan melengkapi profil !</h6>
</div>
<div class="container-fluid p-0"> 
    <form action="/{{ $role }}/profileEdit/save" method="POST" class="form" enctype="multipart/form-data">
        @csrf
        <div class="row w-100 m-0"> 
            <div class="col-6 g-4"> 
                <div class="form-group"> 
                    <label for="nama">Nama</label>
                    <input type="text" class="form-control" name="nama_lengkap" id="nama_lengkap" value="{{ $userprofile->nama_lengkap ?? '' }}" required>
                    
                    <label for="jenkel">Jenis Kelamin</label>
                    <select class="form-select" name="jenis_kelamin" id="jenis_kelamin" required>
                        <option value="L">Laki-Laki</option>
                        <option value="P">Perempuan</option>
                    </select>

                    <label for="no_pokok">No. Pokok Karyawan</label>
                    <input type="text" class="form-control" name="no_pokok" value="{{ $userprofile->no_pokok ?? ''}}" required>

                    <label for="unit">Unit</label>
                    <select class="form-select" name="unit" id="unit" required>
                        <option selected>{{ $userprofile->unit ?? ''}}</option>
                        <option value="IT">IT</option>
                        <option value="Network">Network</option>
                        <option value="Kepegawaian & Diklat">Kepegawaian & Diklat</option>
                    </select>

                    <label for="jabatan">Jabatan</label>
                    <select class="form-select" name="jabatan" id="jabatan" required>
                        <option selected>{{ $userprofile->jabatan ?? ''}}</option>
                        <option value="admin">admin</option>
                        <option value="karyawan">karyawan</option>
                        <option value="manager">manager</option>
                        <option value="HRD">HRD</option>
                    </select>
                </div>
            </div>

            <div class="col-6">
                <label for="alamat">Alamat</label>
                <input type="alamat" class="form-control" name="alamat" value="{{ $userprofile->alamat ?? ''}}" required>

                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" value="{{ $userprofile->email ?? ''}}" required>

                <label for="telp">Nomor Telepon</label>
                <input type="text" class="form-control" name="no_telepon" value="{{ $userprofile->no_telepon ?? ''}}" required>

                <label for="ttd">Upload TTD Digital</label>
                <input type="file" class="form-control" name="ttd" id="ttd" accept="image/png" value="{{ $userprofile->ttd ?? ''}}">

                <label for="foto">Upload Foto Profile</label><button>Tambah TTD</button>
                <p style="color: red">* Disarankan foto berukuran dimensi 3x4 dan ukuran file <= 12mb</p>
                <input type="file" class="form-control" name="foto" id="foto" accept="image/png" value="{{ $userprofile->foto ?? ''}}">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
</div>

@endsection