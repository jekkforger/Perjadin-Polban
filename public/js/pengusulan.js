// Initial setup for form steps

// TAMBAHKAN FUNGSI HELPER INI DI ATAS
/**
 * Mengubah string tanggal "DD/MM/YYYY" menjadi format "Hari, Tanggal Bulan Tahun"
 * @param {string} dateStr - String tanggal dalam format DD/MM/YYYY.
 * @returns {string} Tanggal yang sudah diformat.
 */
function formatFullDate(dateStr) {
    if (!dateStr || dateStr.trim() === '') return '-';

    const parts = dateStr.trim().split('/');
    if (parts.length !== 3) return dateStr; // Kembalikan string asli jika format salah

    // new Date(year, monthIndex, day)
    const dateObj = new Date(parts[2], parts[1] - 1, parts[0]);

    // Opsi untuk format Bahasa Indonesia
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

    return dateObj.toLocaleDateString('id-ID', options);
}
// AKHIR FUNGSI HELPER

let currentStep = 1;
const formSteps = document.querySelectorAll('.form-step');

function showStep(stepNumber) {
    // Sembunyikan semua langkah form
    formSteps.forEach(step => {
        step.classList.remove('form-step-active');
    });
    // Tampilkan langkah yang aktif
    formSteps[stepNumber - 1].classList.add('form-step-active');
    
    // ===================================================================
    // <-- AWAL BLOK KONTROL TOMBOL BARU -->
    // ===================================================================
    // Sembunyikan semua tombol navigasi terlebih dahulu
    $('#btn-kembali, #next-to-personel, #save-draft, #create-task, #submit-surat').hide();

    // Tampilkan tombol yang sesuai berdasarkan langkah saat ini
    if (stepNumber === 1) {
        $('#next-to-personel').show();
    } else if (stepNumber === 2) {
        $('#btn-kembali').show();
        $('#save-draft').show();
        $('#create-task').show();
    } else if (stepNumber === 3) {
        $('#btn-kembali').show();
        $('#submit-surat').show();
    }
    // Ensure correct table is shown and DataTables redraws if needed
    if (stepNumber === 2) {
        // Restore correct table visibility
        const selectedDropdownText = $('#data-selection-dropdown').text();
        if (selectedDropdownText.includes('Pegawai')) {
            $('#data-pegawai-table').show();
            $('#data-mahasiswa-table').hide();
        } else if (selectedDropdownText.includes('Mahasiswa')) {
            $('#data-mahasiswa-table').show();
            $('#data-pegawai-table').hide();
        } else {
            $('#data-pegawai-table').show();
            $('#data-mahasiswa-table').hide();
            $('#data-selection-dropdown').text('Data Pegawai');
        }
        if ($.fn.DataTable.isDataTable('#pegawaiTable')) $('#pegawaiTable').DataTable().columns.adjust().draw();
        if ($.fn.DataTable.isDataTable('#mahasiswaTable')) $('#mahasiswaTable').DataTable().columns.adjust().draw();
    }
}

let usedNomorSuratList = [];

// Fungsi untuk mendapatkan daftar nomor surat terpakai dari backend
function getUsedNomorSuratList() {
    fetch('/pengusul/used-nomor-surat') // **Ini adalah URL AJAX untuk mengambil daftar nomor terpakai**
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal memuat daftar nomor surat terpakai.');
            }
            return response.json();
        })
        .then(data => {
            usedNomorSuratList = data.used_numbers; // Simpan data nomor terpakai
            displayUsedNomorSurat(); // Tampilkan daftar di UI
        })
        .catch(error => {
            console.error('Error memuat daftar nomor surat terpakai:', error);
            $('#nomor-surat-list-ul').html('<li class="text-danger">Gagal memuat daftar.</li>');
        });
}

// Fungsi untuk memeriksa ketersediaan nomor surat (AJAX)
let typingTimerNomorSurat; // Timer untuk debounce input
const doneTypingIntervalNomorSurat = 500; // Waktu tunda setelah mengetik

