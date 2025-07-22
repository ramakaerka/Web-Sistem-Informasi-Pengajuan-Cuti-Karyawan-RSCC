<?php

namespace App\Http\Controllers;

use App\Events\CutiDiajukan;
Use App\Models\Cuti;
Use App\Models\Manager;
Use App\Models\Karyawan;
Use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ManagerController extends Controller
{

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
        $userprofile=$user->manager;
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
            'foto' => 'required|image|mimes:png,jpg,jpeg|max:12000'
        ]);

        // dd($request->file('foto'));
        try {
            $path = $request->file('ttd')->store('ttd','public');
            $pathFoto = $request->file('foto')->store('foto','public');
        } catch (\Exception $e) {
            dd($e->getMessage()); // Tampilkan error spesifik
        }
        
        $manager = Manager::where('user_id', $user->id)->first();
        
        if($manager){
            if ($manager->ttd){
                Storage::delete($manager->ttd);
            }
            if ($manager->foto){
                Storage::delete($manager->foto);
            }
            $manager->update([
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
        Manager::create([
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
        return redirect()->route('manager.profile')->with('profile success','Data profile telah disimpan');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'signature' => 'required|string|starts_with:data:image/png;base64,'
        ]);
        $user = Auth::user();
        $manager = Manager::where('user_id', $user->id)->first();

         try {
            $imageData = str_replace('data:image/png;base64,', '', $request->signature);
            $imageData = str_replace(' ', '+', $imageData);
            $path = 'ttd/' . uniqid() . '.png';
            
            // dd($path);
            Storage::disk('public')->put($path, base64_decode($imageData));
            
            if($manager){
                if($manager->ttd){
                    Storage::delete($manager->ttd);
                    // Simpan ke database jika diperlukan
                    $manager->update([
                        'ttd' => $path
                    ]);
                }else{
                    Manager::create([
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
        $user=Auth::user();
        $role=$user->role;
        $userprofile=Manager::where('user_id',auth()->id())->first();
        $path = Auth::user()->manager->ttd;
        return view('profile',compact('role','userprofile','path'));
    }
    public function pengajuan(){
        $user=Auth::user();
        $role=$user->role;
        return view('pengajuan',compact('role','user'));
    }
    public function store(Request $request){
        $request->validate([
            'jenis_cuti' => [
                'required', 'string',
                function ($attribute, $value, $fail) use ($request) {
                    $user = Auth::user(); 
                    $gender = $user->manager->jenis_kelamin;

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
    
                if ($request->jenis_cuti != 'cuti_tahunan'){

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
        $hari_ini = new \DateTime();
        $hari_ini->setTime(0,0,0);
        $tanggal_mulai = new \Datetime($request->tanggal_mulai);
        $tanggal_selesai = new \Datetime($request->tanggal_selesai);
        $jumlah_hari = $tanggal_mulai->diff($tanggal_selesai)->days;

        $jenisCuti = $request->jenis_cuti;
        $user = Auth::user()->manager;
        $manager=$user;
        $unitUser = $user->unit;

        if(!$user->ttd)
        {
            return redirect()->back()->with('no changes','Sebelum mengajukan cuti, anda harus mengupload tanda tangan digital di halaman Profile');
        }
        else{
            if ($jenisCuti === 'cuti_melahirkan') {
                // Pastikan hanya wanita yang bisa mengajukan cuti melahirkan
                if ($manager->jenis_kelamin !== 'P') {
                    return redirect()->back()->with('no changes', 'Cuti Melahirkan hanya dapat diajukan oleh karyawan perempuan.');
                }
        
                // Cek apakah jumlah hari yang diajukan sesuai dengan aturan 90 hari cuti melahirkan
                if ($jumlah_hari > 90) {
                    return redirect()->back()->with('no changes', 'Cuti Melahirkan maksimal 30 hari.');
                }
        
                // Simpan pengajuan cuti tanpa mengurangi jatah cuti tahunan
               $cuti = Cuti::create([
                    'manager_id' => $manager->id,
                    'jenis_cuti' => $request->jenis_cuti,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'jumlah_hari' => $jumlah_hari,
                    'alasan' => $request->alasan,
                    'surat_keterangan' => $request->surat_keterangan ?? null,
                ]);
                // event(new CutiDiajukan($cuti, 'manager'));
                return redirect()->route('manager.email',$cuti->id)->with('pengajuan success', 'Pengajuan Cuti Melahirkan berhasil dilakukan, menunggu persetujuan atasan & admin.');
            }
            elseif ($jenisCuti === 'cuti_tahunan') {
                // Untuk cuti tahunan (multiple dates)
                $dates = json_decode($request->selected_dates_array);
                $jumlahHari = count($dates);
                $sisaCuti = $manager->sisa_cuti + $manager->sisa_cuti_sebelumnya;
                    
                if ($jumlahHari > $sisaCuti) {
                    return back()->withErrors([
                        'selected_dates_array' => "Jatah cuti tahunan Anda hanya {$sisaCuti} hari, tidak mencukupi untuk {$jumlahHari} hari cuti."
                    ]);
                }

                // Simpan setiap tanggal cuti
                foreach ($dates as $date) {
                    $Cuti = Cuti::create([
                        'manager_id' => $manager->id,
                        'jenis_cuti' => $jenisCuti,
                        'tanggal_mulai' => $date,
                        'tanggal_selesai' => $date, // Untuk cuti tahunan, tanggal selesai sama dengan tanggal mulai
                        'jumlah_hari' => 1,
                        'alasan' => $request->alasan,
                        'surat_keterangan' =>$request->surat_keterangan ?? null
                    ]);
                }
                return redirect()->route('manager.email',$Cuti->id)->with('pengajuan success', 'Pengajuan Cuti berhasil dilakukan, menunggu persetujuan atasan & admin.');
                // event(new CutiDiajukan($Cuti,'manager'));
            }
            elseif ($jenisCuti === 'cuti_sakit' || $jenisCuti === 'cuti_lainnya') {
                $dates = json_decode($request->selected_dates_array);
                $jumlahHari = count($dates);

                if($request->file('surat_keterangan')){
                    $pathSuket = $request->file('surat_keterangan')->store('suket','public');
                }
           
                $sisaCuti = $manager->sisa_cuti + $manager->sisa_cuti_sebelumnya;
                    
                if ($jumlahHari > $sisaCuti) {
                    return back()->withErrors([
                        'selected_dates_array' => "Jatah cuti sakit Anda hanya {$sisaCuti} hari, tidak mencukupi untuk {$jumlahHari} hari cuti."
                    ]);
                }

                if($jumlahHari > 3){
                    foreach($dates as $date){
                        $Cuti = Cuti::create([
                            'manager_id' => $manager->id,
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
                foreach($dates as $date){
                    $Cuti = Cuti::create([
                        'manager_id' => $manager->id,
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
                $Cuti->manager->update(['sisa_cuti' => $Cuti->manager->sisa_cuti - $jumlahHari]);
                $Cuti->manager->refresh();
              return redirect()->route('manager.email',$Cuti->id)->with('pengajuan success', 'Pengajuan Cuti berhasil dilakukan, menunggu persetujuan atasan & admin.');
                // event(new CutiDiajukan($Cuti,'manager'));
            }
            else{
                if (($manager->sisa_cuti + $manager->sisa_cuti_sebelumnya) < $jumlah_hari){
                return redirect()->back()->with('no changes','Sisa cuti tidak mencukupi');
                }
                if($tanggal_mulai >= $hari_ini){
                    $cuti = Cuti::whereHas('manager')
                    ->where('manager_id',Auth::user()->manager->id)
                    ->where('tanggal_mulai',$tgl_mulai)->first();

                    $unitCuti = Cuti::whereHas('manager', function ($q) use ($unitUser){
                        $q->where('unit',$unitUser);
                    })
                    ->where('tanggal_mulai',$tanggal_mulai)->count();

                    if($unitCuti >= 1){
                        return redirect()->back()->with('no changes','Unit anda telah mencapai batas maksimal pengajuan cuti pada tanggal tersebut. Silahkan mengajukan di tanggal lain');
                    }
                    else {
                        if($cuti){
                            return redirect()->back()->with('no changes','Anda sudah memiliki pengajuan cuti di tanggal ini!');
                        }
                        $Cuti = Cuti::create([
                            'manager_id' => $manager->id,
                            'jenis_cuti' => $request->jenis_cuti,
                            'tanggal_mulai' => $request->tanggal_mulai,
                            'tanggal_selesai' => $tanggal_selesai,
                            'jumlah_hari' => $jumlah_hari,
                            'alasan' => $request->alasan,
                            'surat_keterangan' => $request->surat_keterangan
                        ]);
                        // event(new CutiDiajukan($Cuti,'manager'));
                        \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $Cuti->id);
                        return redirect()->route('manager.email',$Cuti->id)->with('pengajuan success', 'Pengajuan Cuti berhasil dilakukan, menunggu persetujuan atasan & admin.');
                    }
                }
                else{
                    return redirect()->back()->with('no changes','Tanggal mulai harus hari ini atau setelah hari ini');
                }
            }
            // return redirect()->route('manager.status')->with('pengajuan success','Pengajuan cuti berhasil dilakukan');
            }
        }   
public function findAvailableApprover($jabatan)
{
    // Urutan hierarki jabatan dari yang terendah ke tertinggi
    $hierarchy = [
        'karyawan' => 'Kepala Unit',
        'Kepala Unit' => 'Kepala Seksi',
        'Kepala Seksi' => 'Kepala Bagian',
        'Kepala Bagian' => 'Direktur',
        // Tambahkan hierarki lainnya jika perlu
    ];

    // Jika jabatan tidak ada dalam hierarki, return null
    if (!isset($hierarchy[$jabatan])) {
        return null;
    }

    // Ambil jabatan atasan dari hierarki
    $atasanJabatan = $hierarchy[$jabatan];

    // Cek apakah atasan ini ada di database
    $atasan = Manager::where('jabatan', $atasanJabatan)->first();

    if ($atasan) {
        return $atasan; // Kembalikan atasan yang ditemukan
    }

    // Jika atasan ini tidak ada, cari atasan di level lebih tinggi
    return $this->findAvailableApprover($atasanJabatan);
}
    public function prosesCuti(Request $request,$id){
        $user=Auth::user();
        $manager_id = $user->manager->id;
        $ttdManager = Manager::where('id',$manager_id)->first()->ttd ?? null;
        $prosescuti = Cuti::with('manager','admin')->findOrFail($id);
        $today=now()->toDateString();
        

        $request->validate([
            'status_manager' => 'required|in:approved,rejected,pending',
            'alasan_penolakan' => 'nullable|string|required_if:status_manager,rejected',
        ]);

        if(!$user->manager->ttd){
            return redirect()->back()->with('no changes','Sebelum anda menyetujui cuti, anda harus mengupload tanda tangan digital di halaman profile!');
        }
        else {
            $jabatanManagers = Manager::pluck('jabatan')->toArray();

            // Mapping jabatan ke kolom tanda tangan dan ID
            $ttd_columns = [
                'Direktur' => ['ttd_direktur','apr_direktur_id','tanggal_disetujui'],
                'Kepala Seksi Keperawatan' => ['ttd_kasi', 'apr_kasi_id'],
                'Kepala Seksi IGD & Klinik Umum' => ['ttd_kasi', 'apr_kasi_id'],
                'Kepala Seksi Penunjang Medis' => ['ttd_kasi', 'apr_kasi_id'],
                'Kepala Seksi Keuangan & Akuntansi' => ['ttd_kasi', 'apr_kasi_id'],
                'Kepala Seksi SDM' => ['ttd_kasi', 'apr_kasi_id'],
                'Kepala Seksi Umum' => ['ttd_kasi', 'apr_kasi_id'],
                'Kepala Bagian Pelayanan Medis' => ['ttd_kabag', 'apr_kabag_id'],
                'Kepala Bagian Penunjang Medis' => ['ttd_kabag', 'apr_kabag_id'],
                'Kepala Bagian Administrasi Umum & Keuangan' => ['ttd_kabag', 'apr_kabag_id'],
                'Kepala Unit Rawat Jalan & Home Care' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Rawat Inap Lantai 2' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Rawat Inap Lantai 3' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit ICU' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit IGD' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Hemodialisa' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Kamar Operasi & CSSD' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Maternal & Perinatal' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Laboratorium' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Radiologi' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Gizi' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Rekam Medis' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Laundry' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Pendaftaran' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Farmasi' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Rehabilitasi Medis' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Keuangan' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Akuntansi' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Kasir' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Casemix' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Kepegawaian & Diklat' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Keamanan' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Transportasi' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Pramu Kantor' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Logistik' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit Sanitasi' => ['ttd_atasan', 'apr_atasan_id'],
                'Kepala Unit IPSRS' => ['ttd_atasan', 'apr_atasan_id'],
            ];
            $pending_map_kasi_keperawatan = [
                'Rawat Jalan & Home Care' => 'approved:Kepala Unit Rawat Jalan & Home Care',

            ];
            $pending_map_kasi_penunjang_medis = [
                'Laboratorium' => 'approved:Kepala Unit Laboratorium',
                'Radiologi' => 'approved:Kepala Unit Radiologi',
                'Gizi' => 'approved:Kepala Unit Gizi',
                'Rekam Medis' => 'approved:Kepala Unit Rekam Medis',
                'Laundry' => 'approved:Kepala Unit Laundry',
                'Pendaftaran' => 'approved:Kepala Unit Pendaftaran',
                'Farmasi' => 'approved:Kepala Unit Farmasi',
                'Rehabilitasi Medis' => 'approved:Kepala Unit Rehabilitasi Medis',
                'karyawan' => 'pending',

            ];

            $pending_map_kasi = [
                'Kepala Unit Rawat Jalan & Home Care' => 'pending',
                'Kepala Unit Rawat Inap Lantai 2' => 'pending',
                'Kepala Unit Rawat Inap Lantai 3' => 'pending',
                'Kepala Unit Kamar Operasi & CSSD' => 'pending',
                'Kepala Unit Maternal & Perinatal' => 'pending',
                'Kepala Unit Hemodialisa' => 'pending',
                'Kepala Unit ICU' => 'pending',
                'Kepala Unit IGD' => 'pending',

                'Kepala Unit Laboratorium' => 'pending',
                'Kepala Unit Radiologi' => 'pending',
                'Kepala Unit Gizi' => 'pending',
                'Kepala Unit Rekam Medis' => 'pending',
                'Kepala Unit Laundry' => 'pending',
                'Kepala Unit Pendaftaran' => 'pending',
                'Kepala Unit Farmasi' => 'pending',
                'Kepala Unit Rehabilitasi Medis' => 'pending',

                'Kepala Unit Keuangan' => 'pending',
                'Kepala Unit Akuntansi' => 'pending',
                'Kepala Unit Kasir' => 'pending',
                'Kepala Unit Casemix' => 'pending',

                'Kepala Unit Kepegawaian & Diklat' => 'pending',
                'Kepala Unit Keamanan' => 'pending',
                'Kepala Unit Transportasi' => 'pending',
                'Kepala Unit Pramu Kantor' => 'pending',

                'Kepala Unit Logistik' => 'pending',
                'Kepala Unit Sanitasi' => 'pending',
                'Kepala Unit IPSRS' => 'pending',
            ];
            
            $pending_map_kabag = [
                'Kepala Seksi Penunjang Medis' => 'pending',
                'Kepala Seksi Keperawatan' => 'pending',
                'Kepala Seksi IGD & Klinik Umum' => 'pending',
                'Kepala Seksi Keuangan & Akuntansi' => 'pending',
                'Kepala Seksi SDM' => 'pending',
                'Kepala Seksi Umum' => 'pending',
                
                'Kepala Unit Rawat Jalan & Home Care' => 'approved:Kepala Seksi Keperawatan',
                'Kepala Unit Rawat Inap Lantai 2' => 'approved:Kepala Seksi Keperawatan',
                'Kepala Unit Rawat Inap Lantai 3' => 'approved:Kepala Seksi Keperawatan',
                'Kepala Unit Kamar Operasi & CSSD' => 'approved:Kepala Seksi Keperawatan',
                'Kepala Unit Maternal & Perinatal' => 'approved:Kepala Seksi Keperawatan',
                'Kepala Unit Hemodialisa' => 'approved:Kepala Seksi Keperawatan',
                'Kepala Unit ICU' => 'approved:Kepala Seksi Keperawatan',
                'Kepala Unit IGD' => 'approved:Kepala Seksi Keperawatan',
                
                'Kepala Unit Laboratorium' => 'approved:Kepala Seksi Penunjang Medis',
                'Kepala Unit Radiologi' => 'approved:Kepala Seksi Penunjang Medis',
                'Kepala Unit Gizi' => 'approved:Kepala Seksi Penunjang Medis',
                'Kepala Unit Rekam Medis' => 'approved:Kepala Seksi Penunjang Medis',
                'Kepala Unit Laundry' => 'approved:Kepala Seksi Penunjang Medis',
                'Kepala Unit Pendaftaran' => 'approved:Kepala Seksi Penunjang Medis',
                'Kepala Unit Farmasi' => 'approved:Kepala Seksi Penunjang Medis',
                'Kepala Unit Rehabilitasi Medis' => 'approved:Kepala Seksi Penunjang Medis',
                
                'Kepala Unit Keuangan' => 'approved:Kepala Seksi Keuangan & Akuntansi',
                'Kepala Unit Akuntansi' => 'approved:Kepala Seksi Keuangan & Akuntansi',
                'Kepala Unit Kasir' => 'approved:Kepala Seksi Keuangan & Akuntansi',
                'Kepala Unit Casemix' => 'approved:Kepala Seksi Keuangan & Akuntansi',
                
                'Kepala Unit Kepegawaian & Diklat' => 'approved:Kepala Seksi SDM',
                'Kepala Unit Transportasi' => 'approved:Kepala Seksi SDM',
                'Kepala Unit Keamanan' => 'approved:Kepala Seksi SDM',
                'Kepala Unit Pramu Kantor' => 'approved:Kepala Seksi SDM',
                
                'Kepala Unit Logistik' => 'approved:Kepala Seksi Umum',
                'Kepala Unit Sanitasi' => 'approved:Kepala Seksi Umum',
                'Kepala Unit IPSRS' => 'approved:Kepala Seksi Umum',
            ];
            $pending_map_direktur = [
                'Kepala Seksi Penunjang Medis' => 'approved:Kepala Bagian Penunjang Medis',
                'Kepala Seksi Keperawatan' => 'approved:Kepala Bagian Pelayanan Medis',
                'Kepala Seksi IGD & Klinik Umum' => 'approved:Kepala Bagian Pelayanan Medis',
                'Kepala Seksi Keuangan & Akuntansi' => 'approved:Kepala Bagian Administrasi Umum & Keuangan',
                'Kepala Seksi SDM' => 'approved:Kepala Bagian Administrasi Umum & Keuangan',
                'Kepala Seksi Umum' => 'approved:Kepala Bagian Administrasi Umum & Keuangan',
                'Kepala Bagian Pelayanan Medis' => 'pending',
                'Kepala Bagian Penunjang Medis' => 'pending',
                'Kepala Bagian Administrasi Umum & Keuangan' => 'pending',

                'Kepala Unit Rawat Jalan & Home Care' => $pending_map_kabag['Kepala Unit Rawat Jalan & Home Care'],
                'Kepala Unit Rawat Inap Lantai 2' => $pending_map_kabag['Kepala Unit Rawat Inap Lantai 2'],
                'Kepala Unit Rawat Inap Lantai 3' => $pending_map_kabag['Kepala Unit Rawat Inap Lantai 3'],
                'Kepala Unit Kamar Operasi & CSSD' => $pending_map_kabag['Kepala Unit Kamar Operasi & CSSD'],
                'Kepala Unit Maternal & Perinatal' => $pending_map_kabag['Kepala Unit Maternal & Perinatal'],
                'Kepala Unit Hemodialisa' => $pending_map_kabag['Kepala Unit Hemodialisa'],
                'Kepala Unit ICU' => $pending_map_kabag['Kepala Unit ICU'],
                'Kepala Unit IGD' => $pending_map_kabag['Kepala Unit IGD'],
                
                'Kepala Unit Laboratorium' => $pending_map_kabag['Kepala Unit Laboratorium'],
                'Kepala Unit Radiologi' => $pending_map_kabag['Kepala Unit Radiologi'],
                'Kepala Unit Gizi' => $pending_map_kabag['Kepala Unit Gizi'],
                'Kepala Unit Rekam Medis' => $pending_map_kabag['Kepala Unit Rekam Medis'],
                'Kepala Unit Laundry' => $pending_map_kabag['Kepala Unit Laundry'],
                'Kepala Unit Pendaftaran' => $pending_map_kabag['Kepala Unit Pendaftaran'],
                'Kepala Unit Farmasi' => $pending_map_kabag['Kepala Unit Farmasi'],
                'Kepala Unit Rehabilitasi Medis' => $pending_map_kabag['Kepala Unit Rehabilitasi Medis'],

                'Kepala Unit Keuangan' => $pending_map_kabag['Kepala Unit Keuangan'],
                'Kepala Unit Akuntansi' => $pending_map_kabag['Kepala Unit Akuntansi'],
                'Kepala Unit Kasir' => $pending_map_kabag['Kepala Unit Kasir'],
                'Kepala Unit Casemix' => $pending_map_kabag['Kepala Unit Casemix'],

                'Kepala Unit Kepegawaian & Diklat' => $pending_map_kabag['Kepala Unit Kepegawaian & Diklat'],
                'Kepala Unit Transportasi' => $pending_map_kabag['Kepala Unit Transportasi'],
                'Kepala Unit Keamanan' => $pending_map_kabag['Kepala Unit Keamanan'],
                'Kepala Unit Pramu Kantor' => $pending_map_kabag['Kepala Unit Pramu Kantor'],
                
                'Kepala Unit Sanitasi' => $pending_map_kabag['Kepala Unit Sanitasi'],
                'Kepala Unit Logistik' => $pending_map_kabag['Kepala Unit Logistik'],
                'Kepala Unit IPSRS' => $pending_map_kabag['Kepala Unit IPSRS'],
            ];
            // Mapping status cuti sesuai jabatan
            $status_map_direktur = [
                'approved' => 'approved:' . $user->manager->jabatan,
                'rejected' => 'rejected:' . $user->manager->jabatan,
                'pending' => 'pending',
            ];
            $status_map_kabag = [
                'approved' => 'approved:' . $user->manager->jabatan,
                'rejected' => 'rejected:' . $user->manager->jabatan,
                'pending' => 'pending',
            ];
            $status_map_kasi = [
                'approved' => 'approved:' . $user->manager->jabatan,
                'rejected' => 'rejected:' . $user->manager->jabatan,
                'pending' => 'pending',
            ];
            // $status_map_kasi_keperawatan = [
            //     'approved' => 'approved:' . $user->manager->jabatan,
            //     'rejected' => 'rejected:' . $user->manager->jabatan,
            //     'pending' => $pending_map_kasi_keperawatan[$prosescuti->karyawan->unit],
            // ];
            $status_map_kasi_penunjang_medis = [
                'approved' => 'approved:' . $user->manager->jabatan,
                'rejected' => 'rejected:' . $user->manager->jabatan,
                'pending' => 'pending',
            ];

            // Pastikan jabatan user ada di dalam daftar manager
            if (in_array($user->manager->jabatan, $jabatanManagers)) {

                $jabatanUser = $user->manager->jabatan;
                if($jabatanUser === 'Direktur'){
                    // Set default update data dengan status yang sudah dimapping
                    if($prosescuti->admin){
                        $sisaCuti = $prosescuti->admin->sisa_cuti;
                        $jumlahHari = $prosescuti->jumlah_hari;
                        $sisaCutiSebelumnya = $prosescuti->admin->sisa_cuti_sebelumnya;
                        $today = now()->toDateString();

                        if($prosescuti->jenis_cuti === 'cuti_melahirkan'){
                            
                            $updateData = [
                                'status_manager' => $status_map_direktur[$request->status_manager],
                                'status_admin' => $status_map_direktur[$request->status_manager],
                                'tanggal_disetujui'=>$today
                            ];
                            // event(new CutiDisetujui($prosescuti,'admin'));
                            return redirect()->route('manager.email.to.admin')->with('approve success','Cuti melahirkan telah disetujui');
                        }
                        if($request->status_manager == 'approved'){
                            if(($sisaCuti + $sisaCutiSebelumnya) < $jumlahHari){
                                return redirect()->back()->with('no changes','Sisa cuti karyawan tidak mencukupi untuk disetujui');
                            }
    
                            if($sisaCutiSebelumnya >= $jumlahHari){
                                $updateData = [
                                    'status_manager' => $status_map_direktur[$request->status_manager],
                                    'status_admin' => $status_map_direktur[$request->status_manager],
                                    'tanggal_disetujui'=>$today
                                ];
    
                                $prosescuti->admin->update([
                                    'sisa_cuti_sebelumnya' => $sisaCutiSebelumnya - $jumlahHari,
                                    'tanggal_disetujui'=>$today
                                ]);
                                $prosescuti->admin->refresh();
                                return redirect()->route('manager.email.to.admin')->with('approve success','Cuti telah berhasil disetujui');
                            }else{
                                $jumlahHari = $jumlahHari - $sisaCutiSebelumnya;
    
                                $updateData = [
                                    'status_manager' => $status_map_direktur[$request->status_manager],
                                    'status_admin' => $status_map_direktur[$request->status_manager],
                                    'tanggal_disetujui'=>$today
                                ];
    
                                $prosescuti->admin->update([
                                    'sisa_cuti_sebelumnya' => 0,
                                    'sisa_cuti'=>$sisaCuti - $jumlahHari,
                                    'tanggal_disetujui'=>$today
                                ]);
                                $prosescuti->admin->refresh();
                                return redirect()->route('manager.email.to.admin')->with('approve success','Cuti telah berhasil disetujui');
                            }
                        }
                        else {
                            $updateData =[
                                'status_manager' => $status_map_direktur[$request->status_manager],
                                'status_admin' => $status_map_direktur[$request->status_manager],
                                'alasan_penolakan' =>$request->alasan_penolakan ?? null,
                                'ttd_direktur' => null,
                                'apr_direktur_id' =>null,
                            ];
                        }

                    }
                    elseif($prosescuti->manager){
                        $updateData = [
                            'status_manager' => $status_map_direktur[$request->status_manager],
                        ];
                    }
    
                    // Cek apakah jabatan memiliki mapping kolom tanda tangan
                    if (isset($ttd_columns[$user->manager->jabatan])) {
                        [$ttd_column, $id_column,$disetujui] = $ttd_columns[$user->manager->jabatan];
    
                        // Jika approved, simpan tanda tangan dan ID manager
                        if ($request->status_manager === 'approved') {
                            $updateData[$ttd_column] = $ttdManager;
                            $updateData[$id_column] = $manager_id;
                            $updateData[$disetujui] = $today;
                        }
                        // Jika rejected, hapus tanda tangan dan ID manager
                        elseif ($request->status_manager === 'rejected') {
                            $updateData['alasan_penolakan'] = $request->alasan_penolakan;
                            $updateData[$ttd_column] = null;
                            $updateData[$id_column] = null;
                        }
                        elseif ($request->status_manager === 'pending') {
                            $updateData[$ttd_column] = null;
                            $updateData[$id_column] = null;
                        }
                    }

                }
                elseif(str_contains($jabatanUser,'Kepala Bagian')){
                    // Set default update data dengan status yang sudah dimapping
                    $updateData = [
                        'status_manager' => $status_map_kabag[$request->status_manager],
                    ];
    
                    // Cek apakah jabatan memiliki mapping kolom tanda tangan
                    if (isset($ttd_columns[$user->manager->jabatan])) {
                        [$ttd_column, $id_column] = $ttd_columns[$user->manager->jabatan];
    
                        // Jika approved, simpan tanda tangan dan ID manager
                        if ($request->status_manager === 'approved') {
                            $updateData[$ttd_column] = $ttdManager;
                            $updateData[$id_column] = $manager_id;
                        }
                        // Jika rejected, hapus tanda tangan dan ID manager
                        elseif ($request->status_manager === 'rejected') {
                            $updateData['alasan_penolakan'] = $request->alasan_penolakan;
                            $updateData[$ttd_column] = null;
                            $updateData[$id_column] = null;
                        }
                        elseif ($request->status_manager === 'pending') {
                            $updateData[$ttd_column] = null;
                            $updateData[$id_column] = null;
                        }
                    }
                }
                elseif(str_contains($jabatanUser,'Kepala Seksi')){
                    // Set default update data dengan status yang sudah dimapping
                    $updateData = [
                        'status_manager' => $status_map_kasi_penunjang_medis[$request->status_manager],
                    ];

                    // Cek apakah jabatan memiliki mapping kolom tanda tangan
                    if (isset($ttd_columns[$user->manager->jabatan])) {
                        [$ttd_column, $id_column] = $ttd_columns[$user->manager->jabatan];
    
                        // Jika approved, simpan tanda tangan dan ID manager

                        if ($request->status_manager === 'approved') {
                            $updateData[$ttd_column] = $ttdManager;
                            $updateData[$id_column] = $manager_id;
                        }
                        // Jika rejected, hapus tanda tangan dan ID manager
                        elseif ($request->status_manager === 'rejected') {
                            $updateData['alasan_penolakan'] = $request->alasan_penolakan;
                            $updateData[$ttd_column] = null;
                            $updateData[$id_column] = null;
                        }
                        elseif ($request->status_manager === 'pending') {
                            $updateData[$ttd_column] = null;
                            $updateData[$id_column] = null;
                        }
                    }
                    
                }
                elseif(str_contains($jabatanUser,'Kepala Unit')){
                    // Set default update data dengan status yang sudah dimapping
                    $updateData = [
                        'status_manager' => $status_map_kasi_penunjang_medis[$request->status_manager],
                    ];

                    // Cek apakah jabatan memiliki mapping kolom tanda tangan
                    if (isset($ttd_columns[$user->manager->jabatan])) {
                        [$ttd_column, $id_column] = $ttd_columns[$user->manager->jabatan];
    
                        // Jika approved, simpan tanda tangan dan ID manager
                        if ($request->status_manager === 'approved') {
                            $updateData[$ttd_column] = $ttdManager;
                            $updateData[$id_column] = $manager_id;
                        }
                        // Jika rejected, hapus tanda tangan dan ID manager
                        elseif ($request->status_manager === 'rejected') {
                            $updateData['alasan_penolakan'] = $request->alasan_penolakan;
                            $updateData[$ttd_column] = null;
                            $updateData[$id_column] = null;
                        }
                        elseif ($request->status_manager === 'pending') {
                            $updateData[$ttd_column] = null;
                            $updateData[$id_column] = null;
                        }
                    }
                   
                }

                // Update proses cuti
                $prosescuti->update($updateData);
                return redirect()->back()->with('approve success', 'Cuti telah diperbarui.');
            }

            return redirect()->back()->with('no changes', 'Jabatan anda tidak benar / tidak cocok.');
        }
    }

    public function updateCutiStatus(){
        $bawahanDirektur = [
            'status_manager'=>['approved:Kepala Bagian Penunjang Medis'],
            'status_admin'=>['pending'],
            'unit'=>['Penunjang Medis'],
            'jabatan'=>['Kepala Seksi Penunjang Medis','Kepala Seksi Keperawatan','Kepala Seksi Keuangan & Akuntansi','Kepala Seksi SDM','Kepala Seksi Umum','Kepala Unit Teknologi Informasi']
        ];
        $bawahanKabagAdm =[
            'jabatan'=> [
                'Kepala Unit Keuangan',
                'Kepala Unit Akuntansi',
                'Kepala Unit Kasir',
                'Kepala Unit Casemix',

                'Kepala Unit Kepegawaian & Diklat',
                'Kepala Unit Keamanan',
                'Kepala Unit Transportasi',
                'Kepala Unit Pramu Kantor',

                'Kepala Unit Logistik',
                'Kepala Unit Sanitasi',
                'Kepala Unit IPSRS',

                'Keuangan',
                'Akuntansi',
                'Kasir',
                'Casemix',

                'Kepegawaian & Diklat',
                'Keamanan',
                'Transportasi',
                'Pramu Kantor',

                'Logistik',
                'Sanitasi',
                'IPSRS',
            ],
            'status_manager'=>[
                'approved:Kepala Unit Keuangan',
                'approved:Kepala Unit Akuntansi',
                'approved:Kepala Unit Kasir',
                'approved:Kepala Unit Casemix',
                'approved:Kepala Unit Kepegawaian & Diklat',
                'approved:Kepala Unit Keamanan',
                'approved:Kepala Unit Transportasi',
                'approved:Kepala Unit Pramu Kantor',
                'approved:Kepala Unit Logistik',
                'approved:Kepala Unit Sanitasi',
                'approved:Kepala Unit IPSRS',
                'pending',
            ]
        ];
        $bawahanKabagPenMed =[
            'jabatan'=> [
                'Kepala Unit Laboratorium',
                'Kepala Unit Radiologi',
                'Kepala Unit Gizi',
                'Kepala Unit Rekam Medis',
                'Kepala Unit Laundry',
                'Kepala Unit Pendaftaran',
                'Kepala Unit Farmasi',
                'Kepala Unit Rehabilitasi Medis',

                'Laboratorium',
                'Radiologi',
                'Gizi',
                'Rekam Medis',
                'Laundry',
                'Pendaftaran',
                'Farmasi',
                'Rehabilitasi Medis',
            ],
            'status_manager'=>[
                'approved:Kepala Unit Laboratorium',
                'approved:Kepala Unit Radiologi',
                'approved:Kepala Unit Gizi',
                'approved:Kepala Unit Rekam Medis',
                'approved:Kepala Unit Laundry',
                'approved:Kepala Unit Pendaftaran',
                'approved:Kepala Unit Farmasi',
                'approved:Kepala Unit Rehabilitasi Medis',
                'pending',
            ]
        ];
        $bawahanKabagPelMed =[
            'jabatan'=> [
                'Kepala Unit Rawat Jalan & Home Care',
                'Kepala Unit Rawat Inap Lantai 2',
                'Kepala Unit Rawat Inap Lantai 3',
                'Kepala Unit Kamar Operasi & CSSD',
                'Kepala Unit Maternal & Perinatal',
                'Kepala Unit Hemodialisa',
                'Kepala Unit ICU',
                'Kepala Unit IGD',

                'Rawat Jalan & Home Care',
                'Rawat Inap Lantai 2',
                'Rawat Inap Lantai 3',
                'Kamar Operasi & CSSD',
                'Maternal & Perinatal',
                'Hemodialisa',
                'ICU',
                'IGD',
            ],
            'status_manager'=>[
                'approved:Kepala Unit Rawat Jalan & Home Care',
                'approved:Kepala Unit Rawat Inap Lantai 2',
                'approved:Kepala Unit Rawat Inap Lantai 3',
                'approved:Kepala Unit Kamar Operasi & CSSD',
                'approved:Kepala Unit Maternal & Perinatal',
                'approved:Kepala Unit Hemodialisa',
                'approved:Kepala Unit ICU',
                'approved:Kepala Unit IGD',
                'pending',
            ]
        ];
        $bawahanKasiPenMed = [
            'unit'=> [
                'Laboratorium',
                'Gizi',
                'Radiologi',
                'Rekam Medis',
                'Laundry',
                'Pendaftaran',
                'Farmasi',
                'Rehabilitasi Medis',
            ]
        ];
        $bawahanKasiKeperawatan = [
            'unit'=> [
                'Rawat Jalan & Home Care',
                'Rawat Inap Lantai 2',
                'Rawat Inap Lantai 3',
                'Kamar Operasi & CSSD',
                'Maternal & Perinatal',
                'Hemodialisa',
                'ICU',
                'IGD',
            ]
        ];
        $bawahanKasiKeuangan = [
            'unit'=> [
                'Keuangan',
                'Akuntansi',
                'Kasir',
                'Casemix',
            ]
        ];
        $bawahanKasiSDM = [
            'unit'=> [
                'Kepegawaian & Diklat',
                'Keamanan',
                'Transportasi',
                'Pramu Kantor',
            ]
        ];
        $bawahanKasiUmum = [
            'unit'=> [
                'Logistik',
                'Sanitasi',
                'IPSRS',
            ]
        ];
        $status_manager = [
            
                'approved:Kepala Unit Rawat Jalan & Home Care',
                'approved:Kepala Unit Rawat Inap Lantai 2',
                'approved:Kepala Unit Rawat Inap Lantai 3',
                'approved:Kepala Unit Kamar Operasi & CSSD',
                'approved:Kepala Unit Maternal & Perinatal',
                'approved:Kepala Unit Hemodialisa',
                'approved:Kepala Unit ICU',
                'approved:Kepala Unit IGD',

                'approved:Kepala Unit Laboratorium',
                'approved:Kepala Unit Radiologi',
                'approved:Kepala Unit Gizi',
                'approved:Kepala Unit Rekam Medis',
                'approved:Kepala Unit Laundry',
                'approved:Kepala Unit Pendaftaran',
                'approved:Kepala Unit Farmasi',
                'approved:Kepala Unit Rehabilitasi Medis',

                'approved:Kepala Unit Keuangan',
                'approved:Kepala Unit Akuntansi',
                'approved:Kepala Unit Kasir',
                'approved:Kepala Unit Casemix',
                'approved:Kepala Unit Kepegawaian & Diklat',
                'approved:Kepala Unit Keamanan',
                'approved:Kepala Unit Transportasi',
                'approved:Kepala Unit Pramu Kantor',
                'approved:Kepala Unit Logistik',
                'approved:Kepala Unit Sanitasi',
                'approved:Kepala Unit IPSRS',

        ];
        $jabatanUser = Auth::user()->manager->jabatan;
        
        try {
            DB::beginTransaction();

            if($jabatanUser === 'Direktur'){
                // Ambil data cuti yang masih pending
                $cutiList = Cuti::with('manager','admin')
                ->whereHas('manager', function ($q) use ($bawahanDirektur) {
                    $q->whereIn('jabatan',$bawahanDirektur['jabatan'])
                    ->where('status_manager', 'pending');
                })
                ->orWhereHas('admin',function($q) use ($bawahanDirektur){
                    $q->whereIn('jabatan',$bawahanDirektur['jabatan'])
                    ->where('status_manager','pending');
                })
                ->get();
            }
            elseif($jabatanUser === 'Kepala Bagian Administrasi Umum & Keuangan'){
                // Ambil data cuti yang masih pending
                $cutiList = Cuti::with('karyawan','manager')
                ->whereHas('manager', function ($q) use ($bawahanKabagAdm) {
                    $q
                    ->whereNotIn('jabatan',['Kepala Unit Kepegawaian & Diklat','Kepala Unit Keamanan','Kepala Unit Transportasi','Kepala Unit Pramu Kantor'])
                    ->whereIn('jabatan',$bawahanKabagAdm['jabatan'])
                    ->where('status_manager', 'pending');
                })
                ->orWhereHas('karyawan', function ($q) use ($bawahanKabagAdm) {
                    $q
                    ->whereNotIn('unit',['Kepegawaian & Diklat','Keamanan','Transportasi','Pramu kantor'])
                    ->whereIn('unit',$bawahanKabagAdm['jabatan'])
                    ->whereIn('status_manager', $bawahanKabagAdm['status_manager']);
                })
                ->get();
            }
            elseif($jabatanUser === 'Kepala Bagian Penunjang Medis'){
                // Ambil data cuti yang masih pending
                $cutiList = Cuti::with('karyawan','manager')
                ->whereHas('manager', function ($q) use ($bawahanKabagPenMed) {
                    $q->whereIn('jabatan',$bawahanKabagPenMed['jabatan'])
                    ->where('status_manager', 'pending');
                })
                ->orWhereHas('karyawan', function ($q) use ($bawahanKabagPenMed){
                    $q->whereIn('unit',$bawahanKabagPenMed['jabatan'])
                    ->whereIn('status_manager', $bawahanKabagPenMed['status_manager']);
                })->get();
            }
            elseif($jabatanUser === 'Kepala Bagian Pelayanan Medis'){
                // Ambil data cuti yang masih pending
                $cutiList = Cuti::with('karyawan','manager')
                ->whereHas('manager', function ($q) use ($bawahanKabagPelMed) {
                    $q->whereIn('jabatan',$bawahanKabagPelMed['jabatan'])
                    ->where('status_manager', 'pending');
                })
                ->orWhereHas('karyawan', function ($q) use ($bawahanKabagPelMed) {
                    $q->whereIn('unit',$bawahanKabagPelMed['jabatan'])
                    ->whereIn('status_manager', $bawahanKabagPelMed['status_manager']);
                })
                ->get();
            }
            elseif($jabatanUser === 'Kepala Seksi Penunjang Medis'){
                // Ambil data cuti yang masih pending
                $cutiList = Cuti::where('status_manager', 'pending')
                ->whereHas('karyawan', function ($q) use ($bawahanKasiPenMed) {
                    $q->whereIn('unit',$bawahanKasiPenMed['unit']);
                })->get();
            }
            elseif($jabatanUser === 'Kepala Seksi Keperawatan'){
                // Ambil data cuti yang masih pending
                $cutiList = Cuti::with('karyawan')->where('status_manager', 'pending')->whereHas('karyawan', function ($q) use ($bawahanKasiKeperawatan) {
                    $q->whereIn('unit',$bawahanKasiKeperawatan['unit']);
                })->get();
            }
            elseif($jabatanUser === 'Kepala Seksi Keuangan & Akuntansi'){
                // Ambil data cuti yang masih pending
                $cutiList = Cuti::with('karyawan')->where('status_manager', 'pending')->whereHas('karyawan', function ($q) use ($bawahanKasiKeuangan) {
                    $q->whereIn('unit',$bawahanKasiKeuangan['unit']);
                })->get();
            }
            elseif($jabatanUser === 'Kepala Seksi SDM'){
                // Ambil data cuti yang masih pending
                $cutiList = Cuti::with('karyawan')->where('status_manager', 'pending')->whereHas('karyawan', function ($q) use ($bawahanKasiSDM) {
                    $q->whereIn('unit',$bawahanKasiSDM['unit']);
                })->get();
            }
            elseif($jabatanUser === 'Kepala Seksi Umum'){
                // Ambil data cuti yang masih pending
                $cutiList = Cuti::with('karyawan')->where('status_manager', 'pending')->whereHas('karyawan', function ($q) use ($bawahanKasiUmum) {
                    $q->whereIn('unit',$bawahanKasiUmum['unit']);
                })->get();
            }

            foreach ($cutiList as $cuti) {
                // Ambil jabatan atasan langsung dari pengaju cuti
                $statusCuti = $cuti->status_manager;
                if ($cuti->karyawan){
                    if($cuti->status_manager =='pending'){

                        $jabatanPengaju = $cuti->karyawan->unit;
                        $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
                        $jabatanAtasanAda = Manager::where('jabatan', $jabatanAtasanLangsung)->exists();
                        
                        // Jika atasan langsung tidak ada, ubah status_manager
                        if (!$jabatanAtasanAda) {
                            $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
    
                            $jabatanPengaju = $jabatanAtasanLangsung;
                            $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
    
                            $jabatanAtasanAda = Manager::where('jabatan', $jabatanAtasanLangsung)->exists();
    
                            if(!$jabatanAtasanAda){
                                $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                            }
                        }
                    }
                    elseif (in_array($statusCuti,$status_manager)){
                        $jabatanPengaju = $cuti->karyawan->unit;
                        $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
                        $jabatanAtasanAda = Manager::where('jabatan', $jabatanAtasanLangsung)->exists();
                        
                        // Jika atasan langsung tidak ada, ubah status_manager
                        if (!$jabatanAtasanAda) {
                            $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
    
                            $jabatanPengaju = $jabatanAtasanLangsung;
                            $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
    
                            $jabatanAtasanAda = Manager::where('jabatan', $jabatanAtasanLangsung)->exists();
    
                            if(!$jabatanAtasanAda){
                                $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                            }
                        }

                        $jabatanPengaju = $jabatanAtasanLangsung;
                        $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);

                        $jabatanAtasanAda = Manager::where('jabatan', $jabatanAtasanLangsung)->exists();

                        if(!$jabatanAtasanAda){
                            $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                        }
                    }

                   
                   
                } 

                if($cuti->manager) {

                    $jabatanPengaju = $cuti->manager->jabatan;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
    
                    // Cek apakah atasan langsung ada di database
                    $jabatanAtasanAda = Manager::where('jabatan', $jabatanAtasanLangsung)->exists();
                    // dd($jabatanPengaju);
                    // Jika atasan langsung tidak ada, ubah status_manager
                    if (!$jabatanAtasanAda) {
                        $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    }
                  
                }
                if($cuti->admin) {

                    $jabatanPengaju = $cuti->admin->jabatan;
                    $jabatanAtasanLangsung = $this->getAtasanLangsung($jabatanPengaju);
    
                    // Cek apakah atasan langsung ada di database
                    $jabatanAtasanAda = Manager::where('jabatan', $jabatanAtasanLangsung)->exists();
                    // dd($jabatanPengaju);
                    // Jika atasan langsung tidak ada, ubah status_manager
                    if (!$jabatanAtasanAda) {
                        $cuti->update(['status_manager' => "approved:$jabatanAtasanLangsung"]);
                    }
                  
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
        $jabatanUser=$user->manager->jabatan;
        $bawahanConfigs = [
            'jabatan'=>['Kepala Unit Rawat Jalan & Home Care','Kepala Unit Rawat Inap Lantai 2','Kepala Unit Rawat Inap Lantai 3','Kepala Unit Kamar Operasi & CSSD','Kepala Unit Maternal & Perinatal','Kepala Unit Hemodialisa','Kepala Unit ICU','Kepala Unit IGD'],
            'unit'=>['Rawat Jalan & Home Care','Rawat Inap Lantai 2','Rawat Inap Lantai 3','Kamar Operasi & CSSD','Maternal & Perinatal','Hemodialisa','ICU','IGD'],
            'status_admin'=>['pending'],
            'status_manager'=>['approved:Kepala Seksi Keperawatan','approved:Kepala Seksi IGD & Klinik Umum']
        ];
        $bawahanKabagPenunjangMedis = [
            'jabatan'=>['Kepala Unit Laboratorium','Kepala Unit Radiologi','Kepala Unit Gizi','Kepala Unit Rekam Medis','Kepala Unit Laundry','Kepala Unit Pendaftaran','Kepala Unit Farmasi','Kepala Unit Rehabilitasi Medis'],
            'unit'=>['Laboratorium','Radiologi','Gizi','Rekam Medis','Laundry','Pendaftaran','Farmasi','Rehabilitasi Medis'],
            'status_admin'=>['pending'],
            'status_manager'=>['approved:Kepala Seksi Penunjang Medis']
        ];
        $bawahanKabagAdmUmumKeuangan = [
            'jabatan' => ['Kepala Unit Keuangan','Kepala Unit Akuntansi','Kepala Unit Kasir','Kepala Unit Casemix','Kepala Unit Kepegawaian & Diklat','Kepala Unit Keamanan','Kepala Unit Transportasi','Kepala Unit Pramu Kantor','Kepala Unit Logistik','Kepala Unit Sanitasi','Kepala Unit IPSRS'],
            'unit'=>['Keuangan & Akuntansi','SDM','Umum','Keuangan','Akuntansi','Kasir','Casemix','Kepegawaian & Diklat','Keamanan','Transportasi','Pramu Kantor','Logistik','Sanitasi','IPSRS'],
            'status_admin'=>['pending'],
            'status_manager'=>['approved:Kepala Seksi Keuangan & Akuntansi','approved:Kepala Seksi SDM','approved:Kepala Seksi Umum']
        ];
        $bawahanDirektur = [
            'status_manager'=>['approved:Kepala Bagian Penunjang Medis','approved:Kepala Bagian Pelayanan Medis','approved:Kepala Bagian Administrasi Umum & Keuangan'],
            'status_admin'=>['pending'],
            'unit'=>['Penunjang Medis','Keperawatan','Keuangan & Akuntansi','SDM','Umum','Teknologi Informasi'],
            'jabatan'=>['Kepala Seksi Penunjang Medis','Kepala Seksi Keperawatan','Kepala Seksi Keuangan & Akuntansi','Kepala Seksi SDM','Kepala Seksi Umum','Kepala Unit Teknologi Informasi']
        ];
        $jabatanConfigs=
        [
            'Direktur'=>[
                'status_manager'=>['pending'],
                'status_admin'=>['pending'],
                'unit'=>['Pelayanan Medis','Bagian Penunjang Medis','Administrasi Umum & Keuangan','Teknologi Informasi','SDM','Humas & Pemasaran','Kesekretariatan'],
                'jabatan'=>['Kepala Bagian Pelayanan Medis','Kepala Bagian Penunjang Medis','Kepala Bagian Administrasi Umum & Keuangan','Kepala Unit Humas & Pemasaran','Kepala Unit Kesekretariatan','Kepala Unit Teknologi Informasi','Kepala Seksi SDM']
            ],
            'Kepala Unit Humas & Pemasaran'=>[
                'status_manager'=>['pending'],
                'status_admin'=>['pending'],
                'unit'=>['Humas & Pemasaran'],
                'jabatan'=>['karyawan']
            ],
            'Kepala Unit Kesekretariatan'=>[
                'status_manager'=>['pending'],
                'status_admin'=>['pending'],
                'unit'=>['Kesekretariatan'],
                'jabatan'=>['karyawan']
            ],
            'Kepala Bagian Pelayanan Medis' => 
                [
                    'status_manager' => ['pending','approved:Kepala Seksi Keperawatan','approved:Kepala Seksi IGD & Klinik Umum'],
                    'status_admin' => ['pending'],
                    'unit' => ['Rawat Jalan & Home Care','Rawat Inap Lantai 2','Rawat Inap Lantai 3','Kamar Operasi & CSSD','Maternal & Perinatal','Hemodialisa','ICU','IGD','IGD & Klinik Umum','Keperawatan','Dokter'],
                    'jabatan' => ['Kepala Seksi Keperawatan','Kepala Seksi IGD & Klinik Umum','karyawan','Dokter IGD','Dokter Klinik Umum'],
                ],
                'Kepala Seksi Keperawatan' =>
                [
                    'status_manager' => ['pending','approved:Kepala Unit Rawat Jalan & Home Care','approved:Kepala Unit Rawat Inap Lantai 2','approved:Kepala Unit Rawat Inap Lantai 3','approved:Kepala Unit Kamar Operasi & CSSD','approved:Kepala Unit Maternal & Perinatal','approved:Kepala Unit Hemodialisa','approved:Kepala Unit ICU','approved:Kepala Unit IGD'],
                    'status_admin' => ['pending'],
                    'unit' => ['Rawat Jalan & Home Care','Rawat Inap Lantai 2','Rawat Inap Lantai 3','Kamar Operasi & CSSD','Maternal & Perinatal','Hemodialisa','ICU','IGD'],
                    'jabatan' => ['Kepala Unit Rawat Jalan & Home Care','Kepala Unit Rawat Inap Lantai 2','Kepala Unit Rawat Inap Lantai 3','Kepala Unit Kamar Operasi & CSSD','Kepala Unit Maternal & Perinatal','Kepala Unit Hemodialisa','Kepala Unit ICU','Kepala Unit IGD','karyawan']
                ],
                'Kepala Seksi IGD & Klinik Umum' => 
                [
                    'status_manager'=>['pending'],
                    'status_admin'=>['pending'],
                    'unit'=>['Dokter'],
                    'jabatan'=>['Dokter IGD','Dokter Klinik Umum']
                ],

            'Kepala Unit Rawat Jalan & Home Care' =>
                [
                    'status_manager' => ['pending'],
                    'status_admin' => ['pending'],
                    'unit' => ['Rawat Jalan & Home Care'],
                    'jabatan' => ['karyawan']
                ],
            'Kepala Unit Rawat Inap Lantai 2' =>
                [
                    'status_manager' => ['pending'],
                    'status_admin' => ['pending'],
                    'unit' => ['Rawat Inap Lantai 2'],
                    'jabatan' => ['karyawan']
                ],
            'Kepala Unit Rawat Inap Lantai 3' =>
                [
                    'status_manager' => ['pending'],
                    'status_admin' => ['pending'],
                    'unit' => ['Rawat Inap Lantai 3'],
                    'jabatan' => ['karyawan']
                ],
            'Kepala Unit Kamar Operasi & CSSD' =>
                [
                    'status_manager' => ['pending'],
                    'status_admin' => ['pending'],
                    'unit' => ['Kamar Operasi & CSSD'],
                    'jabatan' => ['karyawan']
                ],
            'Kepala Unit Maternal & Perinatal' =>
                [
                    'status_manager' => ['pending'],
                    'status_admin' => ['pending'],
                    'unit' => ['Maternal & Perinatal'],
                    'jabatan' => ['karyawan']
                ],
            'Kepala Unit Hemodialisa' =>
                [
                    'status_manager' => ['pending'],
                    'status_admin' => ['pending'],
                    'unit' => ['Hemodialisa'],
                    'jabatan' => ['karyawan']
                ],
            'Kepala Unit ICU' =>
                [
                    'status_manager' => ['pending'],
                    'status_admin' => ['pending'],
                    'unit' => ['ICU'],
                    'jabatan' => ['karyawan']
                ],
            'Kepala Unit IGD' =>
                [
                    'status_manager' => ['pending'],
                    'status_admin' => ['pending'],
                    'unit' => ['IGD'],
                    'jabatan' => ['karyawan']
                ],
            'Kepala Bagian Penunjang Medis' => 
            [
                'status_manager' => ['pending','approved:Kepala Seksi Penunjang Medis','rejected:Kepala Bagian Penunjang Medis'],
                'status_admin' => ['pending'],
                'jabatan' => ['Kepala Seksi Penunjang Medis','karyawan'],
                'unit' => ['Penunjang Medis','Laboratorium','Radiologi','Gizi','Rekam Medis','Laundry','Pendaftaran','Farmasi','Rehabilitasi Medis']
            ],
            'Kepala Seksi Penunjang Medis' =>
            [
                'status_manager' => ['pending','approved:Kepala Unit Laboratorium','approved:Kepala Unit Radiologi','approved:Kepala Unit Gizi','approved:Kepala Unit Rekam Medis','approved:Kepala Unit Laundry','approved:Kepala Unit Pendaftaran','approved:Kepala Unit Farmasi','approved:Kepala Unit Rehabilitasi Medis'],
                'status_admin' => ['pending'],
                'unit' => ['Laboratorium','Radiologi','Gizi','Rekam Medis','Laundry','Pendaftaran','Farmasi','Rehabilitasi Medis'],
                'jabatan' => ['Kepala Unit Laboratorium','Kepala Unit Radiologi','Kepala Unit Gizi','Kepala Unit Rekam Medis','Kepala Unit Laundry','Kepala Unit Pendaftaran','Kepala Unit Farmasi','Kepala Unit Rehabilitasi Medis','karyawan']
            ],
            'Kepala Unit Laboratorium' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Laboratorium'],
                'jabatan' => ['karyawan']
            ],
            'Kepala Unit Radiologi' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Radiologi'],
                'jabatan' => ['karyawan']
            ],
            'Kepala Unit Gizi' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Gizi'],
                'jabatan' => ['karyawan']
            ],
            'Kepala Unit Rekam Medis' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Rekam Medis'],
                'jabatan' => ['karyawan']
            ],
            'Kepala Unit Laundry' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Laundry'],
                'jabatan' => ['karyawan']
            ],
            'Kepala Unit Pendaftaran' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Pendaftaran'],
                'jabatan' => ['karyawan']
            ],
            'Kepala Unit Farmasi' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Farmasi'],
                'jabatan' => ['karyawan']
            ],
            'Kepala Unit Rehabilitasi Medis' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Rehabilitasi Medis'],
                'jabatan' => ['karyawan']
            ],
            
            'Kepala Bagian Administrasi Umum & Keuangan'=>
            [
                'status_manager' => ['pending','approved:Kepala Seksi Keuangan & Akuntansi','approved:Kepala Seksi SDM','approved:Kepala Seksi Umum'],
                'status_admin' => ['pending'],
                'unit' => ['Keuangan & Akuntansi','SDM','Umum','Keuangan','Akuntansi','Kasir','Casemix','Kepegawaian & Diklat','Keamanan','Transportasi','Pramu Kantor','Logistik','Sanitasi','IPSRS'],
                'jabatan' => ['Kepala Seksi Keuangan & Akuntansi','Kepala Seksi SDM','Kepala Seksi Umum',]
            ],
            'Kepala Seksi Keuangan & Akuntansi' =>
            [
                'status_manager' => ['pending','approved:Kepala Unit Keuangan','approved:Kepala Unit Akuntansi','approved:Kepala Unit Kasir','approved:Kepala Unit Casemix'],
                'status_admin' => ['pending'],
                'unit' => ['Keuangan','Akuntansi','Kasir','Casemix'],
                'jabatan' => ['karyawan','Kepala Unit Keuangan','Kepala Unit Akuntansi','Kepala Unit Kasir','Kepala Unit Casemix']
            ],
            'Kepala Unit Keuangan' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Keuangan'],
                'jabatan' => ['karyawan']
            ],            
            'Kepala Unit Akuntansi' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Akuntansi'],
                'jabatan' => ['karyawan']
            ],            
            'Kepala Unit Kasir' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Kasir'],
                'jabatan' => ['karyawan']
            ],            
            'Kepala Unit Casemix' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Casemix'],
                'jabatan' => ['karyawan']
            ],            
            'Kepala Seksi SDM' =>
            [
                'status_manager' => ['pending','approved:Kepala Unit Kepegawaian & Diklat','approved:Kepala Unit Keamanan','approved:Kepala Unit Transportasi','approved:Kepala Unit Pramu Kantor'],
                'status_admin' => ['pending'],
                'unit' => ['Kepegawaian & Diklat','Keamanan','Transportasi','Pramu Kantor'],
                'jabatan' => ['karyawan','Kepala Unit Kepegawaian & Diklat','Kepala Unit Keamanan','Kepala Unit Transportasi','Kepala Unit Pramu Kantor']
            ],
            'Kepala Unit Kepegawaian & Diklat' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Kepegawaian & Diklat'],
                'jabatan' => ['karyawan']
            ],  
            'Kepala Unit Keamanan' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Keamanan'],
                'jabatan' => ['karyawan']
            ],  
            'Kepala Unit Transportasi' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Transportasi'],
                'jabatan' => ['karyawan']
            ],  
            'Kepala Unit Pramu Kantor' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Pramu Kantor'],
                'jabatan' => ['karyawan']
            ],  
            'Kepala Seksi Umum' =>
            [
                'status_manager' => ['pending','approved:Kepala Unit Logistik','approved:Kepala Unit Sanitasi','approved:Kepala Unit IPSRS'],
                'status_admin' => ['pending'],
                'unit' => ['Logistik','Sanitasi','IPSRS'],
                'jabatan' => ['karyawan','Kepala Unit Logistik','Kepala Unit Sanitasi','Kepala Unit IPSRS']
            ],
            'Kepala Unit Logistik' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Logistik'],
                'jabatan' => ['karyawan']
            ],  
            'Kepala Unit Sanitasi' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['Sanitasi'],
                'jabatan' => ['karyawan']
            ],  
            'Kepala Unit IPSRS' =>
            [
                'status_manager' => ['pending'],
                'status_admin' => ['pending'],
                'unit' => ['IPSRS'],
                'jabatan' => ['karyawan']
            ],  
        ];



        //Cuti Pending
        if($jabatanUser == 'Direktur'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager');
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

                try{
                    $kabagQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($config) {
                        $q->whereIn('jabatan', $config['jabatan'])
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin', $config['status_admin']);
                        // ->whereIn('status_manager', $config['status_manager']);
                    });

                    $kasiQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($bawahanDirektur) {
                        $q->whereIn('status_manager',$bawahanDirektur['status_manager'])
                        ->whereIn('status_admin',$bawahanDirektur['status_admin'])
                        ->whereIn('unit',$bawahanDirektur['unit']);
                        // ->whereIn('jabatan',$bawahanDirektur['jabatan']);
                    });
                    
                    $adminQuery = (clone $baseQuery)->whereHas('admin', function ($q) use ($bawahanDirektur) {
                        $q->whereIn('jabatan', $bawahanDirektur['jabatan'])
                        ->whereIn('unit', $bawahanDirektur['unit'])
                        ->whereIn('status_admin', $bawahanDirektur['status_admin']);
                        // ->whereIn('status_manager', $bawahanDirektur['status_manager']);
                    });
                    
                    // Gabungkan hasil query

                    if ($unitFilter) {
                    $kabagQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $kasiQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $adminQuery->whereHas('admin', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    }

                /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\

                if ($jenisFilter) {
                    $kabagQuery->where('jenis_cuti', $jenisFilter);
                    $kasiQuery->where('jenis_cuti', $jenisFilter);
                    $adminQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $kabagQuery->whereIn('status_manager',$config['status_manager']);
                    $kasiQuery->whereIn('status_manager',$bawahanDirektur['status_manager']);
                    $adminQuery->whereIn('status_manager',$bawahanDirektur['status_manager']);
                } else {
                    $kabagQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $kasiQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $adminQuery->where('status_manager','LIKE', '%'.$status.'%');
                }

                if ($tanggalMulaiFilter) {
                    $kabagQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $kasiQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $adminQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $kabagQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $kasiQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $adminQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }

                
                /////////////////// SORTING DATA \\\\\\\\\\\\\\\\\\\\\
                $combinedQuery = $kabagQuery->union($kasiQuery)->union($adminQuery);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection)
                        ;
                }
                $units = Admin::whereIn('jabatan',$config['jabatan'])->pluck('unit')
                ->merge(Manager::whereIn('jabatan',$config['jabatan'])->pluck('unit'))
                ->toArray();
            
                $kabagQuery=$kabagQuery->union($kasiQuery);
                $pendingCuti = $kabagQuery->union($adminQuery)
                ->paginate(10)
                ->appends(request()->query());
                    
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Bagian Pelayanan Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];

                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'jenis_cuti';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);
                 
                try{
                    //Menyimpan data bawahan langsung yaitu Kasi
                    $kabagQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($config) {
                        $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    ->whereIn('status_manager', $config['status_manager'])
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($bawahanConfigs){
                    $q
                    ->whereIn('jabatan',$bawahanConfigs['jabatan'])
                    ->whereIn('unit',$bawahanConfigs['unit'])
                    ->whereIn('status_admin',$bawahanConfigs['status_admin']);
                    // ->whereIn('status_manager',$bawahanConfigs['status_manager']);
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($bawahanConfigs) {
                    $q->whereIn('jabatan',['karyawan','Dokter IGD','Dokter Klinik Umum'])
                    ->whereIn('unit', $bawahanConfigs['unit'])
                    ->whereIn('status_admin',$bawahanConfigs['status_admin']);
                    // ->whereIn('status_manager',$bawahanConfigs['status_manager']);
                });
                
                if ($unitFilter) {
                    $kabagQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $kanitQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\

                if ($jenisFilter) {
                    $kabagQuery->where('jenis_cuti', $jenisFilter);
                    $kanitQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $kabagQuery->whereIn('status_manager',$config['status_manager']);
                    $kanitQuery->whereIn('status_manager',$bawahanConfigs['status_manager']);
                    $karyawanQuery->whereIn('status_manager',$bawahanConfigs['status_manager']);
                } else {
                    $kabagQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $kanitQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                }

                if ($tanggalMulaiFilter) {
                    $kabagQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $kanitQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $kabagQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $kanitQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }
                $combinedQuery = $kabagQuery->union($kanitQuery)->union($karyawanQuery);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection);
                }
                $units = Manager::whereIn('jabatan',$config['jabatan'])->pluck('unit')
                ->union(Manager::whereIn('jabatan',$bawahanConfigs['jabatan'])->pluck('unit'))
                ->toArray();

                $kabagQuery = $kabagQuery->union($kanitQuery);
                $pendingCuti = $kabagQuery->union($karyawanQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10)->appends(request()->query());
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Seksi Keperawatan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager','bawahan_langsung');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'jenis_cuti';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);

                try{

                $managerQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin']);
                    // ->whereIn('status_manager', $config['status_manager']);
                });
                $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($config) {
                    $q->where('jabatan','karyawan')
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin']);
                    // ->whereIn('status_manager',$config['status_manager'])
                    // ->where('status_manager','!=','pending');
                });

                /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\
                if ($unitFilter) {
                    $managerQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                if ($jenisFilter) {
                    $managerQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $managerQuery->whereIn('status_manager',$config['status_manager']);
                    $karyawanQuery->where('status_manager','LIKE', '%approved:Kepala Unit%');
                } else {
                    $managerQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                    // dd($managerQuery);
                }

                if ($tanggalMulaiFilter) {
                    $managerQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $managerQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }
                $combinedQuery = $managerQuery->union($karyawanQuery);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection);
                }

                $units = Manager::whereIn('jabatan',$config['jabatan'])->pluck('unit')
                ->merge(Karyawan::where('unit','Keperawatan')->pluck('unit'))
                ->toArray();
            
                $pendingCuti = $managerQuery->union($karyawanQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10)->appends(request()->query());
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        //dan seterusnya
        elseif($jabatanUser == 'Kepala Seksi IGD & Klinik Umum'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'jenis_cuti';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->whereIn('jabatan',$config['jabatan'])
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                    ->whereIn('status_manager',$config['status_manager']);
                });

                $units = Manager::whereIn('jabatan',$config['jabatan'])->pluck('unit')
                ->merge(Karyawan::where('unit','IGD & Klinik Umum')->pluck('unit'))
                ->toArray();
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10)->appends(request()->query());
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Keuangan & Akuntansi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'jenis_cuti';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);

                try{

                $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin']);
                    // ->whereIn('status_manager',$config['status_manager'])
                    // ->where('status_manager','!=','pending');
                });
                $managerQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin']);
                    // ->whereIn('status_manager', $config['status_manager']);
                });

                                /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\
                if ($unitFilter) {
                    $managerQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                if ($jenisFilter) {
                    $managerQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $managerQuery->whereIn('status_manager',$config['status_manager']);
                    $karyawanQuery->where('status_manager','LIKE', '%approved:Kepala Unit%');
                } else {
                    $managerQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                    // dd($managerQuery);
                }

                if ($tanggalMulaiFilter) {
                    $managerQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $managerQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }
                $combinedQuery = $managerQuery->union($karyawanQuery);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection);
                }

                $units = Karyawan::whereIn('jabatan',$config['jabatan'])->pluck('unit')
                ->merge(Karyawan::where('unit','Keuangan & Akuntansi')->pluck('unit'))
                ->toArray();
                
                $pendingCuti = $karyawanQuery->union($managerQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10)->appends(request()->query());
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Seksi SDM'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'jenis_cuti';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);

                try{

                $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin']);
                    // ->whereIn('status_manager',$config['status_manager'])
                    // ->where('status_manager','!=','pending');
                });
                $managerQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin']);
                    // ->whereIn('status_manager', $config['status_manager']);
                });

                /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\
                if ($unitFilter) {
                    $managerQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                if ($jenisFilter) {
                    $managerQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $managerQuery->whereIn('status_manager',$config['status_manager']);
                    $karyawanQuery->where('status_manager','LIKE', '%approved:Kepala Unit%');
                } else {
                    $managerQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                    // dd($managerQuery);
                }

                if ($tanggalMulaiFilter) {
                    $managerQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $managerQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }
                $combinedQuery = $managerQuery->union($karyawanQuery);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection);
                }

                $units = Karyawan::whereIn('jabatan',$config['jabatan'])->pluck('unit')
                ->merge(Karyawan::where('unit','SDM')->pluck('unit'))
                ->toArray();
                
                $pendingCuti = $karyawanQuery->union($managerQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10)->appends(request()->query());
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Umum'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'jenis_cuti';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);

                try{

                $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin']);
                    // ->whereIn('status_manager',$config['status_manager'])
                    // ->where('status_manager','!=','pending');
                });
                $managerQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin']);
                    // ->whereIn('status_manager', $config['status_manager']);
                });

                /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\
                if ($unitFilter) {
                    $managerQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                if ($jenisFilter) {
                    $managerQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $managerQuery->whereIn('status_manager',$config['status_manager']);
                    $karyawanQuery->where('status_manager','LIKE', '%approved:Kepala Unit%');
                } else {
                    $managerQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                    // dd($managerQuery);
                }

                if ($tanggalMulaiFilter) {
                    $managerQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $managerQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }
                $combinedQuery = $managerQuery->union($karyawanQuery);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection);
                }

                $units = Karyawan::whereIn('jabatan',$config['jabatan'])->pluck('unit')
                ->merge(Karyawan::where('unit','Umum')->pluck('unit'))
                ->toArray();
                
                $pendingCuti = $karyawanQuery->union($managerQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10)->appends(request()->query());
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rawat Jalan & Home Care'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rawat Inap Lantai 2'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rawat Inap Lantai 3'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Kamar Operasi & CSSD'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
                
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Maternal & Perinatal'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Hemodialisa'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{
                    
                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit ICU'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit IGD'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Bagian Penunjang Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];

                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'jenis_cuti';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);
                try {
                    $kasiQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($config) {
                        $q->whereIn('jabatan', $config['jabatan'])
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin', $config['status_admin'])
                        // ->whereIn('status_manager', $config['status_manager'])
                        ;
                    });
                    //Menyimpan data bawahan 2 tingkat yaitu Kanit
                    $kanitQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($bawahanKabagPenunjangMedis){
                        $q
                        ->whereIn('jabatan',$bawahanKabagPenunjangMedis['jabatan'])
                        ->whereIn('unit',$bawahanKabagPenunjangMedis['unit'])
                        ->whereIn('status_admin',$bawahanKabagPenunjangMedis['status_admin'])
                        // ->whereIn('status_manager',$bawahanKabagPenunjangMedis['status_manager'])
                        ;
                    });
                    //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                    $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($config) {
                        $q->whereIn('jabatan',['karyawan'])
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        // ->whereIn('status_manager',['approved:Kepala Seksi Penunjang Medis'])
                        ;
                    });

                    /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\

                if ($jenisFilter) {
                    $kasiQuery->where('jenis_cuti', $jenisFilter);
                    $kanitQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $kasiQuery->whereIn('status_manager',$config['status_manager']);
                    $kanitQuery->where('status_manager', 'LIKE','%approved:Kepala Seksi%');
                    $karyawanQuery->where('status_manager', 'LIKE','%approved:Kepala Seksi%');
                } else {
                    $kasiQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $kanitQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                }

                if ($tanggalMulaiFilter) {
                    $kasiQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $kanitQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $kasiQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $kanitQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }
                $combinedQuery = $kasiQuery->union($kanitQuery)->union($karyawanQuery);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection);
                }

                    $units = Karyawan::whereIn('unit',$config['unit'])->pluck('unit')
                    ->merge(Manager::whereIn('unit',['Bagian Penunjang Medis','Penunjang Medis'])->pluck('unit'))
                    ->toArray();
                
                    $kasiQuery = $kasiQuery->union($kanitQuery);
                    $pendingCuti = $kasiQuery->union($karyawanQuery)
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10)->appends(request()->query());
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
                //Menyimpan data bawahan langsung yaitu Kasi
                
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Penunjang Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'jenis_cuti';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);

                try { 

                    $managerQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($config) {
                        $q->whereIn('jabatan', $config['jabatan'])
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin', $config['status_admin']);
                        // ->whereIn('status_manager', $config['status_manager']);
                    });
                    $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin']);
                        // ->whereIn('status_manager',$config['status_manager'])
                        // ->where('status_manager','!=','pending');
                    });

                                    /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\
                if ($unitFilter) {
                    $managerQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                if ($jenisFilter) {
                    $managerQuery->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $managerQuery->whereIn('status_manager',$config['status_manager']);
                    $karyawanQuery->where('status_manager','LIKE', '%approved:Kepala Unit%');
                } else {
                    $managerQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                    // dd($managerQuery);
                }

                if ($tanggalMulaiFilter) {
                    $managerQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $managerQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }
                $combinedQuery = $managerQuery->union($karyawanQuery);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection);
                }

                $units = Karyawan::whereIn('jabatan',$config['jabatan'])->pluck('unit')
                ->merge(Karyawan::where('unit','Penunjang Medis')->pluck('unit'))
                ->toArray();
                
                    $pendingCuti = $managerQuery->union($karyawanQuery)
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10)->appends(request()->query());
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Unit Laboratorium'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];

                // $this->updateCutiStatus();
        
                try {
                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
                
                    $pendingCuti = $karyawanQuery
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10);
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Unit Radiologi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();
                try {

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
                    
                    $pendingCuti = $karyawanQuery
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10);
                    
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Unit Gizi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                // $this->updateCutiStatus();

                try { 
                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
                
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                
                }   
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Unit Rekam Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try { 
                    
                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
                    
                    $pendingCuti = $karyawanQuery
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10);
                    
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Unit Laundry'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        

                // $this->updateCutiStatus();

                try { 

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
                    
                    $pendingCuti = $karyawanQuery
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10);
                    
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Unit Pendaftaran'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];

                // $this->updateCutiStatus();

                try { 
                    
                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
                    
                    $pendingCuti = $karyawanQuery
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10);
                    
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Unit Farmasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try { 
                    
                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
                    
                    $pendingCuti = $karyawanQuery
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10);
                    
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }elseif($jabatanUser == 'Kepala Unit Rehabilitasi Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try { 
                    
                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
                    
                    $pendingCuti = $karyawanQuery
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10);
                    
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Bagian Administrasi Umum & Keuangan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                
                $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'jenis_cuti';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager','admin']);

                try { 

                    //Menyimpan data bawahan langsung yaitu Kasi
                    $kabagQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    // ->whereIn('status_manager', $config['status_manager'])
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = (clone $baseQuery)->whereHas('manager', function ($q) use ($bawahanKabagAdmUmumKeuangan){
                    $q
                    ->whereIn('jabatan',$bawahanKabagAdmUmumKeuangan['jabatan'])
                    ->whereIn('unit',$bawahanKabagAdmUmumKeuangan['unit'])
                    ->whereIn('status_admin',$bawahanKabagAdmUmumKeuangan['status_admin'])
                    // ->whereIn('status_manager',$bawahanKabagAdmUmumKeuangan['status_manager'])
                    ;
                });
                $kanitSDM = (clone $baseQuery)->whereHas('manager', function ($q) use ($bawahanKabagAdmUmumKeuangan){
                    $q
                    ->whereIn('jabatan',$bawahanKabagAdmUmumKeuangan['jabatan'])
                    ->whereIn('unit',$bawahanKabagAdmUmumKeuangan['unit'])
                    ->whereIn('status_admin',$bawahanKabagAdmUmumKeuangan['status_admin'])
                    // ->whereIn('status_manager',['pending'])
                    ;
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($bawahanKabagAdmUmumKeuangan) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $bawahanKabagAdmUmumKeuangan['unit'])
                    ->whereIn('status_admin',$bawahanKabagAdmUmumKeuangan['status_admin'])
                    // ->whereIn('status_manager',$bawahanKabagAdmUmumKeuangan['status_manager'])
                    ;
                });
                
                $karyawanSDM = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($bawahanKabagAdmUmumKeuangan) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $bawahanKabagAdmUmumKeuangan['unit'])
                    ->whereIn('status_admin',$bawahanKabagAdmUmumKeuangan['status_admin'])
                    // ->whereIn('status_manager',['approved:Kepala Unit Kepegawaian & Diklat','approved:Kepala Unit Keamanan','approved:Kepala unit Transportasi','approved:Kepala Unit Pramu Kantor'])
                    ;
                });

                                /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\
                if ($unitFilter) {
                    $kabagQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $kanitQuery->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $kanitSDM->whereHas('manager', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                    $karyawanSDM->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                if ($jenisFilter) {
                    $kabagQuery->where('jenis_cuti', $jenisFilter);
                    $kanitQuery->where('jenis_cuti', $jenisFilter);
                    $kanitSDM->where('jenis_cuti', $jenisFilter);
                    $karyawanSDM->where('jenis_cuti', $jenisFilter);
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $kabagQuery->whereIn('status_manager',$config['status_manager']);
                    $kanitQuery->whereIn('status_manager',$bawahanKabagAdmUmumKeuangan['status_manager']);
                    $kanitSDM->whereIn('status_manager',$bawahanKabagAdmUmumKeuangan['status_manager']);
                    $karyawanQuery->whereIn('status_manager', $bawahanKabagAdmUmumKeuangan['status_manager']);
                    $karyawanSDM->whereIn('status_manager','LIKE','%approved:Kepala Seksi%');
                } else {
                    $kabagQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $kanitQuery->where('status_manager','LIKE', '%'.$status.'%');
                    $kanitSDM->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanSDM->where('status_manager','LIKE', '%'.$status.'%');
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                    // dd($managerQuery);
                }

                if ($tanggalMulaiFilter) {
                    $kabagQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $kanitQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $kanitSDM->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanSDM->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $kabagQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $kanitQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $kanitSDM->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanSDM->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }
                $combinedQuery = $kabagQuery->union($kanitQuery)->union($kanitSDM)->union($karyawanSDM)->union($karyawanQuery);

                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection);
                }

                $units = 
                Karyawan::whereIn('unit',$config['unit'])->pluck('unit')
                ->toArray();
            
                $kabagQuery = $kabagQuery->union($kanitQuery)->union($kanitSDM)->union($karyawanSDM);
                $pendingCuti = $kabagQuery->union($karyawanQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10)->appends(request()->query());
                
                }   
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Keuangan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try { 

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Akuntansi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
                
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Kasir'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
                    
                    $pendingCuti = $karyawanQuery
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10);
                    
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Casemix'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
                    
                    $pendingCuti = $karyawanQuery
                    ->orderBy('tanggal_mulai', 'desc')
                    ->paginate(10);
                    
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Kepegawaian & Diklat'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                $unitFilter = request('unit');
                $jenisFilter = request('jenis_cuti');
                $status = request('status_manager','bawahan_langsung');
                $tanggalMulaiFilter = request('tanggal_mulai');
                $tanggalAkhirFilter = request('tanggal_selesai');

                //////////////////////// SORTING DATA AWAL \\\\\\\\\\\\\\\\\\\\\\\\\\\\
                $sortField = $request->get('sort_by', 'tanggal_mulai');
                $sortDirection = $request->get('sort_dir', 'desc');


                $validSortFields = ['jenis_cuti','tanggal_mulai', 'tanggal_selesai', 'created_at',];
                $sortField = in_array($sortField, $validSortFields) ? $sortField : 'tanggal_mulai';
                $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

                $baseQuery = Cuti::with(['karyawan', 'manager']);

                try{

                    $karyawanQuery = (clone $baseQuery)->whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        // ->whereIn('status_admin',$config['status_admin'])
                    // ->where('status_manager','pending')
                    ;
                });

                                    /////////////////// FILTER DATA \\\\\\\\\\\\\\\\\\\\
                if ($unitFilter) {
                    $karyawanQuery->whereHas('karyawan', function($q) use ($unitFilter) {
                        $q->where('unit', $unitFilter);
                    });
                }

                if ($jenisFilter) {
                    $karyawanQuery->where('jenis_cuti', $jenisFilter);
                }

                if ($status === 'bawahan_langsung'){
                    $karyawanQuery->whereIn('status_manager', ['pending']);
                } else {
                    $karyawanQuery->where('status_manager','LIKE', '%'.$status.'%');
                    // dd($managerQuery);
                }

                if ($tanggalMulaiFilter) {
                    $karyawanQuery->where('tanggal_mulai', '>=', $tanggalMulaiFilter);
                }

                if ($tanggalAkhirFilter) {
                    $karyawanQuery->where('tanggal_selesai', '<=', $tanggalAkhirFilter);
                }
                $combinedQuery = $karyawanQuery;
                if($sortField) {
                    // Sorting untuk field di tabel utama (cutis)
                    $pendingCuti = $combinedQuery
                        ->orderBy($sortField, $sortDirection);
                }

                $units = 
                Karyawan::whereIn('unit',$config['unit'])->pluck('unit')
                ->toArray();
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10)->appends(request()->query());
                
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Keamanan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);

                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Transportasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{
                    
                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Pramu Kantor'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                        ->whereIn('status_admin',$config['status_admin'])
                        ->where('status_manager','pending');
                    });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Logistik'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit Sanitasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{

                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
                
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }
        elseif($jabatanUser == 'Kepala Unit IPSRS'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
        
                // $this->updateCutiStatus();

                try{
                    
                    $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                        $q->where('jabatan','karyawan')
                        ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','pending');
                });
            
                $pendingCuti = $karyawanQuery
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
                
                }
                catch (\Illuminate\Database\QueryException $e){
                    return response()->json(['error' => 'Gagal menampilkan data cuti'], 500);
                } 
            }
        }

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //Cuti Approved
        if($jabatanUser == 'Direktur'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kabag
                $kabagQuery = Cuti::whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    ->whereIn('status_manager', ['approved:Direktur'])
                    ;
                });
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($bawahanDirektur) {
                    $q->whereIn('jabatan', $bawahanDirektur['jabatan'])
                    ->whereIn('unit', $bawahanDirektur['unit'])
                    ->whereIn('status_admin', $bawahanDirektur['status_admin'])
                    ->whereIn('status_manager', ['approved:Direktur'])
                    ;
                });
                $adminQuery = Cuti::whereHas('admin', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', ['approved:Direktur'])
                    ->whereIn('status_manager', ['approved:Direktur'])
                    ;
                });
            
                $kabagQuery = $kabagQuery->union($kasiQuery);
                $cutiApproved = $kabagQuery->union($adminQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Bagian Pelayanan Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kasi
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    ->where('status_manager','approved:Kepala Bagian Pelayanan Medis')
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanConfigs){
                    $q
                    ->whereIn('jabatan',$bawahanConfigs['jabatan'])
                    ->whereIn('unit',$bawahanConfigs['unit'])
                    ->whereIn('status_admin',$bawahanConfigs['status_admin'])
                    ->where('status_manager','approved:Kepala Bagian Pelayanan Medis');
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Bagian Pelayanan Medis');
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiApproved = $kasiQuery->union($karyawanQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Bagian Penunjang Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kasi
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    ->where('status_manager','approved:Kepala Bagian Penunjang Medis')
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanKabagPenunjangMedis){
                    $q
                    ->whereIn('jabatan',$bawahanKabagPenunjangMedis['jabatan'])
                    ->whereIn('unit',$bawahanKabagPenunjangMedis['unit'])
                    ->whereIn('status_admin',$bawahanKabagPenunjangMedis['status_admin'])
                    ->whereIn('status_manager',['approved:Kepala Bagian Penunjang Medis']);
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Bagian Penunjang Medis');
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiApproved = $kasiQuery->union($karyawanQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Bagian Administrasi Umum & Keuangan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kasi
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    ->where('status_manager','approved:Kepala Bagian Administrasi Umum & Keuangan')
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanKabagAdmUmumKeuangan){
                    $q
                    ->whereIn('jabatan',$bawahanKabagAdmUmumKeuangan['jabatan'])
                    ->whereIn('unit',$bawahanKabagAdmUmumKeuangan['unit'])
                    ->whereIn('status_admin',$bawahanKabagAdmUmumKeuangan['status_admin'])
                    ->where('status_manager','approved:Kepala Bagian Administrasi Umum & Keuangan');
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Bagian Administrasi Umum & Keuangan');
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiApproved = $kasiQuery->union($karyawanQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Keperawatan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan', 'manager'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->where('jabatan','karyawan')
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Seksi Keperawatan');
                })
                ->orWhereHas('manager', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Seksi Keperawatan');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi IGD & Klinik Umum'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan','manager'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Seksi IGD & Klinik Umum');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Penunjang Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan','manager'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->whereIn('status_manager',['approved:Kepala Seksi Penunjang Medis']);
                })
                ->orWhereHas('manager', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->whereIn('status_manager',['approved:Kepala Seksi Penunjang Medis']);
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Keuangan & Akuntasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan','admin'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Seksi Keuangan & Akuntasi');
                })
                ->orWhereHas('manager', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Seksi Keuangan & Akuntasi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi SDM'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan','manager'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Seksi SDM');
                })
                ->orWhereHas('manager', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Seksi SDM');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Umum'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan','manager'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Seksi Umum');
                })
                ->orWhereHas('manager', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Seksi Umum');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rawat Jalan & Home Care'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Rawat Jalan & Home Care');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rawat Inap Lantai 2'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Rawat Inap Lantai 2');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rawat Inap Lantai 3'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Rawat Inap Lantai 3');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Kamar Operasi & CSSD'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Kamar Operasi & CSSD');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Maternal & Perinatal'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Maternal & Perinatal');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Hemodialisa'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Hemodialisa');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit ICU'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit ICU');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit IGD'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit IGD');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Laboratorium'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Laboratorium');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Radiologi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Radiologi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Gizi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Gizi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rekam Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Rekam Medis');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Laundry'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Laundry');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Pendaftaran'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Pendaftaran');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Farmasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Farmasi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rehabilitasi Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Rehabilitasi Medis');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Keuangan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Keuangan');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Akuntansi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Akuntansi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Kasir'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Kasir');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Casemix'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Casemix');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Kepegawaian & Diklat'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Kepegawaian & Diklat');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Keamanan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Keamanan');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Transportasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Transportasi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Pramu Kantor'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Pramu Kantor');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Logistik'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Logistik');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Sanitasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit Sanitasi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit IPSRS'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiApproved = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','approved:Kepala Unit IPSRS');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //Cuti Rejected
        if($jabatanUser == 'Direktur'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kabag
                $kabagQuery = Cuti::whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    ->whereIn('status_manager', ['rejected:Direktur'])
                    ;
                });
                $adminQuery = Cuti::whereHas('admin', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', ['rejected:Direktur'])
                    ->whereIn('status_manager', ['rejected:Direktur'])
                    ;
                });
            
                $cutiRejected = $kabagQuery->union($adminQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Bagian Pelayanan Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kasi
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    ->where('status_manager','rejected:Kepala Bagian Pelayanan Medis')
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanConfigs){
                    $q
                    ->whereIn('jabatan',$bawahanConfigs['jabatan'])
                    ->whereIn('unit',$bawahanConfigs['unit'])
                    ->whereIn('status_admin',$bawahanConfigs['status_admin'])
                    ->where('status_manager','rejected:Kepala Bagian Pelayanan Medis');
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Bagian Pelayanan Medis');
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiRejected = $kasiQuery->union($karyawanQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Bagian Penunjang Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kasi
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    ->where('status_manager','rejected:Kepala Bagian Penunjang Medis')
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanKabagPenunjangMedis){
                    $q
                    ->whereIn('jabatan',$bawahanKabagPenunjangMedis['jabatan'])
                    ->whereIn('unit',$bawahanKabagPenunjangMedis['unit'])
                    ->whereIn('status_admin',$bawahanKabagPenunjangMedis['status_admin'])
                    ->where('status_manager','rejected:Kepala Bagian Penunjang Medis');
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Bagian Penunjang Medis');
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiRejected = $kasiQuery->union($karyawanQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Bagian Administrasi Umum & Keuangan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                //Menyimpan data bawahan langsung yaitu Kasi
                $kasiQuery = Cuti::whereHas('manager', function ($q) use ($config) {
                    $q->whereIn('jabatan', $config['jabatan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin', $config['status_admin'])
                    ->where('status_manager','rejected:Kepala Bagian Administrasi Umum & Keuangan')
                    ;
                });
                //Menyimpan data bawahan 2 tingkat yaitu Kanit
                $kanitQuery = Cuti::whereHas('manager', function ($q) use ($bawahanKabagAdmUmumKeuangan){
                    $q
                    ->whereIn('jabatan',$bawahanKabagAdmUmumKeuangan['jabatan'])
                    ->whereIn('unit',$bawahanKabagAdmUmumKeuangan['unit'])
                    ->whereIn('status_admin',$bawahanKabagAdmUmumKeuangan['status_admin'])
                    ->where('status_manager','rejected:Kepala Bagian Administrasi Umum & Keuangan');
                });
                //Menyimpan data bawahan 3 tingkat yaitu Karyawan
                $karyawanQuery = Cuti::whereHas('karyawan', function ($q) use ($config) {
                    $q->whereIn('jabatan',['karyawan'])
                    ->whereIn('unit', $config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Bagian Administrasi Umum & Keuangan');
                });
            
                $kasiQuery = $kasiQuery->union($kanitQuery);
                $cutiRejected = $kasiQuery->union($karyawanQuery)
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Keperawatan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan','manager'])
                ->whereHas('manager', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Seksi Keperawatan');
                })
                ->orWhereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Seksi Keperawatan');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }elseif($jabatanUser == 'Kepala Seksi Penunjang Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan','manager'])
                ->whereHas('manager', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Seksi Penunjang Medis');
                })
                ->orWhereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Seksi Penunjang Medis');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi IGD & Klinik Umum'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Seksi IGD & Klinik Umum');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Keuangan & Akuntansi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Seksi Keuangan & Akuntansi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi SDM'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Seksi SDM');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Seksi Umum'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Seksi Umum');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rawat Jalan & Home Care'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Rawat Jalan & Home Care');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rawat Inap Lantai 2'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Rawat Inap Lantai 2');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rawat Inap Lantai 3'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Rawat Inap Lantai 3');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Kamar Operasi & CSSD'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Kamar Operasi & CSSD');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Maternal & Perinatal'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Maternal & Perinatal');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Hemodialisa'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Hemodialisa');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit ICU'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit ICU');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit IGD'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit IGD');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Laboratorium'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Laboratorium');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Radiologi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Radiologi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Gizi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Gizi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rekam Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Rekam Medis');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Laundry'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Laundry');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Pendaftaran'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Pendaftaran');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Farmasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Farmasi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Rehabilitasi Medis'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Rehabilitasi Medis');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Keuangan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Keuangan');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Akuntansi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Akuntansi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Kasir'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Kasir');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Casemix'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Casemix');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Kepegawaian & Diklat'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Kepegawaian & Diklat');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Keamanan'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Keamanan');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Transportasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Transportasi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Pramu Kantor'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Pramu Kantor');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Logistik'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Logistik');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit Sanitasi'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit Sanitasi');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        elseif($jabatanUser == 'Kepala Unit IPSRS'){
            if(isset($jabatanConfigs[$jabatanUser])){
                $config = $jabatanConfigs[$jabatanUser];
                $cutiRejected = Cuti::with(['karyawan'])
                ->whereHas('karyawan', function ($query) use ($config){
                    $query->whereIn('jabatan',$config['jabatan'])
                    ->whereIn('unit',$config['unit'])
                    ->whereIn('status_admin',$config['status_admin'])
                    ->where('status_manager','rejected:Kepala Unit IPSRS');
                })
                ->orderBy('tanggal_mulai', 'desc')
                ->paginate(10);
            }
        }
        $filterDataManager = ['Direktur','Kepala Bagian Pelayanan Medis','Kepala Bagian Penunjang Medis','Kepala Bagian Administrasi Umum & Keuangan','Kepala Seksi Keperawatan','Kepala Seksi Penunjang Medis','Kepala Seksi Keuangan & Akuntansi','Kepala Seksi SDM','Kepala Seksi Umum','Kepala Unit Teknologi Informasi','Kepala Unit Kepegawaian & Diklat'];
        // dd($filterDataManager);
        $units = Karyawan::pluck('unit')->toArray();
        return view('persetujuan', compact(['cutiApproved','cutiRejected','user','role','pendingCuti','jabatanUser','units','filterDataManager']));
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

            'Kepala Unit Kepegawaian & Diklat' => 'Kepala Seksi SDM',
            'Kepala Unit Keamanan' => 'Kepala Seksi SDM',
            'Kepala Unit Transportasi' => 'Kepala Seksi SDM',
            'Kepala Unit Pramu Kantor' => 'Kepala Seksi SDM',

            'Kepala Unit Logistik' => 'Kepala Seksi Umum',
            'Kepala Unit Sanitasi' => 'Kepala Seksi Umum',
            'Kepala Unit IPSRS ' => 'Kepala Seksi Umum',

            
        
            'Laboratorium' => 'Kepala Unit Laboratorium',
            'Gizi' => 'Kepala Unit Gizi',
            'Radiologi' => 'Kepala Unit Radiologi',
            'Rekam Medis' => 'Kepala Unit Rekam Medis',
            'Laundry' => 'Kepala Unit Laundry',
            'Pendaftaran' => 'Kepala Unit Pendaftaran',
            'Rehabilitasi Medis' => 'Kepala Unit Rehabilitasi Medis',
            'Farmasi' => 'Kepala Unit Farmasi',

            'Rawat Jalan & Home Care' => 'Kepala Unit Rawat Jalan & Home Care',
            'Rawat Inap Lantai 2' => 'Kepala Unit Rawat Inap Lantai 2',
            'Rawat Inap Lantai 3' => 'Kepala Unit Rawat Inap Lantai 3',
            'Kamar Operasi & CSSD' => 'Kepala Unit Kamar Operasi & CSSD',
            'Maternal & Perinatal' => 'Kepala Unit Maternal & Perinatal',
            'Hemodialisa' => 'Kepala Unit Hemodialisa',
            'ICU' => 'Kepala Unit ICU',
            'IGD' => 'Kepala Unit IGD',

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
    public function status(Request $request){
        $managerId = Auth::user()->manager->id;
        $role = Auth::user()->role;
        $jabatan = Auth::user()->manager->jabatan;
        $aliasJabatan = implode(' ', array_slice(explode(' ', $jabatan), 0, 2));
        $statusManager = Cuti ::where('manager_id',$managerId)->pluck('status_manager');
        $statusAdmin = Cuti ::where('manager_id',$managerId)->pluck('status_admin');

        $atasan = Manager::pluck('jabatan');
        $status_manager_rej = [];
        foreach ($atasan as $atasanReject){
            $status_manager_rej[]='rejected:'.$atasanReject;
        }

        $status_manager_apr = [];
        foreach ($atasan as $atasanApprove){
            $status_manager_apr[]='approved:'.$atasanApprove;
        }

        $statusCuti = Cuti::with('manager')
        ->whereHas('manager')
        ->where('manager_id',$managerId)
        ->whereIn('status_manager',$statusManager)
        ->whereIn('status_admin',$statusAdmin)
        ->orderBy('tanggal_pengajuan','desc')
        ->paginate(15);
        if ($request->has('action') && $request->action == 'detail') {
            return $this->detailCuti($request->id);
        }
        
        return view('statusCuti',compact('statusCuti','role','aliasJabatan','status_manager_rej','status_manager_apr'));
    }

    public function download_pdf($id){
        $cuti = Cuti::with('manager','approval_atasan','approval_atasan_admin','approval_kasi','approval_kabag','approval_direktur','approval_admin')->findOrFail($id);
        $user = Auth::user();
        $alasanPenolakan = $cuti->alasan_penolakan;

        $ttdPath = $user->manager->ttd;
        $ttdAtasanAdmin = $cuti->approval_atasan_admin->ttd ?? null;
        $ttdKasi = $cuti->approval_kasi->ttd ?? null;
        $ttdKabag = $cuti->approval_kabag->ttd ?? null;
        $ttdAdmin = $cuti->approval_admin->ttd ?? null;
        $ttdDirektur = $cuti->approval_direktur->ttd ?? null;

        $ttdBase64 = null;
            if (Storage::disk('public')->exists($ttdPath)) {
                $ttdData = Storage::disk('public')->get($ttdPath);
                $ttdBase64 = 'data:image/png;base64,' . base64_encode($ttdData);
            }else{
                $ttdBase64 ="tidak ditemukan";
            }

        if($ttdAtasanAdmin != null){
            $ttd_atasan_admin=null;
            $ttd_atasan_admin = Storage::disk('public')->exists($ttdAtasanAdmin) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($ttdAtasanAdmin))
                : "tidak ditemukan";
        }else{
            $ttd_atasan_admin=null;
        }
        if($ttdKasi != null){
            $ttd_kasi=null;
            $ttd_kasi = Storage::disk('public')->exists($ttdKasi) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($ttdKasi))
                : "tidak ditemukan";
        }else{
            $ttd_kasi=null;
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

        $data = Pdf::loadView('export-pdf', [
            'cuti' => $cuti,
            'ttd_pemohon' => $ttdBase64,
            'ttd_atasan_admin' => $ttd_atasan_admin,
            'ttd_kasi' => $ttd_kasi,
            'ttd_kabag' => $ttd_kabag,
            'ttd_admin' => $ttd_admin,
            'ttd_direktur' => $ttd_direktur,
            'alasanPenolakan'=>$alasanPenolakan,
            'user'=>$user]);
        $now = now();

        // $data->setPaper('A4', 'portrait');
        // $data->setOption([
        // 'isPhpEnabled' => true,
        // 'isRemoteEnabled' => true,
        // 'defaultFont' => 'dejavu sans',
        // 'isHtml5ParserEnabled' => true
        // ]);

        $filename = 'Surat_Cuti_'.$cuti->manager->nama_lengkap.'_'.now()->format('Ymd_His').'.pdf';

        // return $data->download('Surat_Cuti_'.$cuti->manager->nama_lengkap.'_'.$now.'_'.$id.'.pdf');
        return response()->streamDownload(
        fn () => print($data->output()),
        $filename,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]
        );
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
        $detail = Cuti::with(['manager','approval_kabag','approval_kasi','approval_direktur','approval_admin'])->find($id);
        if (!$detail) {
            return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        }
    
        return response()->json([
            'status' => 'success',
            'data' => [
                'jenis_cuti' => $detail->jenis_cuti,
                'nama_lengkap' => $detail->manager->nama_lengkap,
                'unit' => $detail->manager->unit,
                'jabatan' => $detail->manager->jabatan,
                'no_pokok' => $detail->manager->no_pokok,
                'alamat' => $detail->manager->alamat,
                'tanggal_mulai' => $detail->tanggal_mulai,
                'tanggal_selesai' => $detail->tanggal_selesai,
                'tanggal_pengajuan' => $detail->tanggal_pengajuan,
                'tanggal_disetujui' => $detail->tanggal_disetujui,
                'jumlah_hari' => $detail->jumlah_hari,
                'alasan' => $detail->alasan,
                'sisa_cuti' => $detail->manager->sisa_cuti,
                'alasan_penolakan'=>$detail->alasan_penolakan,
                'nama_kasi'=>$detail->approval_kasi->nama_lengkap ?? '',
                'nama_kabag'=>$detail->approval_kabag->nama_lengkap ?? '',
                'nama_direktur'=>$detail->approval_direktur->nama_lengkap ?? '',
                'nama_admin'=>$detail->approval_admin->nama_lengkap ?? '',
                'ttd_kabag'=>$detail->ttd_kabag ?? '',
                'ttd_kasi'=>$detail->ttd_kasi ?? '',
                'ttd_direktur'=>$detail->approval_direktur->ttd ?? '',
                'ttd_admin'=>$detail->ttd_admin ?? '',
                'ttd_pemohon'=>$detail->manager->ttd ?? '',
            ]
        ]);
    }
    

    public function printCuti($id){
        $printCuti = Cuti::with(['manager','approval_kasi','approval_kabag','approval_admin','approval_direktur'])->findOrFail($id);
        
        return view('printCuti',compact('printCuti'));

    }
    public function logout(){
        auth::logout();
        return redirect()->route('login')->with('success','Anda berhasil logout');
    }
}
