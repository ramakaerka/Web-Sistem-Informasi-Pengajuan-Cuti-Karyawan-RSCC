@extends('assets.mainDashboard')
@section('content')
    
<div class="d-flex align-items-center justify-content-center p-4">
    <div class="container-fluid text-center min-vh-100 mb-6" style="padding: 0;">
        <div class="row p-1 mt-0 text-start">
            <h3><strong>Profil Saya</strong></h3>
        </div>
        <div class="table-responsive container-profile">

            <table class="table table-success table-striped-columns table-bordered">
                <thead>
                    <tr class="text-center align-middle">
                        <th>Foto</th>
                        <th>Nama Lengkap</th>
                        <th>No. Pokok</th>
                        <th>Unit</th>
                        <th>Jabatan</th>
                        <th>Alamat</th>
                        <th>Email</th>
                        <th>Nomor Telepon</th>
                        <th>Tanda Tangan Digital</th>
                        {{-- <th>Aksi</th> --}}
                    </tr>

                </thead>
                <tbody>
                    <tr class="text-center align-middle">
                        <td style="width: 15%">    
                            @if ($userprofile && $userprofile->foto)
                            <img src="{{ asset('storage/'.$userprofile->foto) }}" class="rounded mx-auto d-block" width="150" height="200" alt="profile_picture">
                            @else
                            <span>Foto profil belum tersedia</span>
                            @endif
                        </td>
                        <td>{{ $userprofile->nama_lengkap }}</td>
                        <td>{{ $userprofile->no_pokok }}</td>
                        <td>{{ $userprofile->unit }}</td>
                        <td>{{ $userprofile->jabatan }}</td>
                        <td>{{ $userprofile->alamat }}</td>
                        <td>{{ $userprofile->email }}</td>
                        <td>{{ $userprofile->no_telepon }}</td>
                        <td>
                            @if ($userprofile && $userprofile->ttd)
                            <img src="{{ asset('storage/'.$userprofile->ttd) }}" class="img-ttd rounded mx-auto d-block" width="200" height="250" alt="ttd">
                            @else
                            <span>Tanda tangan belum tersedia</span>
                            @endif
                        </td>
                        {{-- <td>
                            <a href="/{{ $role }}/profileEdit" class="btn btn-success"><i class="fa-solid fa-pencil"></i><span> Edit Profile</span></a>
                        </td> --}}
                    </tr>

                </tbody>
            </table>
        </div>
        
        
        <div class="dropdown-center mt-3">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                    Edit Profile
                </button>
                <form action="/{{ $role }}/profileEdit/save" method="POST" style="width: 100%" class="dropdown-menu mt-2" enctype="multipart/form-data">
                    @csrf
                
                    <div class="row w-100 m-0"> 
                        <div class="col-6 g-6"> 
                            <div class="form-group"> 
                                <label for="nama">Nama</label>
                                <input type="text" class="form-control m-0" name="nama_lengkap" id="nama" value="{{ $userprofile->nama_lengkap ?? '' }}" required>
                                
                                <label for="jenkel">Jenis Kelamin</label>
                                <select class="form-select m-0" name="jenis_kelamin" id="jenkel" required>
                                    <option value="L">Laki-Laki</option>
                                    <option value="P">Perempuan</option>
                                </select>

                                <label for="no_pokok">No. Pokok Karyawan</label>
                                <input type="text" class="form-control m-0" name="no_pokok" id="no_pokok" value="{{ $userprofile->no_pokok ?? ''}}" required>

                                <label for="unit">Unit</label>
                                <select class="form-select m-0" name="unit" id="unit" required>
                                    <option selected>{{ $userprofile->unit ?? ''}}</option>
                                    <option value="IT">IT</option>
                                    <option value="Network">Network</option>
                                    <option value="Kepegawaian & Diklat">Kepegawaian & Diklat</option>
                                </select>

                                <label for="jabatan">Jabatan</label>
                                <select class="form-select m-0" name="jabatan" id="jabatan" required>
                                    <option selected>{{ $userprofile->jabatan ?? ''}}</option>
                                    <option value="admin">admin</option>
                                    <option value="karyawan">karyawan</option>
                                    <option value="manager">manager</option>
                                    <option value="HRD">HRD</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-6 g-6">
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <input type="text" class="form-control m-0" id="alamat" name="alamat" value="{{ $userprofile->alamat ?? ''}}" required>

                                <label for="email">Email</label>
                                <input type="email" class="form-control m-0" id="email" name="email" value="{{ $userprofile->email ?? ''}}" required>

                                <label for="telp">Nomor Telepon</label>
                                <input type="text" class="form-control m-0" id="telp" name="no_telepon" value="{{ $userprofile->no_telepon ?? ''}}" required>

                                <label for="ttd">Upload TTD Digital</label>
                                <div class="d-flex gap-2 mb-2">
                                    <button class="btn btn-primary flex-grow-1" type="button" data-bs-toggle="modal" data-bs-target="#signatureModal">
                                        <i class="fa-solid fa-pen"></i> Buat TTD
                                    </button>
                                    {{-- <input type="file" class="form-control" name="ttd" id="ttd" accept="image/png"> --}}
                                </div>
                                <input type="file" class="form-control m-0" name="ttd" id="ttd" accept="image/png" value="{{ $userprofile->ttd ?? ''}}"required>

                                

                                <label for="foto">Upload Foto Profile</label>
                                <input type="file" class="form-control m-0" name="foto" id="foto" accept="image/png, image/jpeg, image/jpg" value="{{ $userprofile->foto ?? ''}}" required>
                                <p style="color: red">* Disarankan foto berukuran dimensi 3x4 dan ukuran file <= 12mb dan berformat <strong>.png</strong></p>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 96%;">Save Profile</button>
                    </div>
                </form>
                </div>
                
            <div class="modal fade" id="signatureModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modal title</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="signature-container">
                                                <h2 class="text-center">Form Tanda Tangan</h2>
                                                
                                                    {{-- @if(session('success'))
                                                        <div class="alert alert-success">
                                                            {{ session('success') }}
                                                        </div>
                                                    @endif --}}
                                                
                                                <form action="/{{ $role }}/profileEdit/signature/upload" id="signatureForm"method="POST">
                                                    @csrf
                                                    <p>Silahkan tanda tangan di bawah ini:</p>
                                                    <canvas id="signatureCanvas" width="400" height="200"></canvas>
                                                    <input type="hidden" name="signature" id="signatureData">
                                                    
                                                    <div class="mt-3">
                                                        <button type="button" id="clearButton" class="btn btn-danger">Hapus</button>
                                                        <button type="submit" class="btn btn-success">Simpan Tanda Tangan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        {{-- <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary">Save changes</button>
                                        </div> --}}
                                        </div>
                                    </div>
                                </div>
                                <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Script loaded!");
            console.log("Canvas:", document.getElementById('signatureCanvas'));
            

            const canvas = document.getElementById('signatureCanvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            const clearButton = document.getElementById('clearButton');
            const signatureInput = document.getElementById('signatureData');
            const form = document.getElementById('signatureForm');
            console.log("Form:", document.querySelector('signatureForm'));

            console.log({
                canvas,
                form,
                clearButton,
                signatureInput
            });
            
            let isDrawing = false;
            
            // Setup canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'rgba(0, 0, 0, 0)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = '#000000';
            ctx.lineWidth = 2;
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';
            
            // Event listeners
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
            
            // Touch support
            canvas.addEventListener('touchstart', handleTouch);
            canvas.addEventListener('touchmove', handleTouch);
            canvas.addEventListener('touchend', stopDrawing);
            
            clearButton.addEventListener('click', clearCanvas);
            
            form.addEventListener('submit', function(e) {
                if (canvas.isEmpty) {
                    e.preventDefault();
                    alert('Silahkan berikan tanda tangan terlebih dahulu!');
                } else {
                    signatureInput.value = canvas.toDataURL('image/png', 1.0);
                    console.log("Data: ", signatureInput);
                    console.log("Form submitted!");
                }
            });
            // form.addEventListener('submit', function(e) {
            //     if (isEmpty) {
            //         e.preventDefault();
            //         alert('Silahkan berikan tanda tangan terlebih dahulu!');
            //         return;
            //     }
                
            //     // Konversi ke format yang lebih bersih
            //     const dataUrl = canvas.toDataURL('image/png');
            //     signatureInput.value = dataUrl;
                
            //     // Optional: Tampilkan preview sebelum submit
            //     console.log('Signature data:', signatureInput);
            // });
            
            // Functions
            function startDrawing(e) {
                isDrawing = true;
                draw(e);
            }
            
            function draw(e) {
                if (!isDrawing) return;
                
                const rect = canvas.getBoundingClientRect();
                let x, y;
                
                if (e.type.includes('touch')) {
                    x = e.touches[0].clientX - rect.left;
                    y = e.touches[0].clientY - rect.top;
                } else {
                    x = e.clientX - rect.left;
                    y = e.clientY - rect.top;
                }
                
                ctx.lineTo(x, y);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(x, y);
            }
            
            function handleTouch(e) {
                e.preventDefault();
                if (e.type === 'touchstart') {
                    startDrawing(e);
                } else if (e.type === 'touchmove') {
                    draw(e);
                }
            }
            
            function stopDrawing() {
                isDrawing = false;
                ctx.beginPath();
            }
            
            function clearCanvas() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
            }
            
            // Add isEmpty property to canvas
            Object.defineProperty(canvas, 'isEmpty', {
                get: function() {
                    const blank = document.createElement('canvas');
                    blank.width = canvas.width;
                    blank.height = canvas.height;
                    return canvas.toDataURL() === blank.toDataURL();
                }
            });
        });
    </script>
    </div>
</div>
@endsection