function checkNomorSuratAvailability() {
    clearTimeout(typingTimerNomorSurat);
    typingTimerNomorSurat = setTimeout(function () {
        const nomorUrutan = $('#nomor_urutan_surat').val();
        const kodePengusul = $('#kode_pengusul_hidden').val();
        const kodePerihal = $('#kode_perihal_hidden').val(); // Asumsi ada hidden input ini
        const tahun = $('#tahun_nomor_surat').val();
        const feedbackElement = $('#nomor-surat-feedback');

        if (!nomorUrutan || !kodePengusul || !kodePerihal || !tahun) {
            feedbackElement.html('').removeClass('text-success text-danger').data('status', '');
            return;
        }

        feedbackElement.html('<span class="text-muted">Memeriksa...</span>').data('status', 'checking');

        fetch(`/pengusul/check-nomor-surat?nomor_urutan_surat=${nomorUrutan}&kode_pengusul=${kodePengusul}&kode_perihal=${kodePerihal}&tahun_nomor_surat=${tahun}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal memeriksa ketersediaan nomor surat.');
                }
                return response.json();
            })
            .then(data => {
                if (data.is_used) {
                    feedbackElement.html('<span class="text-danger">Nomor surat ini sudah terpakai.</span>').data('status', 'used');
                } else {
                    feedbackElement.html('<span class="text-success">Nomor surat ini tersedia.</span>').data('status', 'available');
                }
            })
            .catch(error => {
                console.error('Error memeriksa nomor surat:', error);
                feedbackElement.html('<span class="text-danger">Gagal memeriksa ketersediaan.</span>').data('status', 'error');
            });
    }, doneTypingIntervalNomorSurat);
}

// Fungsi untuk menampilkan daftar nomor surat yang sudah dipakai di UI
function displayUsedNomorSurat() {
    const listUl = $('#nomor-surat-list-ul');
    listUl.empty();
    if (usedNomorSuratList.length > 0) {
        usedNomorSuratList.forEach(nomor => {
            listUl.append(`<li>${nomor}</li>`);
        });
    } else {
        listUl.append('<li>Tidak ada nomor surat terpakai dalam 30 hari terakhir.</li>');
    }
}


// Semua logika sekarang berada di dalam $(document).ready()
$(document).ready(function () {
    showStep(1);

    // ===================================================================
    // BLOK VALIDASI NOMOR SURAT (VERSI FINAL)
    // ===================================================================
    const nomorUrutInput = $('input[name="nomor_urutan_surat"]');
    const tahunSelect = $('select[name="tahun_nomor_surat"]');
    const feedbackDiv = $('#nomor-surat-feedback');
    const nextButton = $('#next-to-personel');
    const nomorTerpakaiList = $('#nomor-surat-list-ul');

    let latestNomorUrut = 0;
    const MAX_NOMOR_GAP = 5;

    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    const fetchLatestNomorUrut = async (tahun) => {
        try {
            const response = await fetch(`/pengusul/get-latest-nomor?tahun=${tahun}`);
            const data = await response.json();
            latestNomorUrut = data.latest_nomor;
            console.log(`Nomor terakhir untuk tahun ${tahun} adalah: ${latestNomorUrut}`);
            await checkNomorAvailability(); // Validasi ulang setelah mendapat nomor terbaru
        } catch (error) {
            console.error('Error fetching latest number:', error);
            latestNomorUrut = 0;
        }
    };

    const checkNomorAvailability = async () => {
        const nomorUrut = nomorUrutInput.val().trim();
        const tahun = tahunSelect.val();
        const kodePengusul = $('input[name="kode_pengusul"]').val();
        const kodePerihal = $('input[name="kode_perihal"]').val();

        if (!nomorUrut) {
            feedbackDiv.html('');
            nextButton.prop('disabled', true).data('status', '');
            return;
        }

        const currentNomor = parseInt(nomorUrut, 10);
        if (isNaN(currentNomor)) {
            feedbackDiv.html('<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> Nomor urut harus berupa angka.</span>').data('status', 'error');
            nextButton.prop('disabled', true);
            return;
        }

        if (currentNomor > latestNomorUrut + MAX_NOMOR_GAP) {
            const maxAllowed = latestNomorUrut + MAX_NOMOR_GAP;
            feedbackDiv.html(`<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> Nomor urut terlalu jauh. Nomor terakhir adalah ${latestNomorUrut}, maksimal nomor berikutnya adalah ${maxAllowed}.</span>`).data('status', 'error');
            nextButton.prop('disabled', true);
            return;
        }
        
        if (currentNomor <= latestNomorUrut) {
             feedbackDiv.html(`<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> Nomor urut harus lebih besar dari nomor terakhir (${latestNomorUrut}).</span>`).data('status', 'error');
            nextButton.prop('disabled', true);
            return;
        }

        feedbackDiv.html('<span class="text-muted">Mengecek...</span>').data('status', 'checking');

        try {
            const response = await fetch(`/pengusul/check-nomor-surat?nomor_urutan_surat=${nomorUrut}&kode_pengusul=${kodePengusul}&kode_perihal=${kodePerihal}&tahun_nomor_surat=${tahun}`);
            const data = await response.json();

            if (data.is_used) {
                feedbackDiv.html('<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> Nomor surat sudah digunakan.</span>').data('status', 'used');
                nextButton.prop('disabled', true);
            } else {
                feedbackDiv.html('<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Nomor surat tersedia.</span>').data('status', 'available');
                nextButton.prop('disabled', false);
            }
        } catch (error) {
            console.error('Error:', error);
            feedbackDiv.html('<span class="text-danger">Gagal memeriksa nomor surat.</span>').data('status', 'error');
            nextButton.prop('disabled', true);
        }
    };

    const loadUsedNumbers = async () => {
        try {
            const response = await fetch('/pengusul/used-nomor-surat');
            const data = await response.json();
            nomorTerpakaiList.empty();
            if (data.used_numbers && data.used_numbers.length > 0) {
                data.used_numbers.forEach(nomor => {
                    nomorTerpakaiList.append(`<li>${nomor}</li>`);
                });
            } else {
                nomorTerpakaiList.append('<li class="text-muted">Tidak ada nomor yang digunakan dalam 30 hari terakhir.</li>');
            }
        } catch (error) {
            console.error('Error loading used numbers:', error);
            nomorTerpakaiList.html('<li class="text-danger">Gagal memuat daftar.</li>');
        }
    };

    const debouncedCheck = debounce(checkNomorAvailability, 500);
    nomorUrutInput.on('input', debouncedCheck);
    
    tahunSelect.on('change', function() {
        fetchLatestNomorUrut($(this).val());
    });

    loadUsedNumbers();
    fetchLatestNomorUrut(tahunSelect.val());
    
    if (!nomorUrutInput.val()) {
        nextButton.prop('disabled', true);
    }


    if (!$.fn.DataTable.isDataTable('#pegawaiTable')) {
        $('#pegawaiTable').DataTable({
            paging: true,
            searching: true,
            info: true,
            pageLength: 5,
            lengthMenu: [5, 10, 15],
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: 0 }]
        });
    } else {
        $('#pegawaiTable').DataTable().columns.adjust().draw();
    }

    if (!$.fn.DataTable.isDataTable('#mahasiswaTable')) {
        $('#mahasiswaTable').DataTable({
            paging: true,
            searching: true,
            info: true,
            pageLength: 10,
            lengthMenu: [5, 10, 15],
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: 0 }]
        });
    } else {
        $('#mahasiswaTable').DataTable().columns.adjust().draw();
    }

    $('#selectedPersonelContainer').hide();

    // PROVINSI
    const provinsiDropdownContainer = $('#provinsi-scroll-container');
    const provinsiSearchInput = $('#search-provinsi-input');
    const provinsiInputField = $('#provinsi');
    const provinsiIdField = $('#provinsi_id');

    // Load Data Provinsi dari API
    function loadProvinces() {
        provinsiDropdownContainer.html('<span class="dropdown-item-text px-2">Memuat data...</span>');

        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal memuat data');
                }
                return response.json();
            })
            .then(provinces => {
                provinsiDropdownContainer.empty();
                provinsiDropdownContainer.data('provinces', provinces);

                // Menambahkan setiap provinsi ke dropdown
                provinces.forEach(provinsi => {
                    const itemHtml = `<a class="dropdown-item" href="#" data-id="${provinsi.id}" data-name="${provinsi.name}">${provinsi.name}</a>`;
                    provinsiDropdownContainer.append(itemHtml);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                provinsiDropdownContainer.html('<span class="dropdown-item-text text-danger px-2">Gagal memuat provinsi.</span>');
            });
    }

    // Trigger load saat dokumen siap
    loadProvinces();

    // Handle item dipilih
    provinsiDropdownContainer.on('click', '.dropdown-item', function (e) {
        e.preventDefault();
        const selectedName = $(this).data('name');
        const selectedId = $(this).data('id');

        provinsiInputField.val(selectedName);
        provinsiIdField.val(selectedId);
        $('#btn-provinsi-dropdown').dropdown('toggle');
    });

    // Handle pencarian
    provinsiSearchInput.on('keyup', function () {
        const searchTerm = $(this).val().toLowerCase();
        const allProvinces = provinsiDropdownContainer.data('provinces') || [];

        const filtered = allProvinces.filter(p => p.name.toLowerCase().includes(searchTerm));
        provinsiDropdownContainer.empty();

        if (filtered.length > 0) {
            filtered.forEach(p => {
                provinsiDropdownContainer.append(`<a class="dropdown-item" href="#" data-id="${p.id}" data-name="${p.name}">${p.name}</a>`);
            });
        } else {
            provinsiDropdownContainer.html('<span class="dropdown-item-text px-2">Tidak ditemukan.</span>');
        }
    });

    // Cegah dropdown tertutup saat klik input
    provinsiSearchInput.on('click', function (e) {
        e.stopPropagation();
    });

    getUsedNomorSuratList();

    // Pantau perubahan pada input nomor urutan dan tahun
    $('#nomor_urutan_surat').on('keyup', checkNomorSuratAvailability);
    $('#tahun_nomor_surat').on('change', checkNomorSuratAvailability);
});

let selectedPersonel = [];

function initializeSelectedPersonel() {
    selectedPersonel = [];
    $('.personel-checkbox:checked').each(function () {
        const checkbox = this;
        const personelData = {
            id: checkbox.dataset.id,
            type: checkbox.dataset.type,
            nama: checkbox.dataset.nama,
            nip: checkbox.dataset.nip,
            pangkat: checkbox.dataset.pangkat,
            golongan: checkbox.dataset.golongan,
            jabatan: checkbox.dataset.jabatan,
            nim: checkbox.dataset.nim,
            jurusan: checkbox.dataset.jurusan,
            prodi: checkbox.dataset.prodi,
        };
        // Only add if not already in the array
        if (!selectedPersonel.some(p => p.id === personelData.id && p.type === personelData.type)) {
            selectedPersonel.push(personelData);
        }
    });
    renderSelectedPersonel();
}

function updateSelectedPersonel(checkbox) {
    const personelId = checkbox.dataset.id;
    const type = checkbox.dataset.type;
    const personelData = {
        id: personelId, type: type, nama: checkbox.dataset.nama,
        nip: checkbox.dataset.nip, pangkat: checkbox.dataset.pangkat,
        golongan: checkbox.dataset.golongan, jabatan: checkbox.dataset.jabatan,
        nim: checkbox.dataset.nim, jurusan: checkbox.dataset.jurusan, prodi: checkbox.dataset.prodi,
    };
    if (checkbox.checked) {
        if (!selectedPersonel.some(p => p.id === personelId && p.type === type)) {
            selectedPersonel.push(personelData);
        }
    } else {
        selectedPersonel = selectedPersonel.filter(p => !(p.id === personelId && p.type === type));
        if (type === 'pegawai') $('#select-all-pegawai').prop('checked', false);
        else if (type === 'mahasiswa') $('#select-all-mahasiswa').prop('checked', false);
    }
    renderSelectedPersonel();
}

function renderSelectedPersonel() {
    const container = $('#selectedPersonelContainer');
    const listElement = $('#selectedPersonelList');
    listElement.html('');
    if (selectedPersonel.length > 0) {
        container.show();
        selectedPersonel.forEach((personel, index) => {
            let identifier = personel.type === 'pegawai' ? (personel.nip || '-') : (personel.type === 'mahasiswa' ? (personel.nim || '-') : '-');
            let roleOrMajor = personel.type === 'pegawai' ? (personel.jabatan || '-') : (personel.type === 'mahasiswa' ? (personel.jurusan || '-') : '-');
            const rowHtml = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${personel.nama}</td>
                    <td>${identifier}</td>
                    <td>${roleOrMajor}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removePersonel('${personel.id}', '${personel.type}')">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </td>
                </tr>`;
            listElement.append(rowHtml);
        });
    } else {
        container.hide();
    }
}

