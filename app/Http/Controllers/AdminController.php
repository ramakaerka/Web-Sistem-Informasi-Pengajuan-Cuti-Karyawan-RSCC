<?php

namespace App\Http\Controllers;

use App\Models\Admin;
Use App\Models\Cuti;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\Manager;
use App\Models\Unit;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use Livewire\WithPagination;
use Log;
use Storage;
use App\Models\HariLibur;
use App\Events\CutiDisetujui;
use App\Events\CutiDiajukan;
use Barryvdh\DomPDF\Facade\PDF;
class AdminController extends Controller
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public function destroy($id)
    {
        $cuti = Cuti::find($id);
        $cuti->delete(); // Mengisi deleted_at
        
        return back()->with('approve success', 'Cuti diarsipkan');
    }

    // Restore data
    public function restore($id)
    {
        Cuti::withTrashed()->find($id)->restore();
        
        return back()->with('approve success', 'Cuti dikembalikan');
    }

    // Hapus permanen
    public function forceDelete($id)
    {
        Cuti::withTrashed()->find($id)->forceDelete();
        
        return back()->with('approve success', 'Cuti dihapus permanen');
    }
    public function profileEdit(){
        $role = Auth::user()->role;
        $user=Auth::user();
        $userprofile=$user->admin;
        return view('profileEdit',compact('role','userprofile'));
    }
    public function saveProfile(Request $request){
        $user=Auth::user();
        $request->validate([
            'nama_lengkap' => 'required|string',
            'jenis_kelamin' => 'required|string',
            'no_pokok' => 'required|string',
            'unit' => 'required|string',
            'jabatan' => 'required|string',
            'alamat' => 'required|string',
            'email' => 'required|string',
            'no_telepon' => 'required|string',
            'ttd' => 'required|image|mimes:png|max:1000',
            'foto' => 'required|image|mimes:png|max:12000'
        ]);
        
            
        $admin = Admin::where('user_id', $user->id)->first();
        $path = $request->file('ttd')->store('ttd','public');
        $pathFoto = $request->file('foto')->store('foto','public');
        if($admin){
            if ($admin->ttd){
                Storage::delete($admin->ttd);
            }
            if ($admin->foto){
                Storage::delete($admin->foto);
            }
            $admin->update([
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_pokok' => $request->no_pokok,
                'unit' => $request->unit,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat,
                'email' => $request->email,
                'no_telepon' => $request->no_telepon,
                'ttd'=>$path,
                'foto'=>$pathFoto,
            ]);
        } else {
        Admin::create([
            'nama_lengkap' => $request->nama_lengkap,
            'jenis_kelamin' => $request->jenis_kelamin,
            'no_pokok' => $request->no_pokok,
            'unit' => $request->unit,
            'jabatan' => $request->jabatan,
            'alamat' => $request->alamat,
            'email' => $request->email,
            'no_telepon' => $request->no_telepon,
            'user_id'=>$user->id,
            'ttd'=>$path,
            'foto'=>$pathFoto,
        ]);
    }
        return redirect()->route('admin.profile')->with('profile success','Data profile telah disimpan');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'signature' => 'required|string|starts_with:data:image/png;base64,'
        ]);
        $user = Auth::user();
        $admin = Admin::where('user_id', $user->id)->first();

         try {
            $imageData = str_replace('data:image/png;base64,', '', $request->signature);
            $imageData = str_replace(' ', '+', $imageData);
            $path = 'ttd/' . uniqid() . '.png';
            
            // dd($path);
            Storage::disk('public')->put($path, base64_decode($imageData));
            
            if($admin){
                if($admin->ttd){
                    Storage::delete($admin->ttd);
                    // Simpan ke database jika diperlukan
                    $admin->update([
                        'ttd' => $path
                    ]);
                }else{
                    Admin::create([
                        'ttd'=>$path
                    ]);
                }
            }
            
            // return response()->json([
            //     'status' => 'success',
            //     'debug' => [
            //         'input_received' => $request->exists('signature'),
            //         'signature_length' => strlen($request->input('signature', '')),
            //         'user_id' => auth()->id()
            //     ]
            // ]);
            return back()->with('profile success', 'TTD berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->with('no changes', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
    public function profile(){
        $user = Auth::user();
        $role=$user->role;
        $userprofile=$user->admin;
        return view('profile',compact('userprofile','role'));
    }
    public function addUnitJabatan(){
        
        return view('addUnitJabatan');
    }
    public function saveUnitJabatan(Request $request){
        $request->validate([
            'nama_unit'=>'nullable|string|unique:unit,nama_unit',
            'nama_jabatan'=>'nullable|string|unique:jabatan,nama_jabatan',
            'level'=>'nullable|string',
            'jenis_jabatan'=>'nullable|string',
        ],
        [
            'nama_unit.unique'=>'nama Unit sudah ada!',
            'nama_jabatan.unique'=>'nama jabatan sudah ada!',
        ]);
        $unitExists = Unit::where('nama_unit', $request->nama_unit)->exists();

    // Cek apakah nama jabatan sudah ada di database
    $jabatanExists = Jabatan::where('nama_jabatan', $request->nama_jabatan)->exists();

    // Jika unit sudah ada, beri pesan error
    if ($unitExists) {
        $message='Nama unit sudah ada!';
    }

    // Jika jabatan sudah ada, beri pesan error
    if ($jabatanExists) {
        return redirect()->back()->with('no changes', 'Nama jabatan sudah ada!');
    }

        $message='Tidak ada perubahan!';

        if($request->filled('nama_unit')){
            Unit::create([
                'nama_unit'=>$request->nama_unit
            ]);
            $message='Berhasil menambah Unit!';
        }
        if($request->filled('nama_jabatan')){
            Jabatan::create([
                'nama_jabatan'=>$request->nama_jabatan,
                'level'=>$request->level,
                'jenis_jabatan'=>$request->jenis_jabatan,
            ]);
            $message='Berhasil menambah Jabatan!';
        }
        return redirect()->back()->with('profile success',$message);
    }
    public function addProfile(){
        $units = Unit::select('nama_unit')->distinct()->get();
        $jabatans = Jabatan::select('nama_jabatan')->distinct()->get();
        return view('addProfile',compact(['units','jabatans']));
    }
    public function saveProfileKaryawan(Request $request){
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
            'name' => 'required|string',
            'role' => 'required|string',
            'nama_lengkap' => 'string',
            'email' => 'required|string',
            'no_pokok' => 'required|string',
            'jenis_kelamin' => 'required|string',
            'no_telepon' => 'required|string',
            'unit' => 'required|string',
            'jabatan' => 'required|string',
            'alamat' => 'required|string',
        ]);
        
        $jabatanDokter = ['Dokter Spesialis Umum'];
        $jabatanKanit = ['Kepala Unit Rawat Jalan & Home Care'];
        $jabatanKasi = ['Kepala Seksi Keperawatan'];
        $jabatanKabag = ['Kepala Bagian Pelayanan Medis'];
        
        $jabatanId = Jabatan::where('nama_jabatan',$request->jabatan)->pluck('id')->first();
        $unitId = Unit::where('nama_unit',$request->unit)->pluck('id')->first();
        
        if($request->role == 'karyawan'){

            $user = User::create([
                'username' => $request->username,
                'password' => $request->password,
                'name' => $request->name,
                'role' => $request->role,
            ]);
            $karyawan = Karyawan::create([
                'nama_lengkap' => $user->name,
                'email' => $request->email,
                'no_pokok' => $request->no_pokok,
                'jenis_kelamin' => $request->jenis_kelamin,
                'role' => $user->role,
                'no_telepon' => $request->no_telepon,
                'unit' => $request->unit,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat,
                'user_id'=>$user->id,
            ]);
            return redirect()->back()->with('profile success','User dan Profil Karyawan berhasil ditambahkan !');
        }
        elseif ($request->role == 'admin'){
            $user = User::create([
                'username' => $request->username,
                'password' => $request->password,
                'name' => $request->name,
                'role' => $request->role,
            ]);
            $admin = Admin::create([
                'nama_lengkap' => $user->name,
                'email' => $request->email,
                'no_pokok' => $request->no_pokok,
                'jenis_kelamin' => $request->jenis_kelamin,
                'role' => $user->role,
                'no_telepon' => $request->no_telepon,
                'unit' => $request->unit,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat,
                'user_id'=>$user->id,
            ]);
            return redirect()->back()->with('profile success','User dan Profil Karyawan berhasil ditambahkan !');
        }
        elseif ($request->role == 'manager'){
            $unit = $request->unit;
            $jabatan = $request->jabatan;
            $user = User::create([
                'username' => $request->username,
                'password' => $request->password,
                'name' => $request->name,
                'role' => $request->role,
            ]);
            $manager = Manager::create([
                'nama_lengkap' => $user->name,
                'email' => $request->email,
                'no_pokok' => $request->no_pokok,
                'jenis_kelamin' => $request->jenis_kelamin,
                'role' => $user->role,
                'no_telepon' => $request->no_telepon,
                'unit' => $request->unit,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat,
                'user_id'=>$user->id,
            ]);
            return redirect()->back()->with('profile success','User dan Profil Karyawan berhasil ditambahkan !');
        }
        return view('addProfile')->with('no changes','Data tidak tersimpan. Isi semua data yang ada !');
    }
    public function pengajuan(){
        $user=Auth::user();
        $role=$user->role;
        $tanggalLibur = HariLibur::pluck('tanggal')->toArray();
        return view('pengajuan',compact('role','user','tanggalLibur'));
    }
    public function store(Request $request){
        $request->validate([
            'jenis_cuti' => [
                'required', 'string',
                function ($attribute, $value, $fail) use ($request) {
                    $user = Auth::user(); 
                    $gender = $user->admin->jenis_kelamin;

                    if ($value === 'cuti_melahirkan' && strtoupper($gender) !== 'P') {
                        $fail("Hanya perempuan yang dapat mengajukan cuti melahirkan.");
                    }
                }
            ],
            'selected_dates_array' => 'required_if:jenis_cuti,cuti_tahunan,cuti_sakit,cuti_lainnya',
            'surat_keterangan' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $dates = json_decode($request->selected_dates_array ?? '[]', true) ?: [];
                    $jumlahHari = count($dates);
                    if (($request->jenis_cuti === 'cuti_sakit' || $request->jenis_cuti === 'cuti_lainnya') 
                        && $jumlahHari > 3 
                        && empty($value)) {
                        $fail('Surat keterangan wajib diupload untuk jenis cuti ini dengan durasi lebih dari 3 hari.');
                    }
                },
                'image',
                'mimes:png,jpg,jpeg',
                'max:12000'
            ],
            'tanggal_mulai' => ['required','date',
            function ($attribute, $value, $fail) use ($request) {
                $tambahanHariMulai = 0;  
                
                $aturanMulai = [
                    'cuti_tahunan' => 7,
                    'cuti_panjang' => 7,
                    'cuti_sakit' => 0,
                    'cuti_melahirkan' => 0,
                    'cuti_menikah' => 7,
                    'cuti_kelahiran_anak' => 1,
                    'cuti_pernikahan_anak' => 3,
                    'cuti_mati_sedarah' => 0,
                    'cuti_mati_klg_serumah' => 0,
                    'cuti_mati_ortu' => 0,
                    'cuti_lainnya' => 1,
                ];
    
                // Terapkan aturan jika ada dalam daftar
                if (isset($aturanMulai[$request->jenis_cuti])) {
                    $tambahanHariMulai = $aturanMulai[$request->jenis_cuti];
                }
    
                $minDate = now()->addDays($tambahanHariMulai)->toDateString();
    
                if ($value < $minDate) {
                    $fail("Tanggal mulai harus minimal " . now()->addDays($tambahanHariMulai)->format('Y-m-d') . " untuk cuti " . $request->jenis_cuti);
                }
            }
        ],
        'tanggal_selesai' => [
            'required','date',
            function ($attribute, $value, $fail) use ($request) {
                $tambahanHariSelesai = 0;  
                
                $aturanSelesai = [
                    'cuti_tahunan' => 0,
                    'cuti_panjang' => 6,
                    'cuti_sakit' => 12,
                    'cuti_melahirkan' => 90,
                    'cuti_menikah' => 3,
                    'cuti_kelahiran_anak' => 2,
                    'cuti_pernikahan_anak' => 2,
                    'cuti_mati_sedarah' => 2,
                    'cuti_mati_klg_serumah' => 1,
                    'cuti_mati_ortu' => 2,
                    'cuti_lainnya' => 3,
                ];
    
                if (isset($aturanSelesai[$request->jenis_cuti])) {
                    $tambahanHariSelesai = $aturanSelesai[$request->jenis_cuti];
                }
    
                $minSelesai = \Carbon\Carbon::parse($request->tanggal_mulai)->addDays($tambahanHariSelesai)->toDateString();

                if($request->jenis_cuti != 'cuti_tahunan'){

                    if ($value > $minSelesai) {
                        $fail("Tanggal selesai maksimal " . \Carbon\Carbon::parse($request->tanggal_mulai)->addDays($tambahanHariSelesai)->format('Y-m-d') . " untuk " . $request->jenis_cuti);
                    }
                    elseif ($value < $minSelesai) {
                        $fail("Tanggal selesai minimal " . \Carbon\Carbon::parse($request->tanggal_mulai)->addDays($tambahanHariSelesai)->format('Y-m-d') . " untuk " . $request->jenis_cuti);
                    }
                }
            }
        ],
            'alasan'=> 'required|string',
        ]);
        
        $tgl_mulai = $request->tanggal_mulai;
        $hari_ini = new DateTime();
        $hari_ini->setTime(0,0,0);
        $tanggal_mulai = new DateTime($request->tanggal_mulai);
        $tanggal_selesai = new DateTime($request->tanggal_selesai);
        $jumlah_hari = $tanggal_mulai->diff($tanggal_selesai)->days;

        $proses_cuti = Cuti::with('admin')->get();
        $jenisCuti = $request->jenis_cuti;
        $user = Auth::user()->admin;
        $admin=$user;

        if(!$user->ttd)
        {
            return redirect()->back()->with('no changes','Sebelum mengajukan cuti, anda harus mengupload tanda tangan digital di halaman Profile');
        }
        else{

            if ($request->jenis_cuti === 'cuti_melahirkan') {
                if ($admin->jenis_kelamin !== 'P') {
                    return redirect()->back()->with('no changes', 'Cuti Melahirkan hanya dapat diajukan oleh karyawan perempuan.');
                }
                
                // Cek apakah jumlah hari yang diajukan sesuai dengan aturan 90 hari cuti melahirkan
                if ($jumlah_hari > 90) {
                    return redirect()->back()->with('no changes', 'Cuti Melahirkan maksimal 90 hari.');
                }
                
                // Simpan pengajuan cuti tanpa mengurangi jatah cuti tahunan
                $cuti =Cuti::create([
                    'admin_id' => $admin->id,
                    'jenis_cuti' => $request->jenis_cuti,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'jumlah_hari' => $jumlah_hari,
                    'alasan' => $request->alasan,
                    'surat_keterangan' => $request->surat_keterangan ?? null,
                ]);
                // event(new CutiDiajukan($cuti ,'admin'));
                return redirect()->route('admin.email',$cuti->id)->with('pengajuan success', 'Pengajuan Cuti Melahirkan berhasil dilakukan, menunggu persetujuan atasan.');
            }
            elseif ($jenisCuti === 'cuti_tahunan') {
                // Untuk cuti tahunan (multiple dates)
                $dates = json_decode($request->selected_dates_array);
                $jumlahHari = count($dates);
                $sisaCuti = $admin->sisa_cuti + $admin->sisa_cuti_sebelumnya;
                    
                if ($jumlahHari > $sisaCuti) {
                    return back()->withErrors([
                        'selected_dates_array' => "Jatah cuti sakit Anda hanya {$sisaCuti} hari, tidak mencukupi untuk {$jumlahHari} hari cuti."
                    ]);
                }

                // Simpan setiap tanggal cuti
                foreach ($dates as $date) {
                    $Cuti = Cuti::create([
                        'admin_id' => $admin->id,
                        'jenis_cuti' => $jenisCuti,
                        'tanggal_mulai' => $date,
                        'tanggal_selesai' => $date, // Untuk cuti tahunan, tanggal selesai sama dengan tanggal mulai
                        'jumlah_hari' => 1,
                        'alasan' => $request->alasan,
                        'surat_keterangan' =>$request->surat_keterangan ?? null
                    ]);
                }
                return redirect()->route('admin.email',$Cuti->id)->with('pengajuan success', 'Pengajuan Cuti berhasil dilakukan, menunggu persetujuan atasan.');
                // event(new CutiDiajukan($Cuti,'admin'));
            }
            elseif ($jenisCuti === 'cuti_sakit' || $jenisCuti === 'cuti_lainnya') {
                $dates = json_decode($request->selected_dates_array);
                $jumlahHari = count($dates);
                if($request->file('surat_keterangan')){
                    $pathSuket = $request->file('surat_keterangan')->store('suket','public');
                }
                
                $sisaCuti = $admin->sisa_cuti + $admin->sisa_cuti_sebelumnya;
                    
                if ($jumlahHari > $sisaCuti) {
                    return back()->withErrors([
                        'selected_dates_array' => "Jatah cuti tahunan Anda hanya {$sisaCuti} hari, tidak mencukupi untuk {$jumlahHari} hari cuti."
                    ]);
                }

                if($jumlahHari > 3){

                    foreach ($dates as $date){
                        $Cuti = Cuti::create([
                            'admin_id' => $admin->id,
                            'jenis_cuti' => $jenisCuti,
                            'tanggal_mulai' => $date,
                            'tanggal_selesai' => $date, 
                            'jumlah_hari' => 1,
                            'alasan' => $request->alasan,
                            'surat_keterangan' =>$pathSuket ?? null,
                            'status_manager' => 'approved:bySistem' ?? null,
                            'status_admin' =>'approved:bySistem' ?? null,
                        ]);
                    }
                }
                
                    foreach ($dates as $date){
                        $Cuti = Cuti::create([
                            'admin_id' => $admin->id,
                            'jenis_cuti' => $jenisCuti,
                            'tanggal_mulai' => $date,
                            'tanggal_selesai' => $date, 
                            'jumlah_hari' => 1,
                            'alasan' => $request->alasan,
                            'surat_keterangan' =>$pathSuket ?? null,
                            'status_manager' => 'approved:bySistem' ?? null,
                            'status_admin' =>'approved:bySistem' ?? null,
                        ]);
                    }
                $Cuti->admin->update(['sisa_cuti' => $Cuti->admin->sisa_cuti - $jumlahHari]);
                $Cuti->admin->refresh();
                return redirect()->route('admin.email',$Cuti->id)->with('pengajuan success', 'Pengajuan Cuti berhasil dilakukan, menunggu persetujuan atasan.');
                // event(new CutiDiajukan($Cuti,'admin'));
            }
            else{
                if (($admin->sisa_cuti + $admin->sisa_cuti_sebelumnya) < $jumlah_hari){
                    return redirect()->back()->with('no changes','Sisa cuti tidak mencukupi');
                }

                if($tanggal_mulai >= $hari_ini){

                    $cuti = Cuti::whereHas('admin')
                    ->where('admin_id',Auth::user()->admin->id)
                    ->where('tanggal_mulai',$tgl_mulai)->first();

                    
                    if($cuti){
                        return redirect()->back()->with('no changes','Anda sudah memiliki pengajuan cuti di tanggal ini!');
                    }
                    else {
                        $Cuti = Cuti::create([
                            'admin_id' => $admin->id,
                            'jenis_cuti' => $request->jenis_cuti,
                            'tanggal_mulai' => $request->tanggal_mulai,
                            'tanggal_selesai' => $tanggal_selesai,
                            'jumlah_hari' => $jumlah_hari,
                            'alasan' => $request->alasan,
                            'surat_keterangan' => $request->surat_keterangan
                        ]);
                        return redirect()->route('admin.email',$Cuti->id)->with('pengajuan success', 'Pengajuan Cuti berhasil dilakukan, menunggu persetujuan atasan.');
                        // event(new CutiDiajukan($Cuti,'admin'));
                    }
                }
                else {
                    return redirect()->back()->with('no changes','Tanggal mulai harus hari ini atau setelah hari ini');
                }
            }

    // return redirect()->route('admin.status')->with('pengajuan success','Pengajuan cuti berhasil dilakukan!');
    }
}
private function getAtasanLangsung($jabatanPengaju)
{
    // Mapping jabatan dengan atasan langsungnya
    $hierarkiJabatan = [
        

        'Kepala Bagian Pelayanan Medis' => 'Direktur',
        'Kepala Bagian Penunjang Medis' => 'Direktur',
        'Kepala Bagian Administrasi Umum & Keuangan' => 'Direktur',
        'Kepala Seksi Penunjang Medis' => 'Kepala Bagian Penunjang Medis',
        'Kepala Seksi Keperawatan' => 'Kepala Bagian Pelayanan Medis',
        'Kepala Seksi Keuangan & Akuntansi' => 'Kepala Bagian Administrasi Umum & Keuangan',
        'Kepala Seksi SDM' => 'Kepala Bagian Administrasi Umum & Keuangan',
        'Kepala Seksi Umum' => 'Kepala Bagian Administrasi Umum & Keuangan',
        'Kepala Unit Teknologi Informasi' => 'Kepala Bagian Administrasi Umum & Keuangan',
        'Kepala Unit Humas & Pemasaran' => 'Kepala Bagian Administrasi Umum & Keuangan',
        'Kepala Unit Kesekretariatan' => 'Kepala Bagian Administrasi Umum & Keuangan',

        'Kepala Unit Rawat Jalan & Home Care' => 'Kepala Seksi Keperawatan',
        'Kepala Unit Rawat Inap Lantai 2' => 'Kepala Seksi Keperawatan',
        'Kepala Unit Rawat Inap Lantai 3' => 'Kepala Seksi Keperawatan',
        'Kepala Unit Kamar Operasi & CSSD' => 'Kepala Seksi Keperawatan',
        'Kepala Unit Maternal & Perinatal' => 'Kepala Seksi Keperawatan',
        'Kepala Unit Hemodialisa' => 'Kepala Seksi Keperawatan',
        'Kepala Unit ICU' => 'Kepala Seksi Keperawatan',
        'Kepala Unit IGD' => 'Kepala Seksi Keperawatan',

        'Kepala Unit Laboratorium' => 'Kepala Seksi Penunjang Medis',
        'Kepala Unit Radiologi' => 'Kepala Seksi Penunjang Medis',
        'Kepala Unit Gizi' => 'Kepala Seksi Penunjang Medis',
        'Kepala Unit Rekam Medis' => 'Kepala Seksi Penunjang Medis',
        'Kepala Unit Laundry' => 'Kepala Seksi Penunjang Medis',
        'Kepala Unit Pendaftaran' => 'Kepala Seksi Penunjang Medis',
        'Kepala Unit Rehabilitasi Medis' => 'Kepala Seksi Penunjang Medis',
        'Kepala Unit Farmasi' => 'Kepala Seksi Penunjang Medis',

        'Kepala Unit Keuangan' => 'Kepala Seksi Keuangan & Akuntansi',
        'Kepala Unit Akuntansi' => 'Kepala Seksi Keuangan & Akuntansi',
        'Kepala Unit Kasir' => 'Kepala Seksi Keuangan & Akuntansi',
        'Kepala Unit Casemix' => 'Kepala Seksi Keuangan & Akuntansi',

        'Kepala Unit Kepegawaian & Diklat' => 'Kepala Bagian Administrasi Umum & Keuangan',
        'Kepala Unit Keamanan' => 'Kepala Bagian Administrasi Umum & Keuangan',
        'Kepala Unit Transportasi' => 'Kepala Bagian Administrasi Umum & Keuangan',
        'Kepala Unit Pramu Kantor' => 'Kepala Bagian Administrasi Umum & Keuangan',

        'Kepala Unit Logistik' => 'Kepala Seksi Umum',
        'Kepala Unit Sanitasi' => 'Kepala Seksi Umum',
        'Kepala Unit IPSRS ' => 'Kepala Seksi Umum',

        // 'Teknologi Informasi' => 'Kepala Unit Teknologi Informasi',
        
        'Rawat Jalan & Home Care' => 'Kepala Unit Rawat Jalan & Home Care',
        'Rawat Inap Lantai 2' => 'Kepala Unit Rawat Inap Lantai 2',
        'Rawat Inap Lantai 3' => 'Kepala Unit Rawat Inap Lantai 3',
        'Kamar Operasi & CSSD' => 'Kepala Unit Kamar Operasi & CSSD',
        'Maternal & Perinatal' => 'Kepala Unit Maternal & Perinatal',
        'Hemodialisa' => 'Kepala Unit Hemodialisa',
        'ICU' => 'Kepala Unit ICU',
        'IGD' => 'Kepala Unit IGD',

        'Laboratorium' => 'Kepala Unit Laboratorium',
        'Radiologi' => 'Kepala Unit Radiologi',
        'Gizi' => 'Kepala Unit Gizi',
        'Rekam Medis' => 'Kepala Unit Rekam Medis',
        'Laundry' => 'Kepala Unit Laundry',
        'Pendaftaran' => 'Kepala Unit Pendaftaran',
        'Rehabilitasi Medis' => 'Kepala Unit Rehabilitasi Medis',
        'Farmasi' => 'Kepala Unit Farmasi',

        'Keuangan' => 'Kepala Unit Keuangan',
        'Akuntansi' => 'Kepala Unit Akuntansi',
        'Kasir' => 'Kepala Unit Kasir',
        'Casemix' => 'Kepala Unit Casemix',

        'Kepegawaian & Diklat' => 'Kepala Unit Kepegawaian & Diklat',
        'Keamanan' => 'Kepala Unit Keamanan',
        'Transportasi' => 'Kepala Unit Transportasi',
        'Pramu Kantor' => 'Kepala Unit Pramu Kantor',

        'Logistik' => 'Kepala Unit Logistik',
        'Sanitasi' => 'Kepala Unit Sanitasi',
        'IPSRS ' => 'Kepala Unit IPSRS',
        // Tambahkan hierarki lain sesuai kebutuhan
    ];

    return $hierarkiJabatan[$jabatanPengaju] ?? null;
}

