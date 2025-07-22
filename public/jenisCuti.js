document.addEventListener("DOMContentLoaded", function () {
    const tanggalSelesai = document.getElementById("tanggal_selesai");
    const tanggalMulai = document.getElementById("tanggal_mulai");
    const jenisCuti = document.getElementById("jenis_cuti");
    let selectedDates = [];
    const tanggalSelesaiWrapper = document.querySelector('.tanggal-selesai-wrapper');

    const tanggalLiburFormatted = (typeof tanggalLibur !== 'undefined') ? tanggalLibur : [];



    function formatDateLocal(date) {
        const offset = date.getTimezoneOffset();
        const localDate = new Date(date.getTime() - (offset * 60 * 1000));
        return localDate.toISOString().split('T')[0];
    }
    
    let tambahanHariMulai = 0; // Default H+1
    let tambahanHariSelesai = 0;

    function initDatepicker() {
        let today = new Date();
        tambahanHariMulai=0;

        
        if (jenisCuti.value === "cuti_tahunan") {
            tambahanHariMulai = 7; 
        }
        else if (jenisCuti.value === "cuti_panjang") {
            tambahanHariMulai = 7; 
        }
        else if (jenisCuti.value === "cuti_sakit") {
            tambahanHariMulai = 0; 
        }
        else if (jenisCuti.value === "cuti_melahirkan") {
            tambahanHariMulai = 0; 
        }
        else if (jenisCuti.value === "cuti_menikah") {
            tambahanHariMulai = 7; 
        }
        else if (jenisCuti.value === "cuti_kelahiran_anak") {
            tambahanHariMulai = 1; 
        }
        else if (jenisCuti.value === "cuti_pernikahan_anak") {
            tambahanHariMulai = 3; 
        }
        else if (jenisCuti.value === "cuti_mati_sedarah") {
            tambahanHariMulai = 0; 
        }
        else if (jenisCuti.value === "cuti_mati_klg_serumah") {
            tambahanHariMulai = 0; 
        }
        else if (jenisCuti.value === "cuti_mati_ortu") {
            tambahanHariMulai = 0; 
        }
        else if (jenisCuti.value === "cuti_lainnya") {
            tambahanHariMulai = 1; 
        }

        let minStartDate = new Date();
        minStartDate.setDate(today.getDate() + tambahanHariMulai);
        minStartDate.setHours(0, 0, 0, 0);

    // while (tanggalLiburFormatted.includes(formatDateLocal(minStartDate))) {
    //     minStartDate.setDate(minStartDate.getDate() + 1);
    // }

    tambahanHariSelesai=0;

    // Tentukan tambahan hari selesai berdasarkan jenis cuti
    if (jenisCuti.value === "cuti_tahunan") {
        tambahanHariSelesai = 12;
    }
    else if (jenisCuti.value === "cuti_panjang") {
        tambahanHariSelesai = 6;
    }
    else if (jenisCuti.value === "cuti_sakit") {
        tambahanHariSelesai = 12;
    }
    else if (jenisCuti.value === "cuti_melahirkan") { 
        tambahanHariSelesai = 90;
    }
    else if (jenisCuti.value === "cuti_menikah") {
        tambahanHariSelesai = 3;
    }
    else if (jenisCuti.value === "cuti_kelahiran_anak") {
        tambahanHariSelesai = 2;
    }
    else if (jenisCuti.value === "cuti_pernikahan_anak") {
        tambahanHariSelesai = 2;
    }
    else if (jenisCuti.value === "cuti_mati_sedarah") {
        tambahanHariSelesai = 2;
    }
    else if (jenisCuti.value === "cuti_mati_klg_serumah") {
        tambahanHariSelesai = 1;
    }
    else if (jenisCuti.value === "cuti_mati_ortu") {
        tambahanHariSelesai = 2;
    }
    else if (jenisCuti.value === "cuti_lainnya") {
        tambahanHariSelesai = 3;
    }

    let maxEndDate = new Date(minStartDate);
    maxEndDate.setDate(minStartDate.getDate() + tambahanHariSelesai);
    

    let minDateFormatted = formatDateLocal(minStartDate);
    console.log(minDateFormatted);
    console.log(minStartDate);
   
    let maxDateFormatted = formatDateLocal(maxEndDate);
    tanggalMulai.value = minDateFormatted;
    console.log(tanggalMulai.value);
    tanggalSelesai.value = maxDateFormatted;

    $("#tanggal_mulai").datepicker("destroy").datepicker({
        dateFormat: "yy-mm-dd",
        beforeShowDay: function (date) {

            let batasMaksimalTanggalMulai = new Date();
            batasMaksimalTanggalMulai.setMonth(batasMaksimalTanggalMulai.getMonth() + 6);

            if (date < minStartDate || date > batasMaksimalTanggalMulai) {
                return [false];
            }
            else {

                return [true];
            }
        },
        onSelect: function (selectedDateStr) {
            tanggalMulai.value = selectedDateStr;
        
            let selectedDate = new Date(selectedDateStr);
            let currentDate = new Date(selectedDate);
            // let count = 0;
        
            currentDate.setDate(currentDate.getDate() + tambahanHariSelesai);
        
            tanggalSelesai.value = formatDateLocal(currentDate);
        }
    });
    }

    function toggleTanggalSelesai() {
        if (jenisCuti.value === "cuti_tahunan") {
            tanggalSelesaiWrapper.style.display = 'none';
            tanggalSelesai.value = ''; // Kosongkan nilai tanggal selesai
        } 
        else if (jenisCuti.value === "cuti_sakit") {
            tanggalSelesaiWrapper.style.display = 'none';
            tanggalSelesai.value = ''; // Kosongkan nilai tanggal selesai
        } 
        else if (jenisCuti.value === "cuti_lainnya") {
            tanggalSelesaiWrapper.style.display = 'none';
            tanggalSelesai.value = ''; // Kosongkan nilai tanggal selesai
        } 
        else {
            tanggalSelesaiWrapper.style.display = 'block';
        }
    }

    function handleAnnualLeaveSelection() {
        if (jenisCuti.value !== "cuti_tahunan") {
            return;
        }

        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 7);

        // Buat datepicker khusus untuk cuti tahunan
        $("#tanggal_mulai").datepicker("destroy").datepicker({
            dateFormat: "yy-mm-dd",
            minDate: minDate,
            beforeShowDay: function(date) {
                const dateStr = formatDateLocal(date);
                const isSelected = selectedDates.includes(dateStr);
                const isDisabled = tanggalLiburFormatted.includes(dateStr);
                
                // Batasi pemilihan hanya 6 bulan ke depan
                const maxDate = new Date();
                maxDate.setMonth(maxDate.getMonth() + 6);
                
                return [
                    date >= new Date() && date <= maxDate && !isDisabled,
                    isSelected ? 'selected-date-highlight' : ''
                ];
            },
            onSelect: function(dateText) {
                const dateStr = dateText;
                
                // Toggle selection
                if (selectedDates.includes(dateStr)) {
                    selectedDates = selectedDates.filter(d => d !== dateStr);
                } else {
                    // Batasi maksimal 12 hari
                    if (selectedDates.length >= 12) {
                        alert("Anda hanya bisa memilih maksimal 12 hari cuti tahunan");
                        return;
                    }
                    selectedDates.push(dateStr);
                }
                
                // Update tampilan
                updateSelectedDatesDisplay();
            }
        });
    }
    function handleCutiSakit() {
        if (jenisCuti.value !== "cuti_sakit") {
            return;
        }

        const minDate = new Date;
        minDate.setDate(minDate.getDate() + 0);
        minDate.setHours(0,0,0,0);
        console.log('mindate cuti sakit',minDate);
        
        // Buat datepicker khusus untuk cuti tahunan
        $("#tanggal_mulai").datepicker("destroy").datepicker({
            dateFormat: "yy-mm-dd",
            minDate: minDate,
            beforeShowDay: function(date) {
                const dateStr = formatDateLocal(date);
                const isSelected = selectedDates.includes(dateStr);
                const isDisabled = tanggalLiburFormatted.includes(dateStr);
                
                // Batasi pemilihan hanya 6 bulan ke depan
                const maxDate = new Date();
                maxDate.setMonth(maxDate.getMonth() + 1);
                
                return [
                    date >= minDate && date <= maxDate && !isDisabled,
                    isSelected ? 'selected-date-highlight' : ''
                ];
            },
            onSelect: function(dateText) {
                const dateStr = dateText;
                
                // Toggle selection
                if (selectedDates.includes(dateStr)) {
                    selectedDates = selectedDates.filter(d => d !== dateStr);
                } else {
                    // Batasi maksimal 12 hari
                    if (selectedDates.length >= 12) {
                        alert("Anda hanya bisa memilih maksimal 12 hari cuti sakit");
                        return;
                    }
                    selectedDates.push(dateStr);
                }
                
                // Update tampilan
                updateSelectedDatesDisplay();
            }
        });
    }

    function updateSelectedDatesDisplay() {
        const container = document.getElementById('selected-dates-container') || 
        createSelectedDatesContainer();

        if (selectedDates.length > 0) {
            selectedDates.sort((a, b) => new Date(a) - new Date(b));
            tanggalMulai.value = selectedDates[0];
            // tanggalSelesai.value = selectedDates[selectedDates.length - 1];
            
            // Buat tampilan tanggal yang dipilih
            const container = document.getElementById('selected-dates-container') || 
                createSelectedDatesContainer();
            container.innerHTML = `
            <div class="selected-dates-label"><strong>Tanggal Dipilih:</strong></div>
            <div class="selected-dates-list">
            ${selectedDates.map(date => `
                <span class="selected-date">${date}
                    <span class="remove-date" data-date="${date}">×</span>
                </span>
                `).join('')}
            </div>`;

            document.getElementById('selected_dates_array').value = JSON.stringify(selectedDates);

            document.querySelectorAll('.remove-date').forEach(button => {
                    button.addEventListener('click', function() {
                    const dateToRemove = this.getAttribute('data-date');
                    selectedDates = selectedDates.filter(d => d !== dateToRemove);
                    updateSelectedDatesDisplay();
                });
            });
        }
        else {
            container.innerHTML = '';
            tanggalMulai.value ='';
            document.getElementById('selected_dates_array').value = '';
        
            if (jenisCuti.value === "cuti_tahunan") {
                handleAnnualLeaveSelection();
            }
            else if (jenisCuti.value === "cuti_sakit") {
                handleCutiSakit();
            }
        }
    }

    function createSelectedDatesContainer() {
        const container = document.createElement('div');
        container.id = 'selected-dates-container';
        container.style.marginTop = '10px';
        tanggalMulai.parentNode.appendChild(container);
        return container;
    }

    initDatepicker();
    toggleTanggalSelesai();
    

    // Ubah batas tanggal saat jenis cuti berubah
    jenisCuti.addEventListener("change", function() {
        // Reset selected dates ketika jenis cuti diubah
        selectedDates = [];
        if (document.getElementById('selected-dates-container')) {
            document.getElementById('selected-dates-container').innerHTML = '';
        }
        
        // Panggil fungsi asli
        toggleTanggalSelesai();

        initDatepicker();
        
        // Jika cuti tahunan, aktifkan mode khusus
        if (jenisCuti.value === "cuti_tahunan") {
            handleAnnualLeaveSelection();
        }
        else if (jenisCuti.value === "cuti_sakit") {
            handleCutiSakit();
        }
    });

    // Jika awal load adalah cuti tahunan
    if (jenisCuti.value === "cuti_tahunan") {
        handleAnnualLeaveSelection();
    }
    else if (jenisCuti.value === "cuti_sakit") {
        handleCutiSakit();
    }

    tanggalMulai.addEventListener("change", function () {
        let selectedDate = new Date(tanggalMulai.value);
        let currentDate = new Date(selectedDate);
        let count = 0;
    
        currentDate.setDate(currentDate.getDate() + tambahanHariSelesai);
    
        tanggalSelesai.value = formatDateLocal(currentDate);

    });


});