function removePersonel(personelId, type) {
    const checkbox = $(`input.personel-checkbox[data-id="${personelId}"][data-type="${type}"]`);
    if (checkbox.length) {
        checkbox.prop('checked', false);
        updateSelectedPersonel(checkbox[0]);
    }
}

$('#select-all-pegawai').on('click', function () {
    const isChecked = this.checked;
    $('input.personel-checkbox[data-type="pegawai"]').each(function () {
        if ($(this).prop('checked') !== isChecked) {
            $(this).prop('checked', isChecked);
            updateSelectedPersonel(this);
        }
    });
});
$('#select-all-mahasiswa').on('click', function () {
    const isChecked = this.checked;
    $('input.personel-checkbox[data-type="mahasiswa"]').each(function () {
        if ($(this).prop('checked') !== isChecked) {
            $(this).prop('checked', isChecked);
            updateSelectedPersonel(this);
        }
    });
});

// GANTI DENGAN KODE BARU INI
$('#next-to-personel').on('click', function (e) {
    const form = document.getElementById('pengusulanForm');

    // 1. Lakukan validasi dasar HTML5 (untuk field required, dll)
    if (!form.reportValidity()) {
        Swal.fire('Form Belum Lengkap!', 'Mohon lengkapi semua field yang wajib diisi (*).', 'error');
        return; // Hentikan proses jika form dasar tidak valid
    }

    // 2. Ambil status hasil pengecekan nomor surat dari elemen feedback
    const feedbackElement = $('#nomor-surat-feedback');
    const nomorSuratStatus = feedbackElement.data('status');

    // 3. Tambahkan validasi berdasarkan status tersebut
    if (nomorSuratStatus === 'used') {
        // Jika nomor sudah terpakai, tampilkan error dan jangan lanjutkan
        Swal.fire({
            icon: 'error',
            title: 'Nomor Tidak Valid!',
            text: 'Nomor surat usulan yang Anda masukkan sudah pernah digunakan. Mohon gunakan nomor urutan yang lain.'
        });
        return; // Hentikan proses
    } else if (nomorSuratStatus === 'checking') {
        // Jika sistem masih dalam proses mengecek, beri tahu pengguna untuk menunggu
        Swal.fire({
            icon: 'info',
            title: 'Mohon Tunggu',
            text: 'Sistem sedang memeriksa ketersediaan nomor surat. Silakan coba lagi sesaat.',
        });
        return; // Hentikan proses
    } else if (nomorSuratStatus === 'error') {
        // Jika pengecekan gagal karena error jaringan/server
        Swal.fire({
            icon: 'error',
            title: 'Pengecekan Gagal',
            text: 'Gagal memeriksa ketersediaan nomor surat. Silakan periksa koneksi internet Anda dan coba lagi.',
        });
        return; // Hentikan proses
    }

    // 4. Jika semua validasi lolos, baru lanjutkan ke langkah berikutnya
    currentStep = 2;
    showStep(currentStep);
});

