

<div>

<header>
    <nav class="navbar navbar-expand-sm container">
      <a class="navbar-brand" href="#">Dashboard Admin Cuti</a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      
    </nav>
  </header>

  <main class="container my-5 mt-4 flex-grow-1">
    <div id="notif-container">
      @foreach($notification as $notif)
          <div wire:key="{{ $notif['id'] }}" class="alert">
              {{ $notif['nama_lengkap'] }} mengajukan cuti
          </div>
      @endforeach
  </div>

    <section class="filter-section">
      <h2>Filter Data</h2>
      <div class="row g-3">
        <div class="col-12 col-sm-4">
          <label class="form-label">Bulan</label>
          <select wire:model.live="bulan">
            <option value="Semua Bulan">Semua Bulan</option>
            <option value="1">Januari</option>
            <option value="2">Februari</option>
            <option value="3">Maret</option>
            <option value="4">April</option>
            <option value="5">Mei</option>
            <option value="6">Juni</option>
            <option value="7">Juli</option>
            <option value="8">Agustus</option>
            <option value="9">September</option>
            <option value="10">Oktober</option>
            <option value="11">November</option>
            <option value="12">Desember</option>
          </select>
        </div>
        {{-- <div wire:loading>Loading...</div>       --}}
        <div class="col-12 col-sm-4">
          <label class="form-label">Tahun</label>
          <select wire:model.live="tahun">
            <option value="Semua Tahun">Semua Tahun</option>
            <option value="2025">2025</option>
            <option value="2024">2024</option>
            <option value="2023">2023</option>
            <option value="2022">2022</option>
            <option value="2021">2021</option>
          </select>
        </div>
        <div class="col-12 col-sm-4">
          <label class="form-label">Unit</label>
          <select wire:model.live="unit">
            <option value="Semua Unit">Semua Unit</option>
            <option value="SDM">SDM</option>
            <option value="Teknologi Informasi">IT</option>
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
            <option value="Logistik">Logistik</option>
            <option value="Sanitasi">Sanitasi</option>
            <option value="IPSRS">IPSRS</option>
            <option value="Keperawatan">Keperawatan</option>
            <option value="Penunjang Medis">Penunjang Medis</option>
            <option value="Keuangan & Akuntansi">Keuangan & Akuntansi</option>
            <option value="Umum">Umum</option>
            <option value="IGD & Klinik Umum">IGD & Klinik Umum</option>
            <option value="Pelayanan Medis">Pelayanan Medis</option>
            <option value="Bagian Penunjang Medis">Bagian Penunjang Medis</option>
            <option value="Bagian Administrasi Umum & Keuangan">Bagian Administrasi Umum & Keuangan</option>
            <option value="Humas & Pemasaran">Humas & Pemasaran</option>
            <option value="Kesekretariatan">Kesekretariatan</option>
            <option value="Direktur">Direktur</option>
          </select>
        </div>
      </div>
    </section>

    <section class="filter-section mt-3 mb-3">
      <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-custom p-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="card-icon icon-indigo">
                <i class="fas fa-calendar-alt"></i>
              </div>
              <div>
                <p class="card-title mb-1">Jumlah Cuti</p>
                <p class="card-value mb-0">{{ $jumlahCuti }}</p>
              </div>
            </div>
            
            <p class="card-note mt-3 mb-0">Filter berdasarkan Bulan dan Unit</p>
          </div>
        </div>
  
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-custom p-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="card-icon icon-orange">
                <i class="fa-solid fa-user-xmark"></i>
              </div>
              <div>
                <p class="card-title mb-1">Karyawan Yang Belum Ambil Cuti</p>
                <p class="card-value mb-0"> {{ $karyBelumCuti ?? 0 }}</p>
              </div>
            </div>
            <button 
                      wire:click="showDetailBelumCuti" 
                      class="btn btn-sm btn-info mt-2">
                      <i class="fas fa-eye"></i> Detail
            </button>
            <p class="card-note mt-3 mb-0">Filter berdasarkan Unit</p>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-custom p-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="card-icon icon-green">
                <i class="fas fa-user-check"></i>
              </div>
              <div>
                <p class="card-title mb-1">Karyawan Sedang Cuti Hari Ini</p>
                <p class="card-value mb-0"> {{ $karyawanSedangCuti ?? 0 }}</p>
              </div>
            </div>
            <button 
                      wire:click="showDetailSedangCuti" 
                      class="btn btn-sm btn-info mt-2">
                      <i class="fas fa-eye"></i> Detail
            </button>
            <p class="card-note mt-3 mb-0">Filter berdasarkan Unit</p>
          </div>
        </div>
  
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-custom p-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="card-icon icon-blue">
                <i class="fas fa-check-circle"></i>
              </div>
              <div>
                <p class="card-title mb-1">Jumlah Cuti Approved</p>
                <p class="card-value mb-0"> {{ $jumlahApproved ?? 0 }}</p>
              </div>
            </div>
            <button 
                      wire:click="showDetail" 
                      class="btn btn-sm btn-info mt-4">
                      <i class="fas fa-eye"></i> Detail
            </button>
            <p class="card-note mt-3 mb-0">Filter berdasarkan Bulan dan Tahun</p>
          </div>
        </div>
  
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-custom p-4 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="card-icon icon-red">
                <i class="fas fa-times-circle"></i>
              </div>
              <div>
                <p class="card-title mb-1">Jumlah Cuti Rejected</p>
                <p id="cutiRejected" class="card-value mb-0"> {{ $jumlahRejected ?? 0 }}</p>
              </div>
            </div>
            <button 
                      wire:click="showDetailRejected" 
                      class="btn btn-sm btn-info mt-4">
                      <i class="fas fa-eye"></i> Detail
            </button>
            <p class="card-note mt-3 mb-0">Filter berdasarkan Bulan dan Tahun</p>
          </div>
        </div>
      </div>
    </section>

    <section class="table-section mb-3">
      <h2>Data Cuti</h2>
      <div class="table-responsive">
        <div class="row">
          <div class="col-10 mb-3">
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                class="form-control" 
                placeholder="Cari nama...">
          </div>
          <div class="col-2 mb-3">
            <h5>Rekapan : </h5>
            <button 
                        wire:click="exportExcel()" 
                        class="btn btn-sm btn-success ms-2"><i class="fas fa-file-excel"></i> Excel
                      </button>
          </div>
        </div>
        
        <div class="mt-3">
          {{ $dataCuti->links() }}
        </div>
          <table class="table table-striped">
              <thead>
                  <tr>
                    <th>No</th>
                      <th wire:click="sortBy('nama_lengkap')" style="cursor: pointer;">
                          Karyawan 
                          @if($sortField === 'nama_lengkap')
                              <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                          @endif
                      </th>
                      <th wire:click="sortBy('tanggal_mulai')" style="cursor: pointer;">
                          Tanggal Mulai
                          @if($sortField === 'tanggal_mulai')
                              <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                          @endif
                      </th>
                      <th>Unit</th>
                      <th>Status</th>
                      {{-- <th>Download Excel</th> --}}
                      <th>Lihat Detail</th>
                  </tr>
              </thead>
              <tbody>
                @foreach($dataCuti as $cuti =>$c) 
                
                  <tr>
                    
                    <th scope="row">{{ $dataCuti->firstItem() + $cuti }}</th>

                    @if ($c->karyawan)
                      <td>{{ $c->karyawan->nama_lengkap ?? 0}}</td>
                      <td>{{ $c->tanggal_mulai ?? 0 }}</td>
                      <td>{{ $c->karyawan->unit ?? 0}}</td>
                      <td>
                        @if ($c->status_admin === 'approved:Admin')
                        <span class="badge bg-success">
                            {{ $c->status_admin }}
                        </span>

                      @elseif ($c->status_admin === 'approved:Direktur')
                        <span class="badge bg-success">
                            {{ $c->status_admin }}
                        </span>
                        
                      @elseif ($c->status_admin === 'pending')
                        <span class="badge bg-warning">
                            {{ $c->status_admin }}
                        </span>

                      @else
                        <span class="badge bg-danger">
                          {{ $c->status_admin }}
                        </span>
                      @endif
                      </td>
                    @elseif ($c->manager)
                    <td>{{ $c->manager->nama_lengkap ?? 0}}</td>
                      <td>{{ $c->tanggal_mulai ?? 0 }}</td>
                      <td>{{ $c->manager->unit ?? 0}}</td>
                      <td>
                        @if ($c->status_admin === 'approved:Admin')
                          <span class="badge bg-success">
                              {{ $c->status_admin }}
                          </span>

                        @elseif ($c->status_admin === 'approved:Direktur')
                          <span class="badge bg-success">
                              {{ $c->status_admin }}
                          </span>

                        @elseif ($c->status_admin === 'pending')
                          <span class="badge bg-warning">
                              {{ $c->status_admin }}
                          </span>

                        @else
                          <span class="badge bg-danger">
                            {{ $c->status_admin }}
                          </span>
                        @endif
                      </td>
                    @elseif ($c->admin)
                    <td>{{ $c->admin->nama_lengkap ?? 0}}</td>
                      <td>{{ $c->tanggal_mulai ?? 0 }}</td>
                      <td>{{ $c->admin->unit ?? 0}}</td>
                      <td>
                        @if ($c->status_admin === 'approved:Admin')
                        <span class="badge bg-success">
                            {{ $c->status_admin }}
                        </span>

                      @elseif ($c->status_admin === 'approved:Direktur')
                        <span class="badge bg-success">
                            {{ $c->status_admin }}
                        </span>
                        
                      @elseif ($c->status_admin === 'pending')
                        <span class="badge bg-warning">
                            {{ $c->status_admin }}
                        </span>

                      @else
                        <span class="badge bg-danger">
                          {{ $c->status_admin }}
                        </span>
                      @endif
                      </td>
                    @endif
                    {{-- <td>
                      <button 
                        wire:click="exportExcel({{ $c->id }})" 
                        class="btn btn-sm btn-success ms-2"><i class="fas fa-file-excel"></i> Excel
                      </button>
                    </td> --}}
                    <td>
                      @if ($c->surat_keterangan)
                        <a href="/admin/laporan/download_suket/{{ $c->id }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i>Suket</a>
                      @endif
                      <button wire:click="downloadPdf({{ $c->id }})" class="btn btn-sm btn-primary"><i class="fas fa-download"></i> PDF </button>
                    </td>
                  </tr>
                  @endforeach
              </tbody>
          </table>
      </div>
  
      <!-- Pagination -->
      <div class="mt-3">
          {{ $dataCuti->links() }}
      </div>
  </section>

    <section class="chart-section">
      <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Grafik Tren Pengajuan Cuti {{ $chartYear }}</h5>
                    <select wire:model="chartYear" wire:change="changeYear" class="form-select form-select-sm" style="width: 100px;">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div wire:ignore>
                    <canvas id="cutiTrendChart" height="250" style="width: 100%"></canvas>
                </div>
                
                
                <script>
                console.log('Initial Jumlah Cuti per Bulan:', @json($chartData['values']));
                console.log('Initial Jumlah Cuti Rejected per Bulan:', @json($chartDataRejected['values']));
                console.log('Initial Jumlah Cuti Approved per Bulan:', @json($chartDataApproved['values']));
                window.cutiChart = null;

                // Fungsi inisialisasi chart
                function initChart() {
                    const ctx = document.getElementById('cutiTrendChart');
                    if (!ctx) return;
                    
                    if (window.cutiChart) {
                        window.cutiChart.destroy();
                    }
                    
                    window.cutiChart = new Chart(ctx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: @json($chartData['labels']),
                            datasets: [{
                                label: 'Cuti Diajukan',
                                data: @json($chartData['values']),
                                backgroundColor: 'rgba(58, 113, 213, 0.2)',
                                borderColor: 'rgba(58, 113, 213, 1)',
                                borderWidth: 2
                            },
                            {
                                label: 'Cuti Rejected',
                                data: @json($chartDataRejected['values']),
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 2,
                                tension: 0.1
                            },
                            {
                                label: 'Cuti Approved',
                                data: @json($chartDataApproved['values']),
                                backgroundColor: 'rgba(1, 255, 1, 0.2)',
                                borderColor: 'rgba(1, 200, 1, 1)',
                                borderWidth: 2,
                                tension: 0.1
                            },
                          ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                               legend: { 
                                display: true } 
                              }
                        }
                    });
                }

                // Pertama kali load
                document.addEventListener('livewire:init', () => {
                    // Tunggu hingga DOM benar-benar siap
                    setTimeout(() => {
                        initChart();
                    }, 100);
                });

                // Saat data di-update
                Livewire.hook('commit', ({ component, succeed }) => {
                    succeed(() => {
                            if (component.id !== @this.__instance.id) return;

                            const livewireData = @this.chartData.values;
                            console.log('Data dari Livewire:', livewireData);
                            const livewireDataRejected = @this.chartDataRejected.values;
                            console.log('Data dari Livewire Rejected:', livewireDataRejected);
                            const livewireDataApproved = @this.chartDataApproved.values;
                            console.log('Data dari Livewire Approved:', livewireDataApproved);
                            
                            if (window.cutiChart && livewireData && livewireDataRejected && livewireDataApproved) {
                              window.cutiChart.options.animation = false;
                              window.cutiChart.data.datasets[0].data = livewireData;
                              window.cutiChart.data.datasets[1].data = livewireDataRejected;
                              window.cutiChart.data.datasets[2].data = livewireDataApproved;

                              window.cutiChart.update('none');
                              console.log('Hook terpicu: PENGAJUAN:', (livewireData));
                              console.log('Hook terpicu: REJECTED', (livewireDataRejected));
                              console.log('Hook terpicu: APPROVED', (livewireDataApproved));
                        }
                    });
                });
                </script>
              <script>
                document.addEventListener('DOMContentLoaded', () => {
                    if (!window.Echo) {
                        console.error('Echo not initialized!');
                        return;
                    }
                    console.log('Echo event CutiDiajukan initialized');

                    window.Echo.channel('admin.proses_cuti')
                        .listen('CutiDiajukan', (data) => {
                            toastr.success(`Pengajuan dari: ${data.nama_lengkap}`);
                            console.log('Pengajuan dari :', data);
                        })
                        .error((err) => {
                            console.error('Channel error:', err);
                        });
                });
              </script>
              <script>
                document.addEventListener('DOMContentLoaded', () => {
                    if (!window.Echo){
                        console.error('Echo not initialized');
                        return;
                    }
                    console.log('Echo event CutiDisetujui initialized');
                    const adminId = {{ auth()->user()->admin->id ?? 'null' }};
                    const userId = {{ auth()->id() ?? 'null' }};
                    if (!userId) {
                        console.error('Error: User not authenticated');
                    return;
                    }
                    if (!adminId) {
                        console.error('Error: User not authenticated');
                    return;
                    }

                    window.Echo.channel(`admin.proses_cuti.${adminId}`)
                        .listen('CutiDisetujui', (data) => {
                            console.log('Cuti disetujui:', data);
                            toastr.success(`Cuti Anda disetujui! Mulai: ${data.tanggal_mulai}`);
                        })
                        .error((err) => {
                            console.error('Channel error : ', err);
                        });
                });
              </script>
            </div>
        </div>
    </div>
  
 
    </section>

    @include('livewire.detail-cuti-modal')
    @include('livewire.detail-cuti-modal-rejected')
    @include('livewire.detail-cuti-modal-sedangcuti')
    @include('livewire.detail-cuti-modal-belum-cuti')
    {{-- @include('view-suket') --}}
  </main>
</div>
  
  
