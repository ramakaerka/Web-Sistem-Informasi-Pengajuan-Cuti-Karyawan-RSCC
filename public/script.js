
function observeDateChanges() {
  const dateInput = document.getElementById('selected_dates_array');
  
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(() => {
      checkLampiranRequirement(); 
    });
  });

  observer.observe(dateInput, { 
    attributes: true, 
    attributeFilter: ['value'] 
  });
}

function checkLampiranRequirement() {
  const jenisCuti = document.getElementById('jenis_cuti').value;
  const lampiranDiv = document.getElementById('lampiran');
  const dateInput = document.getElementById('selected_dates_array');
  
  try {
    const dates = JSON.parse(dateInput.value || '[]');
    const shouldShow = (jenisCuti === 'cuti_sakit' || jenisCuti === 'cuti_lainnya') && dates.length > 3;
    lampiranDiv.style.display = shouldShow ? 'block' : 'none';
  } catch (e) {
    console.error("Error parsing dates:", e);
    lampiranDiv.style.display = 'none';
  }
}


document.addEventListener('DOMContentLoaded', function() {
  observeDateChanges();
  document.getElementById('jenis_cuti').addEventListener('change', checkLampiranRequirement);
  
  checkLampiranRequirement();
});