$('#back').on('click', () => {
    currentStep = 1;
    showStep(currentStep);
});

$('#save-draft').on('click', function () {
    if (selectedPersonel.length === 0) {
        Swal.fire('Peringatan!', 'Pilih setidaknya satu personel untuk simpan draft!', 'warning');
        return;
    }

    // Collect all form data
    const formData = new FormData(document.getElementById('pengusulanForm'));

    // Add personel data
    selectedPersonel.forEach(p => {
        if (p.type === 'pegawai') formData.append('pegawai_ids[]', p.id);
        else if (p.type === 'mahasiswa') formData.append('mahasiswa_ids[]', p.id);
    });

    // Add status and draft_id if editing
    formData.append('status_pengajuan', 'draft');
    const draftId = typeof draftIdVar !== 'undefined' ? draftIdVar : '';
    if (draftId) {
        formData.append('draft_id', draftId);
        formData.append('_method', 'PUT');
    }

    // Determine the correct URL
    const url = typeof pengusulanUpdateUrl !== 'undefined' && draftId ? pengusulanUpdateUrl.replace(':draft_id', draftId)
        : (typeof pengusulanStoreUrl !== 'undefined' ? pengusulanStoreUrl : '');

    // Show loading indicator
    const saveButton = $(this);
    saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire('Draft Disimpan!', data.message || 'Draft berhasil disimpan.', 'success')
                    .then(() => window.location.href = typeof pengusulanDraftUrl !== 'undefined' ? pengusulanDraftUrl : '/');
            } else {
                Swal.fire('Gagal!', data.message || 'Gagal menyimpan draft.', 'error');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);

            // ======================= AWAL REVISI =======================
            // Bersihkan error sebelumnya
            $('#nomor_surat_error_container').text('');
            $('input[name="nomor_urutan_surat"]').removeClass('is-invalid');

            let errorMessage = 'Terjadi kesalahan jaringan.';
            if (error.errors) {
                errorMessage = '<strong>Kesalahan Validasi:</strong><ul>';
                for (let key in error.errors) {
                    // Jika ada error spesifik untuk nomor surat, tampilkan di bawah input
                    if (key === 'nomor_surat_usulan_jurusan') {
                        const specificError = error.errors[key].join(', ');
                        $('#nomor_surat_error_container').text(specificError);
                        $('input[name="nomor_urutan_surat"]').addClass('is-invalid');
                    }
                    errorMessage += `<li>${error.errors[key].join(', ')}</li>`;
                }
                errorMessage += '</ul>';
            } else if (error.message) {
                errorMessage = error.message;
            }

            Swal.fire('Error!', errorMessage, 'error');
        })
        .finally(() => {
            saveButton.prop('disabled', false).text('Simpan Draft');
        });
});

