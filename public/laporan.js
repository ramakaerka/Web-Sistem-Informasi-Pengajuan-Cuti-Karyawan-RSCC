const dummyData = [
    { id: 1, unit: 'HR', startDate: '2024-06-01', endDate: '2024-06-05', status: 'approved' },
    { id: 2, unit: 'Finance', startDate: '2024-06-03', endDate: '2024-06-04', status: 'rejected' },
    { id: 3, unit: 'IT', startDate: '2024-06-10', endDate: '2024-06-12', status: 'approved' },
    { id: 4, unit: 'Marketing', startDate: '2024-05-20', endDate: '2024-05-22', status: 'approved' },
    { id: 5, unit: 'Sales', startDate: '2024-06-15', endDate: '2024-06-18', status: 'approved' },
    { id: 6, unit: 'HR', startDate: '2024-06-20', endDate: '2024-06-22', status: 'rejected' },
    { id: 7, unit: 'Finance', startDate: '2024-06-25', endDate: '2024-06-27', status: 'approved' },
    { id: 8, unit: 'IT', startDate: '2024-06-28', endDate: '2024-06-30', status: 'approved' },
    { id: 9, unit: 'Marketing', startDate: '2024-07-01', endDate: '2024-07-03', status: 'approved' },
    { id: 10, unit: 'Sales', startDate: '2024-07-05', endDate: '2024-07-07', status: 'rejected' },
    { id: 11, unit: 'HR', startDate: '2024-07-10', endDate: '2024-07-12', status: 'approved' },
    { id: 12, unit: 'Finance', startDate: '2024-07-15', endDate: '2024-07-17', status: 'approved' },
    { id: 13, unit: 'IT', startDate: '2024-07-20', endDate: '2024-07-22', status: 'approved' },
    { id: 14, unit: 'Marketing', startDate: '2024-07-25', endDate: '2024-07-27', status: 'rejected' },
    { id: 15, unit: 'Sales', startDate: '2024-07-28', endDate: '2024-07-30', status: 'approved' }
  ];

  // Helper function to parse date strings
  function parseDate(dateStr) {
    const parts = dateStr.split('-');
    return new Date(parts[0], parts[1] - 1, parts[2]);
  }

  // Check if a date is between two dates (inclusive)
  function isDateInRange(date, start, end) {
    return date >= start && date <= end;
  }

  // Get today's date without time
  function getToday() {
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), now.getDate());
  }

  // Filter and update dashboard data
  function updateDashboard() {
    const month = parseInt(document.getElementById('filterMonth').value);
    const year = parseInt(document.getElementById('filterYear').value);
    const unit = document.getElementById('filterUnit').value;

    // Filtered data for jumlah cuti (filter by month and unit)
    let filteredCuti = dummyData.filter((item) => {
      const start = parseDate(item.startDate);
      const end = parseDate(item.endDate);
      let matchMonth = true;
      let matchUnit = true;

      if (!isNaN(month)) {
        // Check if start or end date is in the selected month
        matchMonth =
          (start.getMonth() + 1 === month) || (end.getMonth() + 1 === month);
      }
      if (unit) {
        matchUnit = item.unit === unit;
      }
      return matchMonth && matchUnit;
    });

    // Jumlah cuti
    document.getElementById('jumlahCuti').textContent = filteredCuti.length;

    // Total karyawan yang sedang cuti hari ini (filter by unit)
    const today = getToday();
    let cutiHariIni = dummyData.filter((item) => {
      const start = parseDate(item.startDate);
      const end = parseDate(item.endDate);
      const inRange = isDateInRange(today, start, end);
      const matchUnit = unit ? item.unit === unit : true;
      return inRange && matchUnit;
    });
    document.getElementById('karyawanCutiHariIni').textContent = cutiHariIni.length;

    // Jumlah cuti yang di approved (filter by month and year)
    let approvedCuti = dummyData.filter((item) => {
      if (item.status !== 'approved') return false;
      const start = parseDate(item.startDate);
      let matchMonth = true;
      let matchYear = true;
      if (!isNaN(month)) {
        matchMonth = (start.getMonth() + 1) === month;
      }
      if (!isNaN(year)) {
        matchYear = start.getFullYear() === year;
      }
      return matchMonth && matchYear;
    });
    document.getElementById('cutiApproved').textContent = approvedCuti.length;

    // Jumlah cuti yang di reject (filter by month and year)
    let rejectedCuti = dummyData.filter((item) => {
      if (item.status !== 'rejected') return false;
      const start = parseDate(item.startDate);
      let matchMonth = true;
      let matchYear = true;
      if (!isNaN(month)) {
        matchMonth = (start.getMonth() + 1) === month;
      }
      if (!isNaN(year)) {
        matchYear = start.getFullYear() === year;
      }
      return matchMonth && matchYear;
    });
    document.getElementById('cutiRejected').textContent = rejectedCuti.length;

    updateChart(month, year);
  }

  // Setup Chart.js chart
  const ctx = document.getElementById('cutiChart').getContext('2d');
  let cutiChart = null;

  function updateChart(filterMonth, filterYear) {
    // Aggregate cuti count per month in the selected year or all years
    const counts = new Array(12).fill(0);

    dummyData.forEach((item) => {
      const start = parseDate(item.startDate);
      const year = start.getFullYear();
      const month = start.getMonth();

      if (!isNaN(filterYear) && year !== filterYear) return;
      if (!isNaN(filterMonth) && (month + 1) !== filterMonth) return;

      counts[month]++;
    });

    const labels = [
      'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];

    if (cutiChart) {
      cutiChart.data.datasets[0].data = counts;
      cutiChart.options.plugins.title.text = filterYear ? `Tren Cuti Tahun ${filterYear}` : 'Tren Cuti Semua Tahun';
      cutiChart.update();
    } else {
      cutiChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Jumlah Cuti',
            data: counts,
            borderColor: '#4F46E5',
            backgroundColor: 'rgba(79, 70, 229, 0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 4,
            pointHoverRadius: 6,
            borderWidth: 3,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              labels: {
                color: '#374151',
                font: { weight: '600' }
              }
            },
            title: {
              display: true,
              text: filterYear ? `Tren Cuti Tahun ${filterYear}` : 'Tren Cuti Semua Tahun',
              color: '#1F2937',
              font: { size: 18, weight: '700' }
            },
            tooltip: {
              mode: 'index',
              intersect: false,
            }
          },
          scales: {
            x: {
              ticks: { color: '#6B7280', font: { weight: '600' } },
              grid: { display: false }
            },
            y: {
              beginAtZero: true,
              ticks: { color: '#6B7280', stepSize: 1 },
              grid: { color: '#E5E7EB' }
            }
          }
        }
      });
    }
  }

  // Event listeners for filters
  document.getElementById('filterMonth').addEventListener('change', updateDashboard);
  document.getElementById('filterYear').addEventListener('change', updateDashboard);
  document.getElementById('filterUnit').addEventListener('change', updateDashboard);

  // Initial load
  updateDashboard();