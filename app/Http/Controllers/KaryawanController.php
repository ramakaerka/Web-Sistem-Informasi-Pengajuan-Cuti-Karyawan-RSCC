<?php

namespace App\Http\Controllers;

use App\Models\User;
use Date;
use Illuminate\Http\Request;
Use App\Models\Cuti;
Use App\Models\Karyawan;
Use App\Models\Manager;
use Illuminate\Support\Facades\Auth;
use Storage;
// use PDF;
use App\Events\CutiDiajukan;
use Barryvdh\DomPDF\Facade\PDF;

class KaryawanController extends Controller
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
        $user = Auth::user();
        $userprofile = $user->karyawan;
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
            'foto' => 'required|image|mimes:png|max:12000',
            'ttd' => 'required|image|mimes:png|max:1000'
        ]);
        $path = $request->file('ttd')->store('ttd','public');
        $pathFoto = $request->file('foto')->store('foto','public');
        $karyawan = Karyawan::where('user_id', $user->id)->first();
        if($karyawan){
            if ($karyawan->ttd){
                Storage::delete($karyawan->ttd);
            }
            $karyawan->update([
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
        Karyawan::create([
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
        return redirect()->route('karyawan.profile')->with('profile success','Data profile telah disimpan');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'signature' => 'required|string|starts_with:data:image/png;base64,'
        ]);
        $user = Auth::user();
        $karyawan = Karyawan::where('user_id', $user->id)->first();

         try {
            $imageData = str_replace('data:image/png;base64,', '', $request->signature);
            $imageData = str_replace(' ', '+', $imageData);
            $path = 'ttd/' . uniqid() . '.png';
            
            // dd($path);
            Storage::disk('public')->put($path, base64_decode($imageData));
            
            if($karyawan){
                if($karyawan->ttd){
                    Storage::delete($karyawan->ttd);
                    // Simpan ke database jika diperlukan
                    $karyawan->update([
                        'ttd' => $path
                    ]);
                }else{
                    Karyawan::create([
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
        //untuk mengetahui user yang login
        $user = Auth::user();
        //untuk tombol edit profile
        $role=$user->role;
        //untuk menampilkan data profile user yang login
        $userprofile=$user->karyawan;
        return view('profile',compact('userprofile','role'));
    }
    public function pengajuan(){
        $user=Auth::user();
        $role=$user->role;
        return view('pengajuan',compact('role','user'));
    }
    public function store(Request $request){
        // dd($request);
        $request->validate([
            'jenis_cuti' => [
                'required', 'string',
                function ($attribute, $value, $fail) use ($request) {
                    $user = Auth::user(); 
                    $gender = $user->karyawan->jenis_kelamin;

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
        $hari_ini = new \DateTime();
        $hari_ini->setTime(0,0,0);
        $tanggal_mulai = new \Datetime($request->tanggal_mulai);
        // dd($hari_ini);
        $tanggal_selesai = new \Datetime($request->tanggal_selesai);
        $jumlah_hari = $tanggal_mulai->diff($tanggal_selesai)->days;

        $jenisCuti = $request->jenis_cuti;
        $user = Auth::user()->karyawan;
        $karyawan=$user;
        $unitUser = $user->unit;
       

        if(!$user->ttd)
        {
            return redirect()->back()->with('no changes','Sebelum mengajukan cuti, anda harus mengupload tanda tangan digital di halaman Profile');
        }
        else{
            if ($jenisCuti === 'cuti_melahirkan') {
                
                if ($karyawan->jenis_kelamin !== 'P') {
                    return redirect()->back()->with('no changes', 'Cuti Melahirkan hanya dapat diajukan oleh karyawan perempuan.');
                }
        
                // durasi cuti_melahirkan 90 hari
                if ($jumlah_hari > 90) {
                    return redirect()->back()->with('no changes', 'Cuti Melahirkan maksimal 30 hari.');
                }
        
                $cuti=Cuti::create([
                    'karyawan_id' => $karyawan->id,
                    'jenis_cuti' => $request->jenis_cuti,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'jumlah_hari' => $jumlah_hari,
                    'alasan' => $request->alasan,
                    'surat_keterangan' => $request->surat_keterangan ?? null,
                ]);
                // event(new CutiDiajukan($cuti,'karyawan'));
                return redirect()->route('karyawan.status')->with('pengajuan success', 'Pengajuan Cuti Melahirkan berhasil dilakukan, menunggu persetujuan admin.');
            }
            elseif($jenisCuti == 'cuti_tahunan'){
                
                $dates = json_decode($request->selected_dates_array);
                $jumlahHari = count($dates);
                $sisaCuti = $karyawan->sisa_cuti + $karyawan->sisa_cuti_sebelumnya;
                    
                if ($jumlahHari > $sisaCuti) {
                    return back()->withErrors([
                        'selected_dates_array' => "Jatah cuti tahunan Anda hanya {$sisaCuti} hari, tidak mencukupi untuk {$jumlahHari} hari cuti."
                    ]);
                }

                // Simpan semua tanggal terpilih
                foreach ($dates as $date) {
                    $cuti = Cuti::create([
                        'karyawan_id' => $karyawan->id,
                        'jenis_cuti' => $jenisCuti,
                        'tanggal_mulai' => $date,
                        'tanggal_selesai' => $date, 
                        'jumlah_hari' => 1,
                        'alasan' => $request->alasan,
                        'surat_keterangan' =>$request->surat_keterangan ?? null
                    ]);
                
                }
            }
            elseif($jenisCuti == 'cuti_sakit' || $jenisCuti == 'cuti_lainnya'){
                $dates = json_decode($request->selected_dates_array);
                $jumlahHari = count($dates);

                if($request->file('surat_keterangan')){
                    $pathSuket = $request->file('surat_keterangan')->store('suket','public');
                }
                $sisaCuti = $karyawan->sisa_cuti + $karyawan->sisa_cuti_sebelumnya;
                    
                if ($jumlahHari > $sisaCuti) {
                    return back()->withErrors([
                        'jumlah_hari' => "Jatah cuti sakit Anda hanya {$sisaCuti} hari, tidak mencukupi untuk {$jumlahHari} hari cuti."
                    ]);
                }

                if($jumlahHari > 3){
                    foreach($dates as $date){
                        $cuti = Cuti::create([
                            'karyawan_id' => $karyawan->id,
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
                    $cuti = Cuti::create([
                        'karyawan_id' => $karyawan->id,
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
                $cuti->karyawan->update(['sisa_cuti' => $cuti->karyawan->sisa_cuti - $jumlahHari]);
                $cuti->karyawan->refresh();
            }
            else{
                if (($karyawan->sisa_cuti + $karyawan->sisa_cuti_sebelumnya) < $jumlah_hari){
                    return redirect()->back()->with('no changes','Sisa cuti tidak mencukupi');
                }

                if($tanggal_mulai >= $hari_ini){
                    $cuti = Cuti::whereHas('karyawan', function ($query) use ($user){
                        $query->where('karyawan_id',$user->id);
                    })
                    ->where('tanggal_mulai',$request->tanggal_mulai)->first();

                    $unitCuti = Cuti::whereHas('karyawan', function ($query) use ($unitUser,$request) {
                        $query->where('unit', $unitUser)
                        ->where('tanggal_mulai', $request->tanggal_mulai);
                    })
                    ->count();

                    $unitConfig = [
                        'Rawat Jalan & Home Care' => 2,
                        'Rawat Inap Lantai 2' => 2,                    
                        'Rawat Inap Lantai 3' => 2,
                        'Kamar Operasi & CSSD' => 1,
                        'Maternal & Perinatal' => 1,
                        'Hemodialisa' => 2,
                        'ICU' => 2,
                        'IGD' => 2,
                        
                        'Laboratorium' => 1,
                        'Radiologi' => 1,
                        'Rekam Medis' => 1,
                        'Gizi' => 1,
                        'Laundry' => 1,
                        'Pendaftaran' => 1,
                        'Farmasi' => 1,
                        'Rehabilitasi Medis' => 1,
                        
                        'Keuangan' => 1,
                        'Akuntansi' => 1,
                        'Kasir' => 1,
                        'Casemix' => 1,
                        
                        'Kepegawaian & Diklat' => 1,
                        'Keamanan' => 1,
                        'Transportasi' => 1,
                        'Pramu Kantor' => 1,
                        
                        'Logistik' => 1,
                        'Sanitasi' => 1,
                        'IPSRS' => 1,

                        'Teknologi Informasi' => 1,
                        'Kesekretariatan' => 1,
                        'Humas & Pemasaran' => 1,
                    ];
                
                    
                    if(!isset($unitConfig[$unitUser])){   
                        return redirect()->back()->with('no changes','Unit anda tidak ada pada struktur organisasi');  
                    }
                    else {
                        $value = $unitConfig[$unitUser];

                        if ($unitCuti >= $value){ 
                            return redirect()->back()->with('no changes','Unit anda telah mencapai batas maksimal cuti pada tanggal tersebut. Silahkan mengajukan di tanggal lain');
                        }
                        else {
                            if($cuti){
                                return redirect()->back()->with('no changes','Anda sudah memiliki pengajuan cuti di tanggal ini');
                            }
                            else {
                                $cuti = Cuti::create([
                                    'karyawan_id' => $karyawan->id,
                                    'jenis_cuti' => $request->jenis_cuti,
                                    'tanggal_mulai' => $request->tanggal_mulai,
                                    'tanggal_selesai' => $tanggal_selesai,
                                    'jumlah_hari' => $jumlah_hari,
                                    'alasan' => $request->alasan,
                                    'surat_keterangan' => $request->surat_keterangan
                                ]);
                                
                                // event(new CutiDiajukan($cuti, 'karyawan'));
                                \Log::info('Event CutiDiajukan dipicu untuk cuti ID: ' . $cuti->id);
                            }  
                        }
                        }
                    }
                else {
                    return redirect()->back()->with('no changes','Tanggal mulai harus setelah hari ini!');
                }
            }
            
        return redirect()->route('karyawan.email',$cuti->id)->with('pengajuan success','Pengajuan cuti berhasil dilakukan');
        }
    }
    public function status(Request $request){
        
        $karyawanId = Auth::user()->karyawan->id;
        $role = Auth::user()->role;
        $aliasJabatan = Auth::user()->karyawan->jabatan;
        $statusManager = Cuti::where('karyawan_id',$karyawanId)->pluck('status_manager');
        $statusAdmin = Cuti::where('karyawan_id',$karyawanId)->pluck('status_admin');

        $atasan = Manager::pluck('jabatan');
        $status_manager_rej = [];
        foreach ($atasan as $atasanReject){
            $status_manager_rej[]='rejected:'.$atasanReject;
        }

        $status_manager_apr = [];
        foreach ($atasan as $atasanApprove){
            $status_manager_apr[]='approved:'.$atasanApprove;
        }

        

        $statusCuti = Cuti::with('karyawan')
        ->whereHas('karyawan')
        ->where('karyawan_id',$karyawanId)
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
        $cuti = Cuti::with(['karyawan','approval_atasan_admin','approval_atasan','approval_kasi','approval_kabag','approval_admin','approval_direktur'])->findOrFail($id);
        $user = Auth::user();
        $alasanPenolakan = $cuti->alasan_penolakan;

        $ttdPath = $user->karyawan->ttd;
        $ttdKanit = $cuti->approval_atasan->ttd ?? null;
        $ttdKanitAdmin = $cuti->approval_atasan_admin->ttd ?? null;
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
            
        if($ttdKanit != null){
            $ttd_kanit=null;
            $ttd_kanit = Storage::disk('public')->exists($ttdKanit) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($ttdKanit))
                : "tidak ditemukan";
        }else{
            $ttd_kanit = null;
        }
        
        if($ttdKanitAdmin != null){
            $ttd_kanit_admin=null;
            $ttd_kanit_admin = Storage::disk('public')->exists($ttdKanitAdmin) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($ttdKanitAdmin))
                : "tidak ditemukan";
        }else{
            $ttd_kanit_admin = null;
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

        $data = PDF::loadView('export-pdf', [
            'cuti' => $cuti,
            'ttd_pemohon' => $ttdBase64,
            'ttd_kanit' => $ttd_kanit,
            'ttd_kanit_admin' => $ttd_kanit_admin,
            'ttd_kasi' => $ttd_kasi,
            'ttd_kabag' => $ttd_kabag,
            'ttd_admin' => $ttd_admin,
            'ttd_direktur' => $ttd_direktur,
            'alasanPenolakan' => $alasanPenolakan,
            'user'=>$user
        ]);

        $filename = 'Surat_Cuti_'.$cuti->karyawan->nama_lengkap.'_'.now()->format('Ymd_His').'.pdf';

        return response()->streamDownload(
        fn () => print($data->output()),
        $filename,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function detailCuti($id){
        $detail = Cuti::with('karyawan','approval_kasi','approval_atasan','approval_atasan_admin','approval_kabag','approval_admin','approval_atasan_admin','approval_direktur')->find($id);
        if (!$detail) {
            return response()->json(['message' => 'Cuti tidak ditemukan'], 404);
        }
    
        return response()->json([
            'status' => 'success',
            'data' => [
                'jenis_cuti' => $detail->jenis_cuti,
                'nama_lengkap' => $detail->karyawan->nama_lengkap,
                'unit' => $detail->karyawan->unit,
                'jabatan' => $detail->karyawan->jabatan,
                'no_pokok' => $detail->karyawan->no_pokok,
                'alamat' => $detail->karyawan->alamat,
                'tanggal_mulai' => $detail->tanggal_mulai,
                'tanggal_selesai' => $detail->tanggal_selesai,
                'tanggal_pengajuan' => $detail->tanggal_pengajuan,
                'tanggal_disetujui' => $detail->updated_at,
                'jumlah_hari' => $detail->jumlah_hari,
                'alasan' => $detail->alasan,
                'sisa_cuti' => $detail->karyawan->sisa_cuti + $detail->karyawan->sisa_cuti_sebelumnya,
                'alasan_penolakan'=>$detail->alasan_penolakan,
                'nama_atasan'=>$detail->approval_atasan->nama_lengkap ?? $detail->approval_atasan_admin->nama_lengkap ? : '',
                'nama_kasi'=>$detail->approval_kasi->nama_lengkap ?? '',
                'nama_kabag'=>$detail->approval_kabag->nama_lengkap ?? '',
                'nama_admin'=>$detail->approval_admin->nama_lengkap ?? '',
                'ttd_kabag'=>$detail->ttd_kabag ?? '',
                'ttd_kasi'=>$detail->ttd_kasi ?? '',
                'ttd_atasan'=>$detail->approval_atasan->ttd ?? $detail->approval_atasan_admin->ttd ?? '',
                'ttd_admin'=>$detail->ttd_admin ?? '',
                'ttd_pemohon'=>$detail->karyawan->ttd ?? '',
            ]
        ]);
    }
    public function logout(){
        auth::logout();
        return redirect()->route('login')->with('success','Anda berhasil logout');
    }

    
}
