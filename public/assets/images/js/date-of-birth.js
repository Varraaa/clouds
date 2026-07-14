function populateDateOptions() {
    const daySelects = document.querySelectorAll('.day-select');
    const monthSelects = document.querySelectorAll('.month-select');
    const yearSelects = document.querySelectorAll('.year-select');

    // Days 01-31 (value di-zero-pad biar hasil akhir format tanggalnya konsisten YYYY-MM-DD)
    daySelects.forEach(select => {
        for (let i = 1; i <= 31; i++) {
            const value = String(i).padStart(2, '0');
            select.innerHTML += `<option value="${value}">${value}</option>`;
        }
    });

    // Months 01-12
    const months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
    monthSelects.forEach(select => {
        months.forEach(month => {
            select.innerHTML += `<option value="${month}">${month}</option>`;
        });
    });

    // Years: current year mundur 100 tahun
    const currentYear = new Date().getFullYear();
    yearSelects.forEach(select => {
        for (let i = currentYear; i >= currentYear - 100; i--) {
            select.innerHTML += `<option value="${i}">${i}</option>`;
        }
    });
}

populateDateOptions();

/**
 * JARING PENGAMAN: paksa sinkronkan semua hidden date_of_birth
 * tepat sebelum form di-submit, gak cuma andalkan event onchange.
 * Ini nutup celah kalau browser restore value dropdown (misal via
 * tombol Back) tanpa memicu event change.
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-passenger-details');
    if (!form) return;

    form.addEventListener('submit', function () {
        document.querySelectorAll('.day-select').forEach(select => {
            const index = select.dataset.index;
            if (typeof updateDateOfBirth === 'function') {
                updateDateOfBirth(index);
            }
        });
    });
});