$('#create-task').on('click', function () {
    if (selectedPersonel.length === 0) {
        Swal.fire('Peringatan!', 'Pilih setidaknya satu personel!', 'warning'); return;
    }
    Swal.fire({
        title: 'Konfirmasi', text: 'Yakin untuk membuat surat tugas?', icon: 'question',
        showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            currentStep = 3;
            showStep(currentStep);
            const form = $('#pengusulanForm');

            // $('#nomor_surat_display').text(form.find('#nomor_surat_usulan').val() ? form.find('#nomor_surat_usulan').val() + '/PL12.C01/KP/' + new Date().getFullYear() : "___/PL12.C01/KP/" + new Date().getFullYear());
            $('#nomor_surat_display');
            $('#nama_kegiatan_display_text').text(form.find('#nama_kegiatan').val() || '-');
            let tempatKegiatanVal = form.find('#tempat_kegiatan').val();
            $('#nama_penyelenggara_display').text(form.find('#nama_penyelenggara').val() || '-');
            // ======================= AWAL REVISI =======================
const tanggalValue = form.find('#tanggal_pelaksanaan').val() || '-';
let tanggalFormatted = '-';

// Cek apakah ini rentang tanggal atau tanggal tunggal
if (tanggalValue.includes('→')) {
    const dates = tanggalValue.split('→');
    const startDateFormatted = formatFullDate(dates[0]); // Format tanggal mulai
    const endDateFormatted = formatFullDate(dates[1]);   // Format tanggal selesai
    tanggalFormatted = `${startDateFormatted} → ${endDateFormatted}`;
} else if (tanggalValue !== '-') {
    // Ini adalah tanggal tunggal
    tanggalFormatted = formatFullDate(tanggalValue);
}

// Set teks pada elemen preview dengan tanggal yang sudah diformat
$('#tanggal_pelaksanaan_display').text(tanggalFormatted);
// ======================= AKHIR REVISI =======================
            $('#tempat_kegiatan_display').text(tempatKegiatanVal || '-');
            $('#alamat_kegiatan_display_detail').html((form.find('#alamat_kegiatan').val() || '-').replace(/\n/g, '<br>'));
            $('#nama_penyelenggara_display').text(form.find('#nama_penyelenggara').val() || '-');
            const today = new Date();

            // Menggabungkan Tempat dan Alamat Kegiatan
            const tempatKegiatan = form.find('#tempat_kegiatan').val() || '';
            const alamatKegiatan = form.find('#alamat_kegiatan').val() || '';
            let lokasiLengkap = '-'; // Default value

            if (tempatKegiatan && alamatKegiatan) {
                // Jika keduanya diisi, gabungkan dengan baris baru
                lokasiLengkap = tempatKegiatan + '<br>' + alamatKegiatan.replace(/\n/g, '<br>');
            } else if (tempatKegiatan) {
                // Jika hanya tempat yang diisi
                lokasiLengkap = tempatKegiatan;
            } else if (alamatKegiatan) {
                // Jika hanya alamat yang diisi
                lokasiLengkap = alamatKegiatan.replace(/\n/g, '<br>');
            }

            // Mengisi elemen gabungan yang baru
            $('#tempat_dan_alamat_display').html(lokasiLengkap);
            // Baris BARU yang benar
const formattedDate = `${String(today.getDate()).padStart(2, '0')} ${["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"][today.getMonth()]} ${today.getFullYear()}`;
$('#tanggal_surat_formatted_display').text(formattedDate);
            const daftarPersonelContainer = $('#daftar_personel_surat_tugas');
            daftarPersonelContainer.html(''); // Kosongkan kontainer

            if (selectedPersonel.length > 0) {
                let personelHtml = '';
                selectedPersonel.forEach((personel) => {
                    // Mulai buat tabel untuk setiap personel
                    personelHtml += `
                        <table class="table table-borderless table-sm mb-3" style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 30%;">Nama</td>
                                    <td style="width: 5%;">:</td>
                                    <td>${personel.nama || '-'}</td>
                                </tr>`;

                    // Tambahkan baris sesuai dengan tipe personel (Pegawai atau Mahasiswa)
                    if (personel.type === 'pegawai') {
                        personelHtml += `
                                <tr>
                                    <td>NIP</td>
                                    <td>:</td>
                                    <td>${personel.nip || '-'}</td>
                                </tr>
                                <tr>
                                    <td>Pangkat / Golongan</td>
                                    <td>:</td>
                                    <td>${(personel.pangkat || '-') + ' / ' + (personel.golongan || '-')}</td>
                                </tr>
                                <tr>
                                    <td>Jabatan</td>
                                    <td>:</td>
                                    <td>${personel.jabatan || '-'}</td>
                                </tr>`;
                    } else if (personel.type === 'mahasiswa') {
                        personelHtml += `
                                <tr>
                                    <td>NIM</td>
                                    <td>:</td>
                                    <td>${personel.nim || '-'}</td>
                                </tr>
                                <tr>
                                    <td>Jurusan</td>
                                    <td>:</td>
                                    <td>${personel.jurusan || '-'}</td>
                                </tr>
                                <tr>
                                    <td>Program Studi</td>
                                    <td>:</td>
                                    <td>${personel.prodi || '-'}</td>
                                </tr>`;
                    }

                    // Tutup tabel untuk personel ini
                    personelHtml += `
                            </tbody>
                        </table>`;
                });
                
                // Tambahkan semua HTML yang sudah dibuat ke dalam kontainer
                daftarPersonelContainer.html(personelHtml);

            } else {
                // Tampilkan pesan jika tidak ada personel yang dipilih
                daftarPersonelContainer.html('<p class="text-muted fst-italic">Tidak ada personel yang ditugaskan.</p>');
            }
        } // <- kurung kurawal penutup ini mungkin milik fungsi di atasnya, pastikan tidak ikut terhapus
    });
});

