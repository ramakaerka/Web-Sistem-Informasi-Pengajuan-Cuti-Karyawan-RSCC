@extends('assets.mainDashboard')
@section('content')
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
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto gap-3">
          <li class="nav-item">
            <a class="nav-link" href="#">Beranda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Data Cuti</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Karyawan</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Laporan</a>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <main class="container my-5 flex-grow-1">
    <section class="filter-section">
      <h2>Filter Data</h2>
      <form id="filterForm" class="row g-3">
        <div class="col-12 col-sm-4">
          <label for="filterMonth" class="form-label">Bulan</label>
          <select id="filterMonth" wire:model="bulan" name="month" class="form-select">
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
        <div class="col-12 col-sm-4">
          <label for="filterYear" class="form-label">Tahun</label>
          <select id="filterYear" wire:model="tahun" name="year" class="form-select">
            <option value="Semua Tahun">Semua Tahun</option>
            <option value="2024">2024</option>
            <option value="2023">2023</option>
            <option value="2022">2022</option>
            <option value="2021">2021</option>
            <option value="2020">2020</option>
          </select>
        </div>
        <div class="col-12 col-sm-4">
          <label for="filterUnit" class="form-label">Unit</label>
          <select id="filterUnit" wire:model="unit" name="unit" class="form-select">
            <option value="Semua Unit">Semua Unit</option>
            <option value="SDM">HR</option>
            <option value="Teknologi Informasi">IT</option>
          </select>
        </div>
      </form>
    </section>

    <section class="row g-4 mb-5">
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
            <div class="card-icon icon-green">
              <i class="fas fa-user-check"></i>
            </div>
            <div>
              <p class="card-title mb-1">Karyawan Sedang Cuti Hari Ini</p>
              <p id="karyawanCutiHariIni" class="card-value mb-0">0</p>
            </div>
          </div>
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
              <p id="cutiApproved" class="card-value mb-0">0</p>
            </div>
          </div>
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
              <p id="cutiRejected" class="card-value mb-0">0</p>
            </div>
          </div>
          <p class="card-note mt-3 mb-0">Filter berdasarkan Bulan dan Tahun</p>
        </div>
      </div>
    </section>

    <section class="chart-section">
      <h2>Grafik Tren Banyak Cuti Dalam Periode Bulan</h2>
      <canvas id="cutiChart" style="width: 100%; height: 320px;"></canvas>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  </script>
@endsection