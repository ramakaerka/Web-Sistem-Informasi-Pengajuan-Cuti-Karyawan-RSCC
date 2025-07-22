<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Validation\Rules\Exists;
use Livewire\Component;
use App\Models\Cuti;
use App\Models\Karyawan;
use App\Models\Manager;
use App\Models\Admin;
use Livewire\WithPagination;
use App\Exports\CutiExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Events\CutiDiajukan;
use Barryvdh\DomPDF\Facade\Pdf;
use Storage;
use Carbon\Carbon;

class CutiDashboard extends Component
{
    use WithPagination;

    //Notifikasi realtime livewire
    public array $notification;
    protected $listeners = ['CutiDiajukan'];

    //Grafik Tren
    public string $chartYear;
    public array $chartData = []; 
    public array $chartDataRejected = []; 
    public array $chartDataApproved = []; 

    //Layout
    protected $paginationTheme = 'bootstrap';
    protected $layout = 'layouts.app';

    //Filter Data
    public $bulan = 'Semua Bulan';
    public $tahun = 'Semua Tahun';
    public $unit = 'Semua Unit';

    // Data yang akan ditampilkan
    public $karyBelumCuti = 0;
    public $jumlahCuti = 0;
    public $karyawanSedangCuti = 0;
    public $jumlahApproved = 0;
    public $jumlahRejected = 0;

    //Detail Modal
    public $jumlahApprovedDetail =[];
    public $jumlahRejectedDetail =[];
    public $jumlahSedangCuti =[];
    public $jumlahBelumCuti =[];

    //Sorting Data
    public $perPage = 10; // Pagination
    public $sortField = 'tanggal_mulai';
    public $sortDirection = 'desc';

    //Search Data
    public $search = '';

    //Detail Modal
    public $selectedCuti = null;
    public $showModal = false;
    public $showModalRejected = false;
    public $showModalSedangCuti = false;
    public $showModalBelumCuti = false;