$('#back-to-form').on('click', () => { currentStep = 2; showStep(currentStep); });

$('#submit-surat').on('click', function () {
    Swal.fire({
        title: 'Konfirmasi Pengusulan',
        text: 'Data sudah benar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData(document.getElementById('pengusulanForm'));
            selectedPersonel.forEach(p => {
                if (p.type === 'pegawai') formData.append('pegawai_ids[]', p.id);
                else if (p.type === 'mahasiswa') formData.append('mahasiswa_ids[]', p.id);
            });
            formData.append('status_pengajuan', 'diajukan');

            const draftId = typeof draftIdVar !== 'undefined' ? draftIdVar : '';
            if (draftId) {
                formData.append('draft_id', draftId);
                formData.append('_method', 'PUT');
            }

            const url = typeof pengusulanUpdateUrl !== 'undefined' && draftId ? pengusulanUpdateUrl.replace(':draft_id', draftId)
                : (typeof pengusulanStoreUrl !== 'undefined' ? pengusulanStoreUrl : '');

            const submitButton = $(this);
            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengusulkan...');

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', data.message || 'Surat tugas berhasil diusulkan!', 'success')
                            .then(() => window.location.href = typeof pengusulanStatusUrl !== 'undefined' ? pengusulanStatusUrl : '/');
                    } else {
                        Swal.fire('Gagal!', data.message || 'Gagal mengusulkan.', 'error');
                    }
                }).catch(error => {
                    console.error('Fetch Error:', error);

                    // ======================= AWAL REVISI =======================
                    // Bersihkan error sebelumnya
                    $('#nomor_surat_error_container').text('');
                    $('input[name="nomor_urutan_surat"]').removeClass('is-invalid');

                    let errorMessage = 'Terjadi kesalahan jaringan.';
                    if (error.errors) {
                        errorMessage = '<strong>Kesalahan Validasi:</strong><ul>';
                        for (let key in error.errors) {
                            // Jika ada error spesifik untuk nomor surat, tampilkan di bawah input
                            if (key === 'nomor_surat_usulan_jurusan') {
                                const specificError = error.errors[key].join(', ');
                                $('#nomor_surat_error_container').text(specificError);
                                $('input[name="nomor_urutan_surat"]').addClass('is-invalid');
                            }
                            errorMessage += `<li>${error.errors[key].join(', ')}</li>`;
                        }
                        errorMessage += '</ul>';
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    // ======================= AKHIR REVISI =======================

                    Swal.fire('Error!', errorMessage, 'error');
                })
                .finally(() => {
                    submitButton.prop('disabled', false).text('Usulkan');
                });
        }
    });
});

