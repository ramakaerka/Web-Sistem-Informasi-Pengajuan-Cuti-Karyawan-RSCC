@extends('assets.mainDashboard')
@section('content')
<!-- Tambahkan file CSS eksternal -->


<div class="profile-container">
    <div class="form-profile-container">
        <!-- Add User Section -->
        <div class="add-user">
            <form action="/admin/addUnitJabatan/save" method="POST">
                @csrf
            <h2 class="text-2xl font-bold mb-4">Add Jabatan</h2>
                <div class="form-group">
                    <label for="unit">Nama Unit</label>
                    <input type="text" id="nama_unit" name="nama_unit">
                </div>
                <div class="form-group">
                    <label for="jabatan">Nama Jabatan</label>
                    <input type="text" id="nama_jabatan" name="nama_jabatan">
                </div>
                <div class="form-group">
                    <label for="unit">Level</label>
                    <input type="text" id="level" name="level">
                </div>
                <label for="jenis_jabatan">Jenis Jabatan</label>
                <select name="jenis_jabatan" id="jenis_jabatan">
                    <option value="shift">Shift</option>
                    <option value="non_shift">Non Shift</option>
                </select>
                <button type="submit" class="btn btn-primary" style="margin-left: 13%; margin-right: 13%;">Submit</button>
            </div>
        </div>
    </form>

@endsection
