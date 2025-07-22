@extends('assets.mainDashboard')

@section('content')
<!-- Tambahkan file CSS eksternal -->


<div class="profile-container">
    <div class="form-profile-container">
        <!-- Add User Section -->
        <div class="add-user">
            <form action="/admin/addProfileKaryawan/save" method="POST">
                @csrf
            <h2 class="text-2xl font-bold mb-4">Add User</h2>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="karyawan">Karyawan</option>
                    </select>
                </div>
        </div>

        <!-- Add Profile Section -->
        <div class="add-profile">
            <h2 class="text-2xl font-bold mb-4">Add Profile Karyawan</h2>
                {{-- <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap">
                </div> --}}
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="no_pokok">No Pokok</label>
                    <input type="text" id="no_pokok" name="no_pokok" required>
                </div>
                <div class="form-group">
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="L">Laki-Laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="no_telepon">No Telepon</label>
                    <input type="text" id="no_telepon" name="no_telepon" required>
                </div>
                <div class="form-group">
                    <label for="unit">Unit</label>
                    <select id="unit" name="unit" required>
                        <option value="" selected>-- Pilih Unit --</option>
                        <option value="Rawat Jalan & Home Care">Rawat Jalan & Home Care</option>
                        <option value="Rawat Inap Lantai 2">Rawat Inap Lantai 2</option>
                        <option value="Rawat Inap Lantai 3">Rawat Inap Lantai 3</option>
                        <option value="Kamar Operasi & CSSD">Kamar Operasi & CSSD</option>
                        <option value="Maternal & Perinatal">Maternal & Perinatal</option>
                        <option value="Hemodialisa">Hemodialisa</option>
                        <option value="ICU">ICU</option>
                        <option value="IGD">IGD</option>
                        <option value="Laboratorium">Laboratorium</option>
                        <option value="Radiologi">Radiologi</option>
                        <option value="Gizi">Gizi</option>
                        <option value="Rekam Medis">Rekam Medis</option>
                        <option value="Laundry">Laundry</option>
                        <option value="Pendaftaran">Pendaftaran</option>
                        <option value="Farmasi">Farmasi</option>
                        <option value="Rehabilitasi Medis">Rehabilitasi Medis</option>
                        <option value="Keuangan">Keuangan</option>
                        <option value="Akuntansi">Akuntansi</option>
                        <option value="Kasir">Kasir</option>
                        <option value="Casemix">Casemix</option>
                        <option value="Kepegawaian & Diklat">Kepegawaian & Diklat</option>
                        <option value="Keamanan">Keamanan</option>
                        <option value="Transportasi">Transportasi</option>
                        <option value="Pramu Kantor">Pramu Kantor</option>
                        <option value="Logistik">Logistik</option>
                        <option value="Sanitasi">Sanitasi</option>
                        <option value="IPSRS">IPSRS</option>
                        <option value="Keperawatan">Keperawatan</option>
                        <option value="Penunjang Medis">Penunjang Medis</option>
                        <option value="Keuangan & Akuntansi">Keuangan & Akuntansi</option>
                        <option value="SDM">SDM</option>
                        <option value="Umum">Umum</option>
                        <option value="Pelayanan Medis">Pelayanan Medis</option>
                        <option value="Bagian Penunjang Medis">Bagian Penunjang Medis</option>
                        <option value="Administrasi Umum & Keuangan">Administrasi Umum & Keuangan</option>
                        <option value="Direktur">Direktur</option>
                        {{-- @foreach($units as $unit)
                            <option value="{{ $unit->nama_unit }}">{{ $unit->nama_unit }}</option>
                        @endforeach --}}
                    </select>
                </div>
                <div class="form-group">
                    <label for="jabatan">Jabatan</label>
                    <select id="jabatan" name="jabatan" required>
                        <option value="" selected>-- Pilih Jabatan --</option>
                        <option value="karyawan" selected>Karyawan</option>
                        <option value="Kepala Unit Teknologi Informasi" selected>Kepala Unit Teknologi Informasi</option>
                        <option value="Kepala Unit Kesekretariatan" selected>Kepala Unit Kesekretariatan</option>
                        <option value="Kepala Unit Humas & Pemasaran" selected>Kepala Unit Humas & Pemasaran</option>
                        <option value="Kepala Unit Rawat Jalan & Home care" selected>Kepala Unit Rawat Jalan & Home Care</option>
                        <option value="Kepala Unit Rawat Inap Lantai 2" selected>Kepala Unit Rawat Inap Lantai 2</option>
                        <option value="Kepala Unit Rawat Inap Lantai 3" selected>Kepala Unit Rawat Inap Lantai 3</option>
                        <option value="Kepala Unit Kamar Operasi & CSSD" selected>Kepala Unit Kamar Operasi & CSSD</option>
                        <option value="Kepala Unit Maternal & Perinatal" selected>Kepala Unit Maternal & Perinatal</option>
                        <option value="Kepala Unit Hemodialisa" selected>Kepala Unit Hemodialisa</option>
                        <option value="Kepala Unit ICU" selected>Kepala Unit ICU</option>
                        <option value="Kepala Unit IGD" selected>Kepala Unit IGD</option>
                        <option value="Kepala Unit Laboratorium" selected>Kepala Unit Laboratorium</option>
                        <option value="Kepala Unit Radiologi" selected>Kepala Uni Radiologit</option>
                        <option value="Kepala Unit Gizi" selected>Kepala Unit Gizi</option>
                        <option value="Kepala Unit Rekam Medis" selected>Kepala Unit Rekam Medis</option>
                        <option value="Kepala Unit Laundry" selected>Kepala Unit Laundry</option>
                        <option value="Kepala Unit Pendaftaran" selected>Kepala Unit Pendaftaran</option>
                        <option value="Kepala Unit Farmasi" selected>Kepala Unit Farmasi</option>
                        <option value="Kepala Unit Rehabilitasi Medis" selected>Kepala Unit Rehabilitasi Medis</option>
                        <option value="Kepala Unit Keuangan" selected>Kepala Unit Keuangan</option>
                        <option value="Kepala Unit Akuntansi" selected>Kepala Unit Akuntansi</option>
                        <option value="Kepala Unit Kasir" selected>Kepala Unit Kasir</option>
                        <option value="Kepala Unit Casemix" selected>Kepala Unit Casemix</option>
                        <option value="Kepala Unit Kepegawaian & Diklat" selected>Kepala Unit Kepegawaian & Diklat</option>
                        <option value="Kepala Unit Keamanan" selected>Kepala Unit Keamanan</option>
                        <option value="Kepala Unit Transportasi" selected>Kepala Unit Transportasi</option>
                        <option value="Kepala Unit Pramu Kantor" selected>Kepala Unit Pramu Kantor</option>
                        <option value="Kepala Unit Logistik" selected>Kepala Unit Logistik</option>
                        <option value="Kepala Unit Sanitasi" selected>Kepala Unit Sanitasi</option>
                        <option value="Kepala Unit IPSRS" selected>Kepala Unit IPSRS</option>
                        <option value="Kepala Seksi Keperawatan" selected>Kepala Seksi Keperawatan</option>
                        <option value="Kepala Seksi Penunjang Medis" selected>Kepala Seksi Penunjang Medis</option>
                        <option value="Kepala Seksi Keuangan & Akuntansi" selected>Kepala Seksi Keuangan & Akuntansi</option>
                        <option value="Kepala Seksi SDM" selected>Kepala Seksi SDM</option>
                        <option value="Kepala Seksi Umum" selected>Kepala Seksi Umum</option>
                        <option value="Kepala Bagian Pelayanan Medis" selected>Kepala Bagian Pelayanan Medis</option>
                        <option value="Kepala Bagian Penunjang Medis" selected>Kepala Bagian Penunjang Medis</option>
                        <option value="Kepala Bagian Administrasi Umum & Keuangan" selected>Kepala Bagian Administrasi Umum & Keuangan</option>
                        <option value="Direktur" selected>Direktur</option>
                        {{-- @foreach($jabatans as $jabatan)
                            <option value="{{ $jabatan->nama_jabatan }}">{{ $jabatan->nama_jabatan }}</option>
                        @endforeach --}}
                    </select>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" id="alamat" name="alamat" required>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-left: 13%; margin-right: 13%;">Submit</button>
</form>
@endsection