$(document).on('click', '#data-section .dropdown-item[data-value]', function (e) {
    e.preventDefault();
    const section = $(this).data('value');
    $('#data-selection-dropdown').text($(this).text());

    $('#data-pegawai-table, #data-mahasiswa-table').hide();
    if (section === 'data-pegawai') {
        $('#data-pegawai-table').show();
        if ($.fn.DataTable.isDataTable('#pegawaiTable')) {
            $('#pegawaiTable').DataTable().columns.adjust().draw();
        }
    } else if (section === 'data-mahasiswa') {
        $('#data-mahasiswa-table').show();
        if ($.fn.DataTable.isDataTable('#mahasiswaTable')) {
            $('#mahasiswaTable').DataTable().columns.adjust().draw();
        }
    }
});

$('#search-input').on('keyup', function () {
    let tableAPI;
    if ($('#data-pegawai-table').is(':visible')) {
        tableAPI = $('#pegawaiTable').DataTable();
    } else if ($('#data-mahasiswa-table').is(':visible')) {
        tableAPI = $('#mahasiswaTable').DataTable();
    } else {
        return;
    }
    tableAPI.search(this.value).draw();
});

$('.pilih-option').on('click', function () {
    const targetInputId = $(this).data('target');
    const selectedValue = $(this).data('value');
    $('#' + targetInputId).val(selectedValue);
});