    //Method Mount
    public function mount()
    {
      
        
        // Inisialisasi grafik
        \Log::debug("Mount dipanggil");
        $this->chartYear = date('Y');
        $this->chartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'values' => array_fill(0, 12, 0),
        ];
        $this->chartDataRejected = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'values' => array_fill(0, 12, 0),
        ];
        $this->chartDataApproved = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'values' => array_fill(0, 12, 0),
        ];
        $this->updateChartData();
    }

    public function render()
    {
        $todayDate = Carbon::now('Asia/Jakarta')->toDateString();
        $query = Cuti::query()->with(['karyawan', 'manager', 'admin']);

        if ($this->bulan != 'Semua Bulan') {
           $query->whereMonth('tanggal_mulai', $this->bulan);
        }

        if ($this->tahun != 'Semua Tahun') {
            $query->whereYear('tanggal_mulai', $this->tahun);
        }

        if ($this->unit != 'Semua Unit') {
            $query->where(function($q) {
                $q->whereHas('karyawan', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('manager', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('admin', fn($q) => $q->where('unit', $this->unit));
            });
        }
        
        
        // Hitung data
        $this->karyBelumCuti = Karyawan::whereDoesntHave('cuti')->count();
        $this->jumlahCuti = $query->count();

        $this->jumlahApproved = (clone $query)
        ->whereIn('status_admin', ['approved:Admin','approved:Direktur'])
        ->count();

        $this->jumlahRejected = (clone $query)
        ->where(function($q) {
            $q->whereIn('status_admin', ['rejected:Admin','rejected:Direktur'])
              ->orWhere('status_manager', 'LIKE','%rejected:%'
              );
        })
        ->count();

        $todayQuery = $query->whereDate('tanggal_mulai', $todayDate)->whereIn('status_admin',['approved:Admin','approved:Direktur']);
        if ($this->unit != 'Semua Unit') {
            $todayQuery->where(function($q) {
                $q->whereHas('karyawan', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('manager', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('admin', fn($q) => $q->where('unit', $this->unit));
            });
        }
        $this->karyawanSedangCuti = $todayQuery->count();

        $sortingQuery = Cuti::query()
        ->with(['karyawan', 'manager', 'admin'])
        ->when($this->sortField === 'nama_lengkap', function($q) {
            $q->orderBy(
                Karyawan::select('nama_lengkap')
                    ->whereColumn('karyawan.id', 'proses_cuti.karyawan_id')
                    ->limit(1),
                $this->sortDirection
            )->orderBy(
                Manager::select('nama_lengkap')
                    ->whereColumn('manager.id', 'proses_cuti.manager_id')
                    ->limit(1),
                $this->sortDirection
            )->orderBy(
                Admin::select('nama_lengkap')
                    ->whereColumn('admin.id', 'proses_cuti.admin_id')
                    ->limit(1),
                $this->sortDirection
            );
        }, function($q) {
            $q->orderBy($this->sortField, $this->sortDirection);
        })
        ->when($this->bulan != 'Semua Bulan', function($query) {
            $query->whereMonth('tanggal_mulai', $this->bulan);
         })
         ->when($this->tahun != 'Semua Tahun', function ($query) {
            $query->whereYear('tanggal_mulai', $this->tahun);
        })
        ->when($this->unit != 'Semua Unit', function ($query) {
            $query->where(function($q) {
                $q->whereHas('karyawan', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('manager', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('admin', fn($q) => $q->where('unit', $this->unit));
            });
        })
        ->when($this->search, function($q) {
            $q->where(function($query) {
                $query->whereHas('karyawan', fn($q) => $q->where('nama_lengkap', 'like', '%'.$this->search.'%'))
                      ->orWhereHas('manager', fn($q) => $q->where('nama_lengkap', 'like', '%'.$this->search.'%'))
                      ->orWhereHas('admin', fn($q) => $q->where('nama_lengkap', 'like', '%'.$this->search.'%'));
            });
        });

        $dataCuti = $sortingQuery->paginate($this->perPage);
        $rekap = $dataCuti;
        
        return view('livewire.cuti-dashboard',[
            'dataCuti' => $dataCuti, 'rekap' =>$rekap
        ])->layout('layouts.app');
    }
    public function changeYear()
    {
        \Log::debug("changeYear dipanggil", ['values' => $this->chartYear]);
        $this->updateChartData();
    }
    public function updateChartData()
    {
            $this->chartData['values'] = [...$this->fetchDataForYear($this->chartYear)];
            $this->chartDataRejected['values'] = [...$this->fetchCutiRejected($this->chartYear)];
            $this->chartDataApproved['values'] = [...$this->fetchCutiApproved($this->chartYear)];

            \Log::debug("Data chart Jumlah Cuti", $this->chartData);
            \Log::debug("Data chart Jumlah Cuti Rejected", $this->chartDataRejected);
            \Log::debug("Data chart Jumlah Cuti Approved", $this->chartDataApproved);
    }
    protected function fetchDataForYear($tahun): array
    {
        $data = Cuti::query()
            ->selectRaw('MONTH(tanggal_mulai) as month, COUNT(*) as total')
            ->whereYear('tanggal_mulai', $this->chartYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

            $values = array_fill(1, 12, 0);
            foreach ($data as $month => $total) {
                $values[$month] = $total;
            }

            \Log::debug('fechDataForYear di panggil',$values);
            return array_values($values);
    }
    protected function fetchCutiRejected($chartYear): array
    {
        $mapReject = [];
        $jabatanList = Manager::pluck('jabatan')->unique();

        foreach ($jabatanList as $jabatan) {
            $mapReject["rejected:{$jabatan}"] = "rejected:{$jabatan}";
        }

        $data = Cuti::query()
            ->selectRaw('MONTH(tanggal_mulai) as month, COUNT(*) as total')
            ->whereYear('tanggal_mulai', $this->chartYear)
            ->whereIn('status_manager',array_values($mapReject))
            ->orWhereIn('status_admin',['rejected:Direktur'])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

            $values = array_fill(1, 12, 0);
            foreach ($data as $month => $total) {
                $values[$month] = $total;
            }

            \Log::debug('fetchCutiRejected di panggil',$values);
            return array_values($values);
    }
    protected function fetchCutiApproved($chartYear): array
    {
        $data = Cuti::query()->with(['karyawan','manager','admin'])
            ->selectRaw('MONTH(tanggal_mulai) as month, COUNT(*) as total')
            ->whereYear('tanggal_mulai', $this->chartYear)
            ->whereIn('status_admin',['approved:Admin','approved:Direktur'])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

            $values = array_fill(1, 12, 0);
            foreach ($data as $month => $total) {
                $values[$month] = $total;
            }

            \Log::debug('fetchCutiApproved di panggil',$values);
            return array_values($values);
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field 
            ? $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : 'asc';
        
        $this->sortField = $field;
    }

    public function exportExcel()
    {
        $query = $this->getQuery(); 
        return Excel::download(new CutiExport($query), 'data-cuti-'.now()->format('Ymd').'.xlsx');
    }

    protected function getQuery()
    {
        return Cuti::query()
        ->with(['karyawan', 'manager', 'admin'])
        ->when($this->sortField === 'nama_lengkap', function($q) {
            $q->orderBy(
                Karyawan::select('nama_lengkap')
                    ->whereColumn('karyawan.id', 'proses_cuti.karyawan_id')
                    ->limit(1),
                $this->sortDirection
            )->orderBy(
                Manager::select('nama_lengkap')
                    ->whereColumn('manager.id', 'proses_cuti.manager_id')
                    ->limit(1),
                $this->sortDirection
            )->orderBy(
                Admin::select('nama_lengkap')
                    ->whereColumn('admin.id', 'proses_cuti.admin_id')
                    ->limit(1),
                $this->sortDirection
            );
        }, function($q) {
            $q->orderBy($this->sortField, $this->sortDirection);
        })
        ->when($this->bulan != 'Semua Bulan', function($query) {
            $query->whereMonth('tanggal_mulai', $this->bulan);
         })
        ->when($this->tahun != 'Semua Tahun', function ($query) {
            $query->whereYear('tanggal_mulai', $this->tahun);
        })
        ->when($this->unit != 'Semua Unit', function ($query) {
            $query->where(function($q) {
                $q->whereHas('karyawan', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('manager', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('admin', fn($q) => $q->where('unit', $this->unit));
            });
        })
        ->when($this->search, function($q) {
            $q->where(function($query) {
                $query->whereHas('karyawan', fn($q) => $q->where('nama_lengkap', 'like', '%'.$this->search.'%'))
                      ->orWhereHas('manager', fn($q) => $q->where('nama_lengkap', 'like', '%'.$this->search.'%'))
                      ->orWhereHas('admin', fn($q) => $q->where('nama_lengkap', 'like', '%'.$this->search.'%'));
            });
        });
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function showDetail()
    {
        $this->jumlahApprovedDetail = Cuti::with(['karyawan', 'manager', 'admin'])
            ->whereIn('status_admin', ['approved:Admin','approved:Direktur'])
            ->when($this->bulan != 'Semua Bulan', function($query) {
                $query->whereMonth('tanggal_mulai', $this->bulan);
            })
            ->when($this->tahun != 'Semua Tahun', function($query) {
                $query->whereYear('tanggal_mulai', $this->tahun);
            })
            ->when($this->unit != 'Semua Unit', function($query) {
                $query->where(function($q) {
                    $q->whereHas('karyawan', fn($q) => $q->where('unit', $this->unit))
                    ->orWhereHas('manager', fn($q) => $q->where('unit', $this->unit))
                    ->orWhereHas('admin', fn($q) => $q->where('unit', $this->unit));
                });
            })
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $this->showModal = true;
    }
    public function showDetailRejected()
    {

        $query =Cuti::with(['karyawan', 'manager', 'admin']);
        if ($this->bulan != 'Semua Bulan') {
           $query->whereMonth('tanggal_mulai', $this->bulan);
        }

        if ($this->tahun != 'Semua Tahun') {
            $query->whereYear('tanggal_mulai', $this->tahun);
        }

        if ($this->unit != 'Semua Unit') {
            $query->where(function($q) {
                $q->whereHas('karyawan', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('manager', fn($q) => $q->where('unit', $this->unit))
                  ->orWhereHas('admin', fn($q) => $q->where('unit', $this->unit));
            });
        }

        $this->jumlahRejectedDetail = (clone $query)
        ->where(function($q) {
            $q->where('status_admin', 'LIKE','%rejected:%')
              ->orWhere('status_manager', 'LIKE','%rejected:%');
        })
        ->orderBy('tanggal_mulai','desc')
        ->get();
        

        $this->showModalRejected = true;
    }
    public function showDetailSedangCuti()
    {
        $todayDate = Carbon::now('Asia/Jakarta')->toDateString();

        $this->jumlahSedangCuti = Cuti::with(['karyawan', 'manager', 'admin'])
            ->whereDate('tanggal_mulai',$todayDate)
            ->whereIn('status_admin', ['approved:Admin','approved:Direktur'])
            ->when($this->bulan != 'Semua Bulan', function($query) {
                $query->whereMonth('tanggal_mulai', $this->bulan);
            })
            ->when($this->tahun != 'Semua Tahun', function($query) {
                $query->whereYear('tanggal_mulai', $this->tahun);
            })
            ->when($this->unit != 'Semua Unit', function($query) {
                $query->where(function($q) {
                    $q->whereHas('karyawan', fn($q) => $q->where('unit', $this->unit))
                    ->orWhereHas('manager', fn($q) => $q->where('unit', $this->unit))
                    ->orWhereHas('admin', fn($q) => $q->where('unit', $this->unit));
                });
            })
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $this->showModalSedangCuti = true;
    }
    public function showDetailBelumCuti()
    {
        $this->jumlahBelumCuti = Karyawan::
            whereDoesntHave('cuti')
            ->when($this->bulan != 'Semua Bulan', function($query) {
                $query->whereMonth('tanggal_mulai', $this->bulan);
            })
            ->when($this->tahun != 'Semua Tahun', function($query) {
                $query->whereYear('tanggal_mulai', $this->tahun);
            })
            ->when($this->unit != 'Semua Unit', function($query) {
                $query->where(function($q) {
                    $q->whereHas('karyawan', fn($q) => $q->where('unit', $this->unit))
                    ->orWhereHas('manager', fn($q) => $q->where('unit', $this->unit))
                    ->orWhereHas('admin', fn($q) => $q->where('unit', $this->unit));
                });
            })
            ->get();

        $this->showModalBelumCuti = true;
    }

public function closeModal()
{
    $this->showModal = false;
    $this->jumlahApprovedDetail = [];
}
public function closeModalRejected()
{
    $this->showModalRejected = false;
    $this->jumlahRejectedDetail = [];
}
public function closeModalSedangCuti()
{
    $this->showModalSedangCuti = false;
    $this->jumlahSedangCuti = [];
}
public function closeModalBelumCuti()
{
    $this->showModalBelumCuti = false;
    $this->jumlahBelumCuti = [];
}

public function downloadSuket($cutiId){
    $cuti = Cuti::with('karyawan','manager','admin','approval_atasan','approval_kasi','approval_kabag','approval_admin')->findOrFail($cutiId);

    $suket = $cuti->surat_keterangan ?? null;

    if($suket != null){
            $surat_keterangan=null;
            $surat_keterangan = Storage::disk('public')->exists($suket) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($suket))
                : "tidak ditemukan";
        }else{
            $surat_keterangan=null;
        }

    if($cuti->karyawan){
        $filename = 'Surat_Cuti_'.$cuti->karyawan->nama_lengkap.'_'.now()->format('Ymd_His').'.pdf';
    }
    elseif($cuti->manager){
        $filename = 'Surat_Cuti_'.$cuti->manager->nama_lengkap.'_'.now()->format('Ymd_His').'.pdf';
    }
    elseif($cuti->admin){
        $filename = 'Surat_Cuti_'.$cuti->admin->nama_lengkap.'_'.now()->format('Ymd_His').'.pdf';
    }

    return view('view-suket',['cuti' =>$cuti,'surat_keterangan'=>$surat_keterangan]);

 
}

public function downloadPdf($cutiId)
{
    $cuti = Cuti::with('karyawan','manager','admin','approval_atasan','approval_atasan_admin','approval_kasi','approval_kabag','approval_admin','approval_direktur')->findOrFail($cutiId);
    $user = auth()->user();
    $alasanPenolakan = $cuti->alasan_penolakan;

    // Proses tanda tangan (sama seperti sebelumnya)
    if($cuti->karyawan){
        $ttdPath = $cuti->karyawan->ttd;
        $ttdKanit = $cuti->approval_atasan->ttd ?? null;
        $ttdKanitAdmin = $cuti->approval_atasan_admin->ttd ?? null;
        $ttdKasi = $cuti->approval_kasi->ttd ?? null;
        $ttdKabag = $cuti->approval_kabag->ttd ?? null;
        $ttdAdmin = $cuti->approval_admin->ttd ?? null;
        $ttdDirektur = $cuti->approval_direktur->ttd ?? null;
        $suket = $cuti->surat_keterangan ?? null;
    }
    elseif($cuti->manager){
        $ttdPath = $cuti->manager->ttd;
        $ttdKanit = $cuti->approval_atasan->ttd ?? null;
        $ttdKanitAdmin = $cuti->approval_atasan_admin->ttd ?? null;
        $ttdKasi = $cuti->approval_kasi->ttd ?? null;
        $ttdKabag = $cuti->approval_kabag->ttd ?? null;
        $ttdAdmin = $cuti->approval_admin->ttd ?? null;
        $ttdDirektur = $cuti->approval_direktur->ttd ?? null;
        $suket = $cuti->surat_keterangan ?? null;
    }
    elseif($cuti->admin){
        $ttdPath = $cuti->admin->ttd;
        $ttdKanit = $cuti->approval_atasan->ttd ?? null;
        $ttdKanitAdmin = $cuti->approval_atasan_admin->ttd ?? null;
        $ttdKasi = $cuti->approval_kasi->ttd ?? null;
        $ttdKabag = $cuti->approval_kabag->ttd ?? null;
        $ttdAdmin = $cuti->approval_admin->ttd ?? null;
        $ttdDirektur = $cuti->approval_direktur->ttd ?? null;
        $suket = $cuti->surat_keterangan ?? null;
    }

    $ttdBase64=null;
    $ttdBase64 = Storage::disk('public')->exists($ttdPath) 
        ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($ttdPath))
        : "tidak ditemukan";
    
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

        if($suket != null){
            $surat_keterangan=null;
            $surat_keterangan = Storage::disk('public')->exists($suket) 
                ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($suket))
                : "tidak ditemukan";
        }else{
            $surat_keterangan=null;
        }
    

    // Generate PDF
    $pdf = Pdf::loadView('export-pdf', [
        'cuti' => $cuti,
        'ttd_pemohon' => $ttdBase64,
        'ttd_kanit' => $ttd_kanit,
        'ttd_kanit_admin' => $ttd_kanit_admin,
        'ttd_kasi' => $ttd_kasi,
        'ttd_kabag' => $ttd_kabag,
        'ttd_admin' => $ttd_admin,
        'ttd_direktur' => $ttd_direktur,
        'user' => $user,
        'alasanPenolakan' => $alasanPenolakan
    ]);

    if($cuti->karyawan){
        $filename = 'Surat_Cuti_'.$cuti->karyawan->nama_lengkap.'_'.now()->format('Ymd_His').'.pdf';
    }
    elseif($cuti->manager){
        $filename = 'Surat_Cuti_'.$cuti->manager->nama_lengkap.'_'.now()->format('Ymd_His').'.pdf';
    }
    elseif($cuti->admin){
        $filename = 'Surat_Cuti_'.$cuti->admin->nama_lengkap.'_'.now()->format('Ymd_His').'.pdf';
    }

    // Return response download
    return response()->streamDownload(
        fn () => print($pdf->output()),
        $filename,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]
    );
}
}