public function updateCutiStatus(){

    $karyawan = [
        'jabatan' => [
            'karyawan',
            ''
        ]
    ];
    
    $jabatanUser = Auth::user()->admin->jabatan;
    
    try {
        DB::beginTransaction();

        if($jabatanUser === 'Kepala Seksi SDM' || $jabatanUser === 'Kepala Unit Teknologi Informasi'){
            // Ambil data cuti yang masih pending
            $cutiListKaryawanPending = Cuti::where('status_manager', 'pending')->whereHas('karyawan', function ($q) use ($karyawan) {
                $q->whereIn('jabatan',$karyawan['jabatan'])
                ->whereNotIn('unit', ['Teknologi Informasi','Kepegawaian & Diklat']);
            })->get();
            $cutiListKaryawanApproved = Cuti::whereHas('karyawan', function ($q) use ($karyawan) {
                $q->whereIn('jabatan',$karyawan['jabatan'])
                ->whereNotIn('unit', ['Teknologi Informasi'])
                ->where('status_manager','LIKE','approved:%')
                ->where('status_manager','NOT LIKE','approved:Kepala Unit Teknologi Informasi%')
                ->where('status_manager','NOT LIKE','approved:Kepala Bagian Administrasi Umum & Keuangan%');
            })->get();
            $cutiListKanitPending = Cuti::whereHas('manager', function ($q) use ($karyawan) {
                $q
                ->where('jabatan','LIKE','Kepala Unit%')
                ->where('status_manager','LIKE','pending%');
            })->get();
            $cutiListKanitApproved = Cuti::whereHas('manager', function ($q) use ($karyawan) {
                $q->whereNotIn('jabatan',['Kepala Unit Kepegawaian & Diklat','Kepala Unit Keamanan','Kepala Unit Transportasi','Kepala Unit Pramu Kantor'])
                ->where('jabatan','LIKE','Kepala Unit%')
                ->where('status_manager','LIKE','approved:%');
            })->get();
            $cutiListKasiPending = Cuti::whereHas('manager', function ($q) use ($karyawan) {
                $q->where('jabatan','LIKE','Kepala Seksi%')
                ->where('status_manager','LIKE','pending%');
            })->get();
            $cutiListKasiApproved = Cuti::whereHas('manager', function ($q) use ($karyawan) {
                $q->where('jabatan','LIKE','Kepala Seksi%')
                ->where('status_manager','LIKE','approved:%');
            })->get();
            $cutiListKasiPending = Cuti::whereHas('manager', function ($q) use ($karyawan) {
                $q->where('jabatan','LIKE','Kepala Seksi%')
                ->where('status_manager','LIKE','pending%');
            })->get();
            $cutiListKasiApproved = Cuti::whereHas('manager', function ($q) use ($karyawan) {
                $q->where('jabatan','LIKE','Kepala Seksi%')
                ->where('status_manager','LIKE','approved:%');
            })->get();
            $cutiListKabagPending = Cuti::whereHas('manager', function ($q) use ($karyawan) {
                $q->where('jabatan','LIKE','Kepala Bagian%')
                ->where('status_manager','LIKE','pending%');
            })->get();
            $cutiListKabagApproved = Cuti::whereHas('manager', function ($q) use ($karyawan) {
                $q->where('jabatan','LIKE','Kepala Bagian%')
                ->where('status_manager','LIKE','approved:%');
            })->get();
            
            
        }
        
            foreach ($cutiListKaryawanPending as $cuti) {
                // Ambil jabatan atasan langsung dari pengaju cuti
                $jabatanPengaju = $cuti->karyawan->unit;
                $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);

                
                while ($jabatanAtasanLangsung !== null){
                    
                    $jabatanManagers = Manager::pluck('jabatan')->toArray();
                    // Cek apakah atasan langsung ada di database

                    if (in_array($jabatanAtasanLangsung,$jabatanManagers)) {
                        break;
                    } 
                    // Jika atasan langsung tidak ada, ubah status_manager
                    $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    $jabatanPengaju = $jabatanAtasanLangsung;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
                }
            }
            foreach ($cutiListKanitPending as $cuti) {
                // Ambil jabatan atasan langsung dari pengaju cuti
                $jabatanPengaju = $cuti->manager->jabatan;
                $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);

                
                while ($jabatanAtasanLangsung !== null){
                    
                    $jabatanManagers = Manager::pluck('jabatan')->toArray();
                    // Cek apakah atasan langsung ada di database

                    if (in_array($jabatanAtasanLangsung,$jabatanManagers)) {
                        break;
                    } 
                    // Jika atasan langsung tidak ada, ubah status_manager
                    $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    $jabatanPengaju = $jabatanAtasanLangsung;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
                }
            }
            foreach ($cutiListKasiPending as $cuti) {
                // Ambil jabatan atasan langsung dari pengaju cuti
                $jabatanPengaju = $cuti->manager->jabatan;
                $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);

                
                while ($jabatanAtasanLangsung !== null){
                    
                    $jabatanManagers = Manager::pluck('jabatan')->toArray();
                    // Cek apakah atasan langsung ada di database

                    if (in_array($jabatanAtasanLangsung,$jabatanManagers)) {
                        break;
                    } 
                    // Jika atasan langsung tidak ada, ubah status_manager
                    $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    $jabatanPengaju = $jabatanAtasanLangsung;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
                }
            }
            foreach ($cutiListKabagPending as $cuti) {
                // Ambil jabatan atasan langsung dari pengaju cuti
                $jabatanPengaju = $cuti->manager->jabatan;
                $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);

                
                while ($jabatanAtasanLangsung !== null){
                    
                    $jabatanManagers = Manager::pluck('jabatan')->toArray();
                    // Cek apakah atasan langsung ada di database

                    if (in_array($jabatanAtasanLangsung,$jabatanManagers)) {
                        break;
                    } 
                    // Jika atasan langsung tidak ada, ubah status_manager
                    $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    $jabatanPengaju = $jabatanAtasanLangsung;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
                }
            }
            
            
            foreach ($cutiListKaryawanApproved as $cuti) {
                // Ambil jabatan atasan langsung dari pengaju cuti
                $jabatanPengaju = Str::after($cuti->status_manager, 'approved:');
                $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);


                while ($jabatanAtasanLangsung !== null){
                    
                    // Cek apakah atasan langsung ada di database
                    $jabatanManagers = Manager::pluck('jabatan')->toArray();

                    if (in_array($jabatanAtasanLangsung,$jabatanManagers)) {
                        break;
                    } 
                    // Jika atasan langsung tidak ada, ubah status_manager
                    $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    $jabatanPengaju = $jabatanAtasanLangsung;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
                } 
            }
            foreach ($cutiListKanitApproved as $cuti) {
                // Ambil jabatan atasan langsung dari pengaju cuti
                $jabatanPengaju = Str::after($cuti->status_manager, 'approved:');
                $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);


                while ($jabatanAtasanLangsung !== null){
                    
                    // Cek apakah atasan langsung ada di database
                    $jabatanManagers = Manager::pluck('jabatan')->toArray();

                    if (in_array($jabatanAtasanLangsung,$jabatanManagers)) {
                        break;
                    } 
                    // Jika atasan langsung tidak ada, ubah status_manager
                    $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    $jabatanPengaju = $jabatanAtasanLangsung;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung(jabatanPengaju: $jabatanPengaju);
                } 
            }
            foreach ($cutiListKasiApproved as $cuti) {
                // Ambil jabatan atasan langsung dari pengaju cuti
                $jabatanPengaju = Str::after($cuti->status_manager, 'approved:');
                $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);


                while ($jabatanAtasanLangsung !== null){
                    
                    // Cek apakah atasan langsung ada di database
                    $jabatanManagers = Manager::pluck('jabatan')->toArray();

                    if (in_array($jabatanAtasanLangsung,$jabatanManagers)) {
                        break;
                    } 
                    // Jika atasan langsung tidak ada, ubah status_manager
                    $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    $jabatanPengaju = $jabatanAtasanLangsung;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
                } 
            }
            foreach ($cutiListKabagApproved as $cuti) {
                // Ambil jabatan atasan langsung dari pengaju cuti
                $jabatanPengaju = Str::after($cuti->status_manager, 'approved:');
                $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);


                while ($jabatanAtasanLangsung !== null){
                    
                    // Cek apakah atasan langsung ada di database
                    $jabatanManagers = Manager::pluck('jabatan')->toArray();

                    if (in_array($jabatanAtasanLangsung,$jabatanManagers)) {
                        break;
                    } 
                    // Jika atasan langsung tidak ada, ubah status_manager
                    $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    $jabatanPengaju = $jabatanAtasanLangsung;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
                } 
            }
            
         
        DB::commit();
        return response()->json(['success' => 'Status cuti diperbarui'], 200);
    } catch (\Illuminate\Database\QueryException $e) {
        DB::rollBack();
        return response()->json(['error' => 'Gagal memperbarui status cuti', 'message' => $e->getMessage()], 500);
    }
}

    public function persetujuan(Request $request){
        $user=Auth::user();
        $role=$user->role;
        $jabatanUser=Auth::user()->admin->jabatan;
        
        $jabatanConfigs=[
            'Kepala Unit Teknologi Informasi'=>[
                'status_manager'=>['approved:Kepala Bagian Pelayanan Medis','approved:Kepala Bagian Penunjang Medis','approved:Kepala Bagian Administrasi Umum & Keuangan','approved:bySistem'],
                'status_admin'=>['pending','approved:bySistem'],
                'unit'=>['Pelayanan Medis','Bagian Penunjang Medis','Administrasi Umum & Keuangan','Rawat Jalan & Home Care','Rawat Inap Lantai 2','Rawat Inap Lantai 3','Kamar Operasi & CSSD','Maternal & Perinatal','Hemodialisa','ICU','IGD','IGD & Klinik Umum','Keperawatan','Penunjang Medis','Laboratorium','Radiologi','Gizi','Rekam Medis','Laundry','Pendaftaran','Farmasi','Rehabilitasi Medis','Keuangan & Akuntansi','SDM','Umum','Keuangan','Akuntansi','Kasir','Casemix','Kepegawaian & Diklat','Keamanan','Transportasi','Pramu Kantor','Logistik','Sanitasi','IPSRS','Humas & Pemasaran','Kesekretariatan'],
                'jabatan'=>['karyawan','Kepala Bagian Pelayanan Medis','Kepala Bagian Penunjang Medis','Kepala Bagian Administrasi Umum & Keuangan','Kepala Seksi Keperawatan','Kepala Seksi IGD & Klinik Umum','Kepala Seksi Penunjang Medis','Kepala Seksi Keuangan & Akuntansi','Kepala Seksi SDM','Kepala Seksi Umum','Kepala Unit Rawat Jalan & Home Care','Kepala Unit Rawat Inap Lantai 2','Kepala Unit Rawat Inap Lantai 3','Kepala Unit Kamar Operasi & CSSD','Kepala Unit Maternal & Perinatal','Kepala Unit Hemodialisa','Kepala Unit ICU','Kepala Unit IGD','Kepala Unit Laboratorium','Kepala Unit Radiologi','Kepala Unit Gizi','Kepala Unit Rekam Medis','Kepala Unit Laundry','Kepala Unit Pendaftaran','Kepala Unit Farmasi','Kepala Unit Rehabilitasi Medis','Kepala Unit Keuangan','Kepala Unit Akuntansi','Kepala Unit Kasir','Kepala Unit Casemix','Kepala Unit Kepegawaian & Diklat','Kepala Unit Keamanan','Kepala Unit Transportasi','Kepala Unit Pramu Kantor','Kepala Unit Logistik','Kepala Unit Sanitasi','Kepala Unit IPSRS']
            ],
            'Kepala Seksi SDM'=>[
                'status_manager'=>['approved:Kepala Bagian Pelayanan Medis','approved:Kepala Seksi Keperawatan','approved:Kepala Bagian Penunjang Medis','approved:Kepala Bagian Administrasi Umum & Keuangan','approved:Kepala Unit Hemodialisa','approved:Kepala Unit Teknologi Informasi','approved:bySistem'],
                'status_admin'=>['pending','approved:bySistem'],
                'unit'=>['Pelayanan Medis','Bagian Penunjang Medis','Administrasi Umum & Keuangan','Rawat Jalan & Home Care','Rawat Inap Lantai 2','Rawat Inap Lantai 3','Kamar Operasi & CSSD','Maternal & Perinatal','Hemodialisa','ICU','IGD','IGD & Klinik Umum','Keperawatan','Penunjang Medis','Laboratorium','Radiologi','Gizi','Rekam Medis','Laundry','Pendaftaran','Farmasi','Rehabilitasi Medis','Keuangan & Akuntansi','SDM','Umum','Keuangan','Akuntansi','Kasir','Casemix','Kepegawaian & Diklat','Keamanan','Transportasi','Pramu Kantor','Logistik','Sanitasi','IPSRS','Teknologi Informasi','Humas & Pemasaran','Kesekretariatan'],
                'jabatan'=>['karyawan','Kepala Bagian Pelayanan Medis','Kepala Bagian Penunjang Medis','Kepala Bagian Administrasi Umum & Keuangan','Kepala Seksi Keperawatan','Kepala Seksi IGD & Klinik Umum','Kepala Seksi Penunjang Medis','Kepala Seksi Keuangan & Akuntansi','Kepala Seksi SDM','Kepala Seksi Umum','Kepala Unit Rawat Jalan & Home Care','Kepala Unit Rawat Inap Lantai 2','Kepala Unit Rawat Inap Lantai 3','Kepala Unit Kamar Operasi & CSSD','Kepala Unit Maternal & Perinatal','Kepala Unit Hemodialisa','Kepala Unit ICU','Kepala Unit IGD','Kepala Unit Laboratorium','Kepala Unit Radiologi','Kepala Unit Gizi','Kepala Unit Rekam Medis','Kepala Unit Laundry','Kepala Unit Pendaftaran','Kepala Unit Farmasi','Kepala Unit Rehabilitasi Medis','Kepala Unit Keuangan','Kepala Unit Akuntansi','Kepala Unit Kasir','Kepala Unit Casemix','Kepala Unit Kepegawaian & Diklat','Kepala Unit Keamanan','Kepala Unit Transportasi','Kepala Unit Pramu Kantor','Kepala Unit Logistik','Kepala Unit Sanitasi','Kepala Unit IPSRS']
            ]
        ];
        $bawahanAdmin=[
            'status_manager'=>['approved:Direktur','approved:bySistem'],
            'status_admin'=>['pending','approved:bySistem'],
            'unit'=>['Pelayanan Medis','Bagian Penunjang Medis','Administrasi Umum & Keuangan','Teknologi Informasi','Kesekretariatan','Humas & Pemasaran'],
            'jabatan'=>['Kepala Bagian Pelayanan Medis','Kepala Bagian Penunjang Medis','Kepala Bagian Administrasi Umum & Keuangan','Kepala Unit Kesekretariatan','Kepala Unit Humas & Pemasaran']
        ];
        $karyawan =[
            'status_manager'=>['approved:Kepala Bagian Pelayanan Medis','approved:Kepala Bagian Penunjang Medis','approved:Kepala Bagian Administrasi Umum & Keuangan','approved:bySistem'],
            'status_admin'=>['pending','approved:bySistem'],
            'unit'=>['Pelayanan Medis','Bagian Penunjang Medis','Administrasi Umum & Keuangan','Rawat Jalan & Home Care','Rawat Inap Lantai 2','Rawat Inap Lantai 3','Kamar Operasi & CSSD','Maternal & Perinatal','Hemodialisa','ICU','IGD','IGD & Klinik Umum','Keperawatan','Penunjang Medis','Laboratorium','Radiologi','Gizi','Rekam Medis','Laundry','Pendaftaran','Farmasi','Rehabilitasi Medis','Keuangan & Akuntansi','SDM','Umum','Keuangan','Akuntansi','Kasir','Casemix','Kepegawaian & Diklat','Keamanan','Transportasi','Pramu Kantor','Logistik','Sanitasi','IPSRS','Teknologi Informasi','Humas & Pemasaran','Kesekretariatan'],
            'jabatan'=>['karyawan']
        ];
        $bawahanKabag=[
            'status_manager'=>['approved:Kepala Bagian Pelayanan Medis','approved:Direktur','approved:Kepala Bagian Penunjang Medis','approved:Kepala Bagian Administrasi Umum & Keuangan','approved:bySistem'],
            'status_admin'=>['pending','approved:bySistem'],
            'unit'=>['IGD & Klinik Umum','Keperawatan','Penunjang Medis','Keuangan & Akuntansi','SDM','Umum','Rawat Jalan & Home Care','Rawat Inap Lantai 2','Rawat Inap Lantai 3','Kamar Operasi & CSSD','Maternal & Perinatal','Hemodialisa','ICU','IGD','Laboratorium','Radiologi','Gizi','Rekam Medis','Laundry','Pendaftaran','Farmasi','Rehabilitasi Medis','Keuangan','Akuntansi','Kasir','Casemix','Kepegawaian & Diklat','Keamanan','Transportasi','Pramu Kantor','Logistik','Sanitasi','IPSRS'],
            'jabatan'=>['Kepala Seksi IGD & Klinik Umum','Kepala Seksi Keperawatan','Kepala Seksi Penunjang Medis','Kepala Seksi Keuangan & Akuntansi','Kepala Seksi SDM','Kepala Seksi Umum','Kepala Unit Rawat Jalan & Home Care','Kepala Unit Rawat Inap Lantai 2','Kepala Unit Rawat Inap Lantai 3','Kepala Unit Kamar Operasi & CSSD','Kepala Unit Maternal & Perinatal','Kepala Unit Hemodialisa','Kepala Unit ICU','Kepala Unit IGD','Kepala Unit Laboratorium','Kepala Unit Radiologi','Kepala Unit Gizi','Kepala Unit Rekam Medis','Kepala Unit Laundry','Kepala Unit Pendaftaran','Kepala Unit Farmasi','Kepala Unit Rehabilitasi Medis','Kepala Unit Keuangan','Kepala Unit Akuntansi','Kepala Unit Kasir','Kepala Unit Casemix','Kepala Unit Kepegawaian & Diklat','Kepala Unit Keamanan','Kepala Unit Transportasi','Kepala Unit Pramu Kantor','Kepala Unit Logistik','Kepala Unit Sanitasi','Kepala Unit IPSRS']
        ];
        
        //Cuti Pending
        if($jabatanUser == 'Kepala Unit Teknologi Informasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager','bawahan_langsung');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                //////////////////////// SORTING DATA AWAL \\\\\\\\\\\\\\\\\\\\\\\\\\\\
                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'tanggal_mulai';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);
                //////////////////////// SORTING DATA AWAL \\\\\\\\\\\\\\\\\\\\\\\\\\\\

                //Menyimpan data bawahan 2 tingkat yaitu Kabag
                $kabagQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($bawahanAdmin){
                    $q
                    ->whereIn('jabatan',$bawahanAdmin['jabatan'])
                    ->whereIn('unit',$bawahanAdmin['unit'])
                    ->whereIn('status_admin',$bawahanAdmin['status_admin']);
                    // ->whereIn('status_manager',$bawahanAdmin['status_manager']);
                });
                $kasiQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($bawahanKabag){
                    $q
                    ->whereNotIn('jabatan',['Kepala Unit Kepegawaian & Diklat','Kepala Unit Keamanan','Kepala Unit Transportasi','Kepala Unit Pramu Kantor'])
                    ->whereIn('jabatan',$bawahanKabag['jabatan'])
                    ->whereIn('unit',$bawahanKabag['unit'])
                    ->whereIn('status_admin',$bawahanKabag['status_admin']);
                    // ->whereIn('status_manager',$bawahanKabag['status_manager']);
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($karyawan) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $karyawan['unit'])
                    ->whereIn('status_admin',$karyawan['status_admin']);
                    // ->whereIn('status_manager',$karyawan['status_manager']);
                });
                
                $karyawanIT = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($karyawan) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', ['Teknologi Informasi'])
                    ->whereIn('status_admin',$karyawan['status_admin']);
                    // ->whereIn('status_manager',$karyawan['status_manager']);
                });
                
            
                if ($unitFilter) {
                    $kabagQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $kasiQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanIT->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\
                if ($jenisFilter) {
                    $kabagQuery->where('jenis_cuti', $jenisFilter);
                    $kasiQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanIT->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $kabagQuery->whereIn('status_manager',$bawahanAdmin['status_manager']);
                    $kasiQuery->whereIn('status_manager',$bawahanKabag['status_manager']);
                    $karyawanQuery->whereIn('status_manager',$karyawan['status_manager']);
                    $karyawanIT->whereIn('status_manager',['pending','approved:bySistem']);
                } else {
                    $kabagQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $kasiQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanIT->where('status_manager','LIKE', '%'.$status.'%');
                }

                

                if ($tanggalMulaiFilter) {
                    $kabagQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $kasiQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanIT->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $kabagQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $kasiQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanIT->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }

                
                /////////////////// SORTING DATA \\\\\\\\\\\\\\\\\\\\\
                // Gabungkan query dan terapkan sorting
                $combinedQuery = $kabagQuery->union($kasiQuery)->union($karyawanQuery)->union($karyawanIT);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection)
                        ;
                }
            
                $kabagQuery=$kabagQuery->union($kasiQuery);
                $pendingCuti = $kabagQuery->union($karyawanQuery)->union($karyawanIT)
                ->paginate(10)
                ->appends(request()->query());
            }
        }
        elseif($jabatanUser == 'Kepala Seksi SDM'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];

                $this->updateCutiStatus();
                // dd($config);
                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager','bawahan_langsung');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                //////////////////////// SORTING DATA AWAL \\\\\\\\\\\\\\\\\\\\\\\\\\\\
                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'tanggal_mulai';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);
                //////////////////////// SORTING DATA AWAL \\\\\\\\\\\\\\\\\\\\\\\\\\\\


                //Menyimpan data bawahan langsung
                $kabagQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($bawahanAdmin){
                    $q
                    ->whereIn('jabatan',$bawahanAdmin['jabatan'])
                    ->whereIn('unit',$bawahanAdmin['unit'])
                    ->whereIn('status_admin',$bawahanAdmin['status_admin'])
                    // ->orWhereNull('validitas_suket')
                    ;
                    // ->whereIn('status_manager',$bawahanAdmin['status_manager']);
                });
                $kasiQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($bawahanKabag){
                    $q
                    ->whereIn('jabatan',$bawahanKabag['jabatan'])
                    ->whereIn('unit',$bawahanKabag['unit'])
                    ->whereIn('status_admin',$bawahanKabag['status_admin'])
                    // ->orWhereNull('validitas_suket')
                    ;
                    // ->whereIn('status_manager',$bawahanKabag['status_manager']);
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($karyawan) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $karyawan['unit'])
                    ->whereIn('status_admin',$karyawan['status_admin'])
                    // ->orWhereNull('validitas_suket')
                    ;
                    // ->whereIn('status_manager',$karyawan['status_manager']);
                });

                $karyawanIT = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($karyawan) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', ['Teknologi Informasi'])
                    ->whereIn('status_admin',$karyawan['status_admin'])
                    // ->orWhereNull('validitas_suket')
                    ;
                    // ->whereIn('status_manager',$karyawan['status_manager']);
                });
                
                if ($unitFilter) {
                    $kabagQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $kasiQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanIT->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\
                if ($jenisFilter) {
                    $kabagQuery->where('jenis_cuti', $jenisFilter);
                    $kasiQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanIT->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $kabagQuery->whereIn('status_manager',$bawahanAdmin['status_manager']);
                    $kasiQuery->whereIn('status_manager',$bawahanKabag['status_manager']);
                    $karyawanQuery->whereIn('status_manager',$karyawan['status_manager']);
                    $karyawanIT->whereIn('status_manager',['approved:Kepala Unit Teknologi Informasi','approved:bySistem']);
                } else {
                    $kabagQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $kasiQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanIT->where('status_manager','LIKE', '%'.$status.'%');
                }

                if ($tanggalMulaiFilter) {
                    $kabagQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $kasiQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanIT->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $kabagQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $kasiQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanIT->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }

                
                /////////////////// SORTING DATA \\\\\\\\\\\\\\\\\\\\\
                // Gabungkan query dan terapkan sorting
                $combinedQuery = $kabagQuery->union($kasiQuery)->union($karyawanQuery)->union($karyawanIT);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection)
                        ;
                }
            
                $kabagQuery=$kabagQuery->union($kasiQuery);
                $pendingCuti = $kabagQuery->union($karyawanQuery)->union($karyawanIT)
                ->paginate(10)
                ->appends(request()->query());
                // $pendingCuti->appends(request()->query());
            }
        }
        
        if($jabatanUser == 'Kepala Unit Teknologi Informasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu kabag
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($bawahanAdmin) {
                    $q->whereIn('jabatan', $bawahanAdmin['jabatan'])
                    ->whereIn('unit', $bawahanAdmin['unit'])
                    ->whereIn('status_admin', ['approved:Admin','approved:bySistem'])
                    ->whereIn('status_manager',$bawahanAdmin['status_manager'])
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanKabag){
                    $q
                    ->whereIn('jabatan',$bawahanKabag['jabatan'])
                    ->whereIn('unit',$bawahanKabag['unit'])
                    ->whereIn('status_admin',['approved:Admin','approved:bySistem'])
                    ->whereIn('status_manager',$bawahanKabag['status_manager']);
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan_id',[1])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',['approved:Admin','approved:bySistem'])
                    ->whereIn('status_manager',$config['status_manager']);
                });
                $karyawanITquery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', ['Teknologi Informasi'])
                    ->whereIn('status_admin',['pending','approved:bySistem'])
                    ->whereIn('status_manager',['approved:Kepala Unit Teknologi Informasi','approved:bySistem']);
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiApproved = $kasiQuery->union($karyawanQuery)->union($karyawanITquery)
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi SDM'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu kabag
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($bawahanAdmin) {
                    $q->whereIn('jabatan', $bawahanAdmin['jabatan'])
                    ->whereIn('unit', $bawahanAdmin['unit'])
                    ->whereIn('status_admin', ['approved:Admin','approved:bySistem'])
                    ->whereIn('status_manager',$bawahanAdmin['status_manager'])
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanKabag){
                    $q
                    ->whereIn('jabatan',$bawahanKabag['jabatan'])
                    ->whereIn('unit',$bawahanKabag['unit'])
                    ->whereIn('status_admin',['approved:Admin','approved:bySistem'])
                    ->whereIn('status_manager',$bawahanKabag['status_manager']);
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',['approved:Admin','approved:bySistem'])
                    ->whereIn('status_manager',$config['status_manager']);
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiApproved = $kasiQuery->union($karyawanQuery)
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
            }
        }

        if($jabatanUser == 'Kepala Unit Teknologi Informasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kabag
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($bawahanAdmin) {
                    $q->whereIn('jabatan', $bawahanAdmin['jabatan'])
                    ->whereIn('unit', $bawahanAdmin['unit'])
                    ->whereIn('status_admin', ['rejected:Admin'])
                    ->whereIn('status_manager',$bawahanAdmin['status_manager'])
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanKabag){
                    $q
                    ->whereIn('jabatan',$bawahanKabag['jabatan'])
                    ->whereIn('unit',$bawahanKabag['unit'])
                    ->whereIn('status_admin',['rejected:Admin'])
                    ->whereIn('status_manager',$bawahanKabag['status_manager']);
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan_id', [1])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',['rejected:Admin'])
                    ->whereIn('status_manager',$config['status_manager']);
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiRejected = $kasiQuery->union($karyawanQuery)
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi SDM'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kabag
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($bawahanAdmin) {
                    $q->whereIn('jabatan', $bawahanAdmin['jabatan'])
                    ->whereIn('unit', $bawahanAdmin['unit'])
                    ->whereIn('status_admin', ['rejected:Admin'])
                    ->whereIn('status_manager',$bawahanAdmin['status_manager'])
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanKabag){
                    $q
                    ->whereIn('jabatan',$bawahanKabag['jabatan'])
                    ->whereIn('unit',$bawahanKabag['unit'])
                    ->whereIn('status_admin',['rejected:Admin'])
                    ->whereIn('status_manager',$bawahanKabag['status_manager']);
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',['rejected:Admin'])
                    ->whereIn('status_manager',$config['status_manager']);
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiRejected = $kasiQuery->union($karyawanQuery)
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
            }
        }
        $filterDataManager = ['Kepala Seksi SDM','Kepala Unit Teknologi Informasi'];

        $units = Manager::pluck('unit')
        ->union(Karyawan::pluck('unit'))
        ->merge(Admin::pluck('unit'))
        ->toArray();
        return view('persetujuan', compact('pendingCuti','cutiApproved','cutiRejected','role','jabatanUser','units','filterDataManager'));
    }
    public function prosesCuti(Request $request,$id){   
        $user=Auth::user();
      
        $admin_id = $user->admin->id;
        $prosescuti = Cuti::findOrFail($id);
        $ttdAdmin = Admin::where('id',$admin_id)->first()->ttd ?? null;
        $today = now()->toDateString();

        $request->validate([
            'status_admin' => 'required_if:jenis_cuti,cuti_sakit,cuti_lainnya|in:approved,rejected,pending',
            'alasan_penolakan' => 'nullable|string|required_if:status_admin,rejected',
        ]);

        if(!$user->admin->ttd){
            return redirect()->back()->with('no changes','Sebelum anda menyetujui cuti, anda harus mengupload tanda tangan digital di halaman profile!');
        }
        else {
            if($user->admin->jabatan === 'Kepala Seksi SDM'){
                
                if($request->status_admin === 'approved'){
                    if($prosescuti->manager){
                        $sisaCuti = $prosescuti->manager->sisa_cuti;
                        $jumlahHari = $prosescuti->jumlah_hari;
                        $sisaCutiSebelumnya = $prosescuti->manager->sisa_cuti_sebelumnya;
                        if($prosescuti->jenis_cuti === 'cuti_melahirkan'){
                            $prosescuti->update([
                                'status_admin'=>'approved:Admin',
                                'ttd_admin'=>$ttdAdmin,
                                'apr_admin_id'=>$admin_id,
                                'tanggal_disetujui'=>$today
                            ]);
                            // event(new CutiDisetujui($prosescuti,'admin'));
                            return redirect()->route('admin.email.to.manager',$prosescuti->id)->with('approve success','Cuti melahirkan telah disetujui');
                        }
                        

                        if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                            return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                        }

                        if($sisaCutiSebelumnya >= $jumlahHari){
                            $prosescuti->update([
                                'status_admin' => 'approved:Admin',
                                'ttd_admin'=> $ttdAdmin,
                                'apr_admin_id'=>$admin_id,
                                'tanggal_disetujui'=>$today,
                                
                            ]);
                            
                            $prosescuti->manager->update([
                                'sisa_cuti_sebelumnya' => $sisaCutiSebelumnya - $jumlahHari,
                            ]);
                            $prosescuti->manager->refresh();
                            // event(new CutiDisetujui($prosescuti,'admin'));
                            \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                            return redirect()->route('admin.email.to.manager',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                        }else{
                            $jumlahHari = $jumlahHari - $sisaCutiSebelumnya;
                            $prosescuti->update([
                                'status_admin'=>'approved:Admin',
                                'ttd_admin'=>$ttdAdmin,
                                'apr_admin_id'=>$admin_id,
                                'tanggal_disetujui'=>$today
                            ]);
                            
                            $prosescuti->manager->update([
                                'sisa_cuti_sebelumnya' => 0,
                                'sisa_cuti'=>$sisaCuti - $jumlahHari
                            ]);
                            $prosescuti->manager->refresh();
                            // event(new CutiDisetujui($prosescuti,'admin'));
                            \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                            return redirect()->route('admin.email.to.manager',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                        }
                        // return redirect()->back()->with('approve success','Cuti telah berhasil disetujui!');
                    }
                    elseif($prosescuti->karyawan){
                        $sisaCuti = $prosescuti->karyawan->sisa_cuti;
                        $sisaCutiSebelumnya = $prosescuti->karyawan->sisa_cuti_sebelumnya;
                        $jumlahHari = $prosescuti->jumlah_hari;
                        if($prosescuti->jenis_cuti === 'cuti_melahirkan'){
                            $prosescuti->update([
                                'status_admin'=>'approved:Admin',
                                'ttd_admin'=>$ttdAdmin,
                                'apr_admin_id'=>$admin_id,
                                'tanggal_disetujui'=>$today
                            ]);
                            // event(new CutiDisetujui($prosescuti,'admin'));
                            return redirect()->route('admin.email.to.karyawan',$prosescuti->id)->with('approve success','Cuti melahirkan telah disetujui');
                        }
                        else{

                            if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                                return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                            }
    
                            if($sisaCutiSebelumnya >= $jumlahHari){
                                $prosescuti->update([
                                    'status_admin' => 'approved:Admin',
                                    'ttd_admin'=> $ttdAdmin,
                                    'apr_admin_id'=>$admin_id,
                                    'tanggal_disetujui'=>$today
                                ]);
                                
                                $prosescuti->karyawan->update([
                                    'sisa_cuti_sebelumnya' => $sisaCutiSebelumnya - $jumlahHari,
                                ]);
                                $prosescuti->karyawan->refresh();
                                return redirect()->route('admin.email.to.karyawan',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                                // event(new CutiDisetujui($prosescuti,'admin'));
                            }else{
                                $jumlahHari = $jumlahHari - $sisaCutiSebelumnya;
                                $prosescuti->update([
                                    'status_admin'=>'approved:Admin',
                                    'ttd_admin'=>$ttdAdmin,
                                    'apr_admin_id'=>$admin_id,
                                    'tanggal_disetujui'=>$today
                                ]);
                                
                                $prosescuti->karyawan->update([
                                    'sisa_cuti_sebelumnya' => 0,
                                    'sisa_cuti'=>$sisaCuti - $jumlahHari
                                ]);
                                $prosescuti->karyawan->refresh();
                                return redirect()->route('admin.email.to.karyawan',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                                // event(new CutiDisetujui($prosescuti,'admin'));
                            }
                        }

                        // return redirect()->back()->with('approve success','Cuti telah berhasil disetujui');
                    }
                }
                
                elseif($request->status_admin === 'rejected'){
                        $prosescuti->update([
                            'status_admin' => 'rejected:Admin',
                            'alasan_penolakan' => $request->alasan_penolakan,
                        ]);
                        return redirect()->back()->with('approve success', 'Cuti telah ditolak.');
                }
                elseif($request->status_admin === 'pending'){
                    $prosescuti->update([
                        'status_manager' => 'pending',
                        'status_admin' => 'pending',
                        'alasan_penolakan' => '',
                        'apr_admin_id'=>null,
                        'ttd_admin'=>null
                    ]);
                    
                    return redirect()->back()->with('approve success','Cuti telah di update : pending');
                }
                if($request->validitas_suket === 'valid' || $request->validitas_suket === 'tdk_valid'){
                    if($prosescuti->manager){
                        $sisaCuti = $prosescuti->manager->sisa_cuti;
                        $jumlahHari = $prosescuti->jumlah_hari;
                        $sisaCutiSebelumnya = $prosescuti->manager->sisa_cuti_sebelumnya;
                        
                        if($prosescuti->jenis_cuti === 'cuti_sakit'){
                            if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                                return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                            }
                            if($sisaCutiSebelumnya >= $jumlahHari){
                                $prosescuti->update([
                                    'ttd_admin'=> $ttdAdmin,
                                    'apr_admin_id'=>$admin_id,
                                    'tanggal_disetujui'=>$today,
                                    'validitas_suket' => $request->validitas_suket
                                ]);
                                
                                
                                // event(new CutiDisetujui($prosescuti,'admin'));
                                \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                                return redirect()->route('admin.email.to.manager',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                            }else{
                                $jumlahHari = $jumlahHari - $sisaCutiSebelumnya;
                                $prosescuti->update([
                                    'ttd_admin'=>$ttdAdmin,
                                    'apr_admin_id'=>$admin_id,
                                    'tanggal_disetujui'=>$today,
                                    'validitas_suket' => $request->validitas_suket
                                ]);
                                
                                
                                \Log::debug('Form Data:', $request->all()); // Cek di storage/logs/laravel.log
                                // event(new CutiDisetujui($prosescuti,'admin'));
                                \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                                return redirect()->route('admin.email.to.manager',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                            }
                        }
                        elseif($prosescuti->jenis_cuti === 'cuti_lainnya'){
                            if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                                return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                            }
                            if($sisaCutiSebelumnya >= $jumlahHari){
                                $prosescuti->update([
                                    'ttd_admin'=> $ttdAdmin,
                                    'apr_admin_id'=>$admin_id,
                                    'tanggal_disetujui'=>$today,
                                    'validitas_suket' => $request->validitas_suket
                                ]);
                                
                               
                                // event(new CutiDisetujui($prosescuti,'admin'));
                                \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                                return redirect()->route('admin.email.to.manager',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                            }else{
                                $jumlahHari = $jumlahHari - $sisaCutiSebelumnya;
                                $prosescuti->update([
                                    'ttd_admin'=>$ttdAdmin,
                                    'apr_admin_id'=>$admin_id,
                                    'tanggal_disetujui'=>$today,
                                    'validitas_suket' => $request->validitas_suket
                                ]);
                                
                                
                                // event(new CutiDisetujui($prosescuti,'admin'));
                                \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                                return redirect()->route('admin.email.to.manager',$prosescuti->id)->with('approve success', 'Cuti karyawan telah disetujui');
                            }
                        }

                        // return redirect()->back()->with('approve success','Cuti telah berhasil disetujui!');
                    }
                    elseif($prosescuti->karyawan){
                        $sisaCuti = $prosescuti->karyawan->sisa_cuti;
                        $sisaCutiSebelumnya = $prosescuti->karyawan->sisa_cuti_sebelumnya;
                        $jumlahHari = $prosescuti->jumlah_hari;
                        
                        if($prosescuti->jenis_cuti === 'cuti_sakit'){
                            if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                                return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                            }
                            if($sisaCutiSebelumnya >= $jumlahHari){
                                $prosescuti->update([
                                    'ttd_admin'=> $ttdAdmin,
                                    'apr_admin_id'=>$admin_id,
                                    'tanggal_disetujui'=>$today,
                                    'validitas_suket' => $request->validitas_suket,
                                    'alasan_penolakan' => $request->alasan_penolakan ?? null,
                                ]);
                                
                                
                                // dd($prosescuti);
                                // event(new CutiDisetujui($prosescuti,'admin'));
                                \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                                return redirect()->route('admin.email.to.karyawan',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                            }else{
                                
                                $jumlahHari = $jumlahHari - $sisaCutiSebelumnya;
                                $prosescuti->update([
                                    'ttd_admin'=>$ttdAdmin,
                                    'apr_admin_id'=>$admin_id,
                                    'tanggal_disetujui'=>$today,
                                    'validitas_suket' => $request->validitas_suket,
                                    'alasan_penolakan' => $request->alasan_penolakan ?? null
                                ]);
                                
                                
                                // dd($prosescuti);
                                \Log::debug('Form Data:', $request->all()); // Cek di storage/logs/laravel.log
                                // event(new CutiDisetujui($prosescuti,'admin'));
                                \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                                return redirect()->route('admin.email.to.karyawan', $prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                            }
                        }
                        elseif($prosescuti->jenis_cuti === 'cuti_lainnya'){
                            if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                                return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                            }
                            if($sisaCutiSebelumnya >= $jumlahHari){
                            $prosescuti->update([
                                'ttd_admin'=> $ttdAdmin,
                                'apr_admin_id'=>$admin_id,
                                'tanggal_disetujui'=>$today,
                                'validitas_suket' => $request->validitas_suket
                            ]);
                            
                            
                            // event(new CutiDisetujui($prosescuti,'admin'));
                            \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                            return redirect()->route('admin.email.to.karyawan',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                            }else{
                                $jumlahHari = $jumlahHari - $sisaCutiSebelumnya;
                                $prosescuti->update([
                                    'ttd_admin'=>$ttdAdmin,
                                    'apr_admin_id'=>$admin_id,
                                    'tanggal_disetujui'=>$today,
                                    'validitas_suket' => $request->validitas_suket
                                ]);
                                
                                
                                // event(new CutiDisetujui($prosescuti,'admin'));
                                \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $prosescuti->id);
                                return redirect()->route('admin.email.to.karyawan',$prosescuti->id)->with('approve success','Cuti karyawan telah disetujui');
                            }
                        }
                        

                        // return redirect()->back()->with('approve success','Cuti telah berhasil disetujui');
                    }
                }
                return redirect()->back()->with('no changes','tidak ada perubahan');
                
            }
            
            elseif($user->admin->jabatan === 'Kepala Unit Teknologi Informasi'){
                if($request->status_admin === 'approved'){
                    if($prosescuti->manager){
                        $sisaCuti = $prosescuti->manager->sisa_cuti;
                        $jumlahHari = $prosescuti->jumlah_hari;
                        $sisaCutiSebelumnya = $prosescuti->manager->sisa_cuti_sebelumnya;
                        if($prosescuti->jenis_cuti === 'cuti_melahirkan'){
                            $prosescuti->update([
                                'status_admin'=>'approved:Admin',
                                'ttd_admin'=>$ttdAdmin,
                                'apr_admin_id'=>$admin_id,
                                'tanggal_disetujui'=>$today
                            ]);
                            // event(new CutiDisetujui($prosescuti,'admin'));
                            return redirect()->back()->with('approve success','Cuti melahirkan telah disetujui');
                        }

                        if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                            return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                        }

                        if($sisaCutiSebelumnya >= $jumlahHari){
                            $prosescuti->update([
                                'status_admin' => 'approved:Admin',
                                'ttd_admin'=> $ttdAdmin,
                                'apr_admin_id'=>$admin_id,
                                'tanggal_disetujui'=>$today
                            ]);
                            
                            $prosescuti->manager->update([
                                'sisa_cuti_sebelumnya' => $sisaCutiSebelumnya - $jumlahHari,
                            ]);
                            $prosescuti->manager->refresh();
                            // event(new CutiDisetujui($prosescuti,'admin'));
                        }else{
                            $jumlahHari = $jumlahHari - $sisaCutiSebelumnya;
                            $prosescuti->update([
                                'status_admin'=>'approved:Admin',
                                'ttd_admin'=>$ttdAdmin,
                                'apr_admin_id'=>$admin_id,
                                'tanggal_disetujui'=>$today
                            ]);
                            
                            $prosescuti->manager->update([
                                'sisa_cuti_sebelumnya' => 0,
                                'sisa_cuti'=>$sisaCuti - $jumlahHari
                            ]);
                            $prosescuti->manager->refresh();
                            // event(new CutiDisetujui($prosescuti,'admin'));
                        }
                        return redirect()->back()->with('approve success','Cuti telah berhasil disetujui!');
                    }
                    elseif($prosescuti->karyawan){
                        $sisaCuti = $prosescuti->karyawan->sisa_cuti;
                        $sisaCutiSebelumnya = $prosescuti->karyawan->sisa_cuti_sebelumnya;
                        $jumlahHari = $prosescuti->jumlah_hari;
                            if($prosescuti->karyawan->unit === 'Teknologi Informasi'){
                                if($prosescuti->jenis_cuti === 'cuti_melahirkan'){
                                    $prosescuti->update([
                                        'status_manager'=>'approved:Kepala Unit Teknologi Informasi',
                                        'ttd_atasan_admin'=>$ttdAdmin,
                                        'apr_atasan_admin_id'=>$admin_id,
                                    ]);
                                    // event(new CutiDisetujui($prosescuti,'admin'));
                                    return redirect()->back()->with('approve success','Cuti melahirkan telah disetujui');
                                }else{

                                    if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                                        return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                                    }else
                                    {
                                        $prosescuti->update([
                                            'status_manager' => 'approved:Kepala Unit Teknologi Informasi',
                                            'ttd_atasan_admin'=> $ttdAdmin,
                                            'apr_atasan_admin_id'=>$admin_id,
                                        ]);
                                    }
                                }

                                return redirect()->back()->with('approve success','Cuti karyawan berhasil disetujui');
                            }
                            else {
                                if($prosescuti->jenis_cuti === 'cuti_melahirkan'){
                                    $prosescuti->update([
                                        'status_admin'=>'approved:Admin',
                                        'ttd_admin'=>$ttdAdmin,
                                        'apr_admin_id'=>$admin_id,
                                        'tanggal_disetujui'=>$today
                                    ]);
                                    // event(new CutiDisetujui($prosescuti,'admin'));
                                    return redirect()->back()->with('approve success','Cuti melahirkan telah disetujui');
                                }
        
                                if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                                    return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                                }
        
                                if($sisaCutiSebelumnya >= $jumlahHari){
                                    $prosescuti->update([
                                        'status_admin' => 'approved:Admin',
                                        'ttd_admin'=> $ttdAdmin,
                                        'apr_admin_id'=>$admin_id,
                                        'tanggal_disetujui'=>$today
                                    ]);
                                    
                                    $prosescuti->karyawan->update([
                                        'sisa_cuti_sebelumnya' => $sisaCutiSebelumnya - $jumlahHari,
                                    ]);
                                    $prosescuti->karyawan->refresh();
                                    // event(new CutiDisetujui($prosescuti,'admin'));
                                }else{
                                    $jumlahHari = $jumlahHari - $sisaCutiSebelumnya;
                                    $prosescuti->update([
                                        'status_admin'=>'approved:Admin',
                                        'ttd_admin'=>$ttdAdmin,
                                        'apr_admin_id'=>$admin_id,
                                        'tanggal_disetujui'=>$today
                                    ]);
                                    
                                    $prosescuti->karyawan->update([
                                        'sisa_cuti_sebelumnya' => 0,
                                        'sisa_cuti'=>$sisaCuti - $jumlahHari
                                    ]);
                                    $prosescuti->karyawan->refresh();
                                    // event(new CutiDisetujui($prosescuti,'admin'));
                                }
                                return redirect()->back()->with('approve success','Cuti telah berhasil disetujui');
                            }
                    }
                }
                elseif($request->status_admin === 'rejected'){
                    if($prosescuti->karyawan){
                        if($prosescuti->karyawan->unit === 'Teknologi Informasi'){
                            $prosescuti->update([
                            'status_manager' => 'rejected:Kepala Unit Teknologi Informasi',
                            'alasan_penolakan' => $request->alasan_penolakan,
                            'apr_atasan_admin_id' => null,
                            'ttd_atasan_admin'=>null
                        ]);
                        return redirect()->back()->with('approve success', 'Cuti telah ditolak.');
                        }else{
                            $prosescuti->update([
                            'status_admin' => 'rejected:Admin',
                            'alasan_penolakan' => $request->alasan_penolakan,
                            'apr_admin_id' => null,
                            'ttd_admin'=>null
                        ]);
                        return redirect()->back()->with('approve success', 'Cuti telah ditolak.');
                        }
                    }else{
                        $prosescuti->update([
                            'status_admin' => 'rejected:Admin',
                            'alasan_penolakan' => $request->alasan_penolakan,
                            'apr_admin_id' => null,
                            'ttd_admin'=>null,
                            'apr_atasan_id'=>null,
                            'ttd_atasan'=>null
                        ]);
                        return redirect()->back()->with('approve success', 'Cuti telah ditolak.');
                    }
                }
                elseif($request->status_admin === 'pending'){
                    if($prosescuti->karyawan){
                        if($prosescuti->karyawan->unit === 'Teknologi Informasi'){
                            $prosescuti->update([
                            'status_manager' => 'pending',
                            'status_admin' => 'pending',
                            'alasan_penolakan' => '',
                            'apr_atasan_admin_id'=>null,
                            'ttd_atasan_admin'=>null
                            ]);
                        }
                        $prosescuti->update([
                            'status_admin' => 'pending',
                            'alasan_penolakan' => '',
                            'apr_admin_id'=>null,
                            'ttd_admin'=>null
                        ]);
                    }else{
                        $prosescuti->update([
                            'status_admin' => 'pending',
                            'alasan_penolakan' => '',
                            'apr_admin_id'=>null,
                            'ttd_admin'=>null
                        ]);
                    }
                    
                    return redirect()->back()->with('approve success','Cuti telah di update : pending');
                }
                else{
                    return redirect()->back()->with('no changes','tidak ada perubahan');
                }
            }
            
    }
    }
    public function status(Request $request){
        $adminId = Auth::user()->admin->id;
        $role = Auth::user()->role;
        $aliasJabatan = Auth::user()->admin->jabatan;
        $statusManager = Cuti ::where('admin_id',$adminId)->pluck('status_manager');
        $statusAdmin = Cuti ::where('admin_id',$adminId)->pluck('status_admin');

        $statusCuti = Cuti::with('admin')
        ->whereHas('admin')
        ->where('admin_id',$adminId)
        ->whereIn('status_manager',$statusManager)
        ->whereIn('status_admin',$statusAdmin)
        ->orderBy('tanggal_pengajuan','desc')
        ->paginate(15);
        if ($request->has('action') && $request->action == 'detail') {
            return $this->detailCuti($request->id);
        }
        
        return view('statusCuti',compact('statusCuti','role','aliasJabatan'));
    }

    public function view_pdf($id){
        $cuti = Cuti::with('admin')->findOrFail($id);

        $user=Auth::user();

        $ttdPath = $user->admin->ttd;

        $ttdBase64 = null;
            if (Storage::disk('public')->exists($ttdPath)) {
                $ttdData = Storage::disk('public')->get($ttdPath);
                $ttdBase64 = 'data:image/png;base64,' . base64_encode($ttdData);
            }else{
                $ttdBase64 ="tidak ditemukan";
            }
 
        return view('view-pdf',['cuti' =>$cuti, 'ttdBase64' => $ttdBase64, 'user' => $user]);
    }
    public function download_pdf($id){
        $cuti = Cuti::with('admin','approval_kabag','approval_direktur')->findOrFail($id);
        $user = Auth::user();
        $alasanPenolakan = $cuti->alasan_penolakan;
        
        $ttdPath = $user->admin->ttd;
        $ttdKanit = $cuti->approval_atasan->ttd ?? null;
        $ttdKasi = $cuti->approval_kasi->ttd ?? null;
        $ttdKabag = $cuti->approval_kabag->ttd ?? null;
        $ttdAdmin = $cuti->approval_admin->ttd ?? null;
        $ttdDirektur = $cuti->approval_direktur->ttd ?? null;
        $suket = $cuti->surat_keterangan ?? null;

        $ttdBase64 = null;
            if (Storage::disk('public')->exists($ttdPath)) {
                $ttdData = Storage::disk('public')->get($ttdPath);
                $ttdBase64 = 'data:image/png;base64,' . base64_encode($ttdData);
            }else{
                $ttdBase64 ="tidak ditemukan";
            }

        if($ttdKabag != null){
            $ttd_kabag=null;
            $ttd_kabag = Storage::disk('public')->exists($ttdKabag) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($ttdKabag))
                : "tidak ditemukan";
        }else{
            $ttd_kabag=null;
        }

        if($ttdAdmin != null){
            $ttd_admin=null;
            $ttd_admin = Storage::disk('public')->exists($ttdAdmin) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($ttdAdmin))
                : "tidak ditemukan";
        }else{
            $ttd_admin=null;
        }

        if($ttdDirektur != null){
            $ttd_direktur=null;
            $ttd_direktur = Storage::disk('public')->exists($ttdDirektur) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($ttdDirektur))
                : "tidak ditemukan";
        }else{
            $ttd_direktur=null;
        }
        if($suket != null){
            $surat_keterangan=null;
            $surat_keterangan = Storage::disk('public')->exists($suket) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($suket))
                : "tidak ditemukan";
        }else{
            $surat_keterangan=null;
        }

        $data = PDF::loadView('export-pdf', [
            'cuti' => $cuti,
            'ttd_pemohon' => $ttdBase64,
            'ttd_kabag' => $ttd_kabag,
            'ttd_direktur' => $ttd_direktur,
            'ttd_admin' => $ttd_admin,
            // 'surat_keterangan' => $surat_keterangan,
            'user' => $user,
            'alasanPenolakan' => $alasanPenolakan
        ]);
        $now = now();

        $data->setPaper('A4', 'portrait');
        $data->setOption([
        'isPhpEnabled' => true,
        'isRemoteEnabled' => true,
        'defaultFont' => 'dejavu sans',
        'isHtml5ParserEnabled' => true
        ]);

        // return view('export-pdf',['cuti' => $cuti, 'ttd_pemohon' =>$ttdBase64, 'user'=>$user]);
        return $data->download('Surat_Cuti_'.$cuti->admin->nama_lengkap.'_'.$now.'_'.$id.'.pdf');
    }

    public function downloadSuket($cutiId){
        $cuti = Cuti::with('karyawan','manager','admin')->findOrFail($cutiId);

        $suket = $cuti->surat_keterangan ?? null;

        if (!Storage::disk('public')->exists($suket)) {
            abort(404, 'File tidak ditemukan');
        }

        $mime = Storage::disk('public')->mimeType($suket);
        $allowedMimes = ['image/png', 'image/jpeg', 'image/jpg'];       

        if (!in_array($mime, $allowedMimes)) {
            abort(403, 'Tipe file tidak diizinkan');
        }
        // dd($mime, pathinfo($suket, PATHINFO_EXTENSION));
        
        $extension = pathinfo($suket, PATHINFO_EXTENSION);
        
        if($cuti->karyawan){
            $filename = 'Surat_Keterangan_'.$cuti->karyawan->nama_lengkap.'_'.now()->format('Ymd_His').'.'.$extension;
        }
        elseif($cuti->manager){
            $filename = 'Surat_Keterangan_'.$cuti->manager->nama_lengkap.'_'.now()->format('Ymd_His').'.'.$extension;
        }
        elseif($cuti->admin){
            $filename = 'Surat_Keterangan_'.$cuti->admin->nama_lengkap.'_'.now()->format('Ymd_His').'.'.$extension;
        }

        return Storage::disk('public')->download($suket, $filename, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function detailCuti($id){
        $detail = Cuti::with(['admin','approval_direktur','approval_kabag'])->find($id);
        if (!$detail) {
            return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        }
    
        return response()->json([
            'status' => 'success',
            'data' => [
                'jenis_cuti' => $detail->jenis_cuti,
                'nama_lengkap' => $detail->admin->nama_lengkap,
                'unit' => $detail->admin->unit,
                'jabatan' => $detail->admin->jabatan,
                'no_pokok' => $detail->admin->no_pokok,
                'alamat' => $detail->admin->alamat,
                'tanggal_mulai' => $detail->tanggal_mulai,
                'tanggal_selesai' => $detail->tanggal_selesai,
                'tanggal_pengajuan' => $detail->tanggal_pengajuan,
                'tanggal_disetujui' => $detail->tanggal_disetujui,
                'jumlah_hari' => $detail->jumlah_hari,
                'alasan' => $detail->alasan,
                'sisa_cuti' => $detail->admin->sisa_cuti,
                'alasan_penolakan'=>$detail->alasan_penolakan,
                'ttd_pemohon'=>$detail->admin->ttd ?? '',
                'ttd_kabag'=>$detail->ttd_kabag ?? '',
                'nama_kabag'=>$detail->approval_kabag->nama_lengkap ?? '',
                'ttd_direktur'=>$detail->ttd_direktur ?? '',
                'nama_direktur'=>$detail->approval_direktur->nama_lengkap ?? '',
            ]
        ]);
    }
   
    public function logout(){
        auth::logout();
        return redirect()->route('login')->with('success','Anda berhasil logout');
    }

}