$('input[name="pembiayaan_option"]').on('change', function () {
    $('#pembiayaan_value').val($(this).val());
});

$('#pagu_desentralisasi_checkbox').on('change', function () {
    $('#pagu_nominal_input_group').toggle(this.checked);
    if (!this.checked) {
        $('#pagu_nominal').val('');
    }
});

$('#tanggal_pelaksanaan').daterangepicker({
    autoUpdateInput: false,
    locale: {
        cancelLabel: 'Clear',
        format: 'DD/MM/YYYY'
    }
})
    .on('apply.daterangepicker', function (ev, picker) {
        // REVISI DI SINI
        if (picker.startDate.isSame(picker.endDate, 'day')) {
            $(this).val(picker.startDate.format('DD/MM/YYYY'));
        } else {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' → ' + picker.endDate.format('DD/MM/YYYY'));
        }
        // AKHIR REVISI
    })
    .on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    });

// Handler untuk modal tambah alamat
$('#formTambahAlamat').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: "{{ route('alamat.store') }}",
        method: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            if (response.success) {
                // Masukkan alamat lengkap ke textarea
                let alamatLengkap = response.alamat.nama_tempat + '\n' + response.alamat.detail_alamat;
                $('#alamat_kegiatan').val(alamatLengkap);

                // Tutup modal
                $('#modalTambahAlamat').modal('hide');

                // Reset form
                $('#formTambahAlamat')[0].reset();

                // Tampilkan notifikasi sukses
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Alamat berhasil ditambahkan',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function (xhr) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Gagal menambahkan alamat',
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
});