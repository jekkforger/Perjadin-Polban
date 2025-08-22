// public/js/pengusulan.js (VERSI FINAL yang sudah diperbaiki)

// --- FUNGSI HELPER GLOBAL ---
function formatFullDate(dateStr) {
    if (!dateStr || dateStr.trim() === '') return '-';
    const parts = dateStr.trim().split('/');
    if (parts.length !== 3) return dateStr;
    const dateObj = new Date(parts[2], parts[1] - 1, parts[0]);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return dateObj.toLocaleDateString('id-ID', options);
}

// --- VARIABEL GLOBAL DAN FUNGSI FORM STEP ---
let currentStep = 1;
const formSteps = document.querySelectorAll('.form-step');

function showStep(stepNumber) {
    formSteps.forEach((step, index) => {
        step.classList.toggle('form-step-active', index + 1 === stepNumber);
    });

    $('#btn-kembali, #next-to-personel, #save-draft, #create-task, #submit-surat').hide();
    
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

    if (stepNumber === 2) {
        if ($.fn.DataTable.isDataTable('#pegawaiTable')) $('#pegawaiTable').DataTable().columns.adjust().draw();
        if ($.fn.DataTable.isDataTable('#mahasiswaTable')) $('#mahasiswaTable').DataTable().columns.adjust().draw();
    }
}

// --- SEMUA LOGIKA DIMULAI SETELAH DOKUMEN SIAP ---
$(document).ready(function () {
    showStep(1);

    const provinsiDropdownContainer = $('#provinsi-scroll-container');
    const provinsiSearchInput = $('#search-provinsi-input');
    const provinsiInputField = $('#provinsi');
    const provinsiIdField = $('#provinsi_id');

    function loadProvinces() {
        provinsiDropdownContainer.html('<span class="dropdown-item-text px-2">Memuat data...</span>');
        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json`)
            .then(response => {
                if (!response.ok) throw new Error('Gagal memuat data');
                return response.json();
            })
            .then(provinces => {
                provinsiDropdownContainer.empty();
                provinsiDropdownContainer.data('provinces', provinces);
                provinces.forEach(provinsi => {
                    const itemHtml = `<a class="dropdown-item" href="#" data-id="${provinsi.id}" data-name="${provinsi.name}">${provinsi.name}</a>`;
                    provinsiDropdownContainer.append(itemHtml);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                provinsiDropdownContainer.html('<span class="dropdown-item-text text-danger px-2">Gagal memuat provinsi. Coba lagi.</span>');
            });
    }

    loadProvinces(); // Panggil fungsi untuk memuat data saat halaman siap

    provinsiDropdownContainer.on('click', '.dropdown-item', function (e) {
        e.preventDefault();
        provinsiInputField.val($(this).data('name'));
        provinsiIdField.val($(this).data('id'));
        // Menutup dropdown secara manual, karena stopPropagation akan mencegahnya
        // Anda mungkin perlu memastikan dropdown Bootstrap bisa ditutup seperti ini
        // Jika tidak berhasil, bisa gunakan: $(this).closest('.dropdown').removeClass('show');
        $(this).closest('.dropdown-menu').prev('.dropdown-toggle').dropdown('toggle');
    });

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

    provinsiSearchInput.on('click', function (e) {
        e.stopPropagation(); // Mencegah dropdown tertutup saat mengklik input pencarian
    });

    // --- LOGIKA MULTI-LOKASI ---
    let lokasiIndex = $('.lokasi-entry').length;
    $('#btn-tambah-lokasi').on('click', function() {
        const newLokasiHtml = `
            <div class="input-group mb-2 lokasi-entry">
                <span class="input-group-text">${lokasiIndex + 1}.</span>
                <input type="text" name="lokasi[${lokasiIndex}][tempat]" class="form-control" placeholder="Tempat Kegiatan" required>
                <input type="text" name="lokasi[${lokasiIndex}][alamat]" class="form-control" placeholder="Alamat" required>
                <button type="button" class="btn btn-outline-danger btn-hapus-lokasi">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        $('#lokasi-wrapper').append(newLokasiHtml);
        lokasiIndex++;
    });

    $('#lokasi-wrapper').on('click', '.btn-hapus-lokasi', function() {
        $(this).closest('.lokasi-entry').remove();
        lokasiIndex = 0; // Reset
        $('#lokasi-wrapper .lokasi-entry').each(function(index) {
            $(this).find('.input-group-text').text(`${index + 1}.`);
            $(this).find('input[name*="[tempat]"]').attr('name', `lokasi[${index}][tempat]`);
            $(this).find('input[name*="[alamat]"]').attr('name', `lokasi[${index}][alamat]`);
            lokasiIndex = index + 1;
        });
    });

    // --- BLOK VALIDASI NOMOR SURAT ---
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
            await checkNomorAvailability();
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
    
    // --- Inisialisasi dan Event Listener untuk Nomor Surat ---
    const debouncedCheck = debounce(checkNomorAvailability, 500);
    nomorUrutInput.on('input', debouncedCheck);
    tahunSelect.on('change', () => fetchLatestNomorUrut(tahunSelect.val()));
    loadUsedNumbers();
    fetchLatestNomorUrut(tahunSelect.val());
    if (!nomorUrutInput.val()) {
        nextButton.prop('disabled', true);
    }
    
    // --- Inisialisasi DataTable ---
    if (!$.fn.DataTable.isDataTable('#pegawaiTable')) {
        $('#pegawaiTable').DataTable({ paging: true, searching: true, info: true, pageLength: 5, lengthMenu: [5, 10, 15], order: [[1, 'asc']], columnDefs: [{ orderable: false, targets: 0 }] });
    }
    if (!$.fn.DataTable.isDataTable('#mahasiswaTable')) {
        $('#mahasiswaTable').DataTable({ paging: true, searching: true, info: true, pageLength: 10, lengthMenu: [5, 10, 15], order: [[1, 'asc']], columnDefs: [{ orderable: false, targets: 0 }] });
    }

    // --- Sisa event listener lainnya ---
    $('#create-task').on('click', function () {
    if (selectedPersonel.length === 0) {
        Swal.fire('Peringatan!', 'Pilih setidaknya satu personel!', 'warning');
        return;
    }

    const form = $('#pengusulanForm');
    const personelPreviewContainer = $('#personnel-preview-container');
    const attachmentPreviewContainer = $('#attachment-preview-container');
    const pElement = personelPreviewContainer.closest('.surat-tugas-content').find('p').first();

    // Reset
    personelPreviewContainer.empty();
    attachmentPreviewContainer.empty().hide();
    pElement.text('Direktur memberi tugas kepada:');

    // Mengisi data umum (tidak berubah)
    const nomorSuratDisplay = `${form.find('input[name="nomor_urutan_surat"]').val()}/${form.find('input[name="kode_pengusul"]').val()}/${form.find('input[name="kode_perihal"]').val()}/${form.find('select[name="tahun_nomor_surat"]').val()}`;
    const formattedDate = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    $('#nomor_surat_display').text(nomorSuratDisplay);
    $('#nama_kegiatan_display_text').text(form.find('textarea[name="nama_kegiatan"]').val() || '-');
    $('#nama_penyelenggara_display').text(form.find('input[name="nama_penyelenggara"]').val() || '-');
    $('#tanggal_surat_formatted_display').text(formattedDate);
    const tanggalValue = form.find('#tanggal_pelaksanaan').val() || '-';
    let tanggalFormatted = '-';
    if (tanggalValue.includes('→')) {
        const dates = tanggalValue.split('→');
        tanggalFormatted = `${formatFullDate(dates[0])} s.d. ${formatFullDate(dates[1])}`;
    } else if (tanggalValue !== '-') {
        tanggalFormatted = formatFullDate(tanggalValue);
    }
    $('#tanggal_pelaksanaan_display').text(tanggalFormatted);
    const lokasiPreviewContainer = $('#lokasi_kegiatan_preview');
    lokasiPreviewContainer.empty();
    let lokasiHtml = '<ol style="margin: 0; padding-left: 1.2em;">';
    $('.lokasi-entry').each(function() {
        const tempat = $(this).find('input[name*="[tempat]"]').val();
        const alamat = $(this).find('input[name*="[alamat]"]').val();
        if (tempat || alamat) {
            lokasiHtml += `<li style="margin-bottom: 5px;">${tempat ? `<strong>${tempat}</strong>` : ''}${tempat && alamat ? '<br>' : ''}${alamat ? alamat.replace(/\n/g, '<br>') : ''}</li>`;
        }
    });
    lokasiHtml += '</ol>';
    lokasiPreviewContainer.html($('.lokasi-entry').length > 0 ? lokasiHtml : '-');

    const personnelCount = selectedPersonel.length;
    let personelHtml = '';

    if (personnelCount > 0 && personnelCount <= 1) {
        // Tampilan vertikal untuk 1 orang (tetap sama)
        selectedPersonel.forEach(personel => {
            personelHtml += `<table class="table table-borderless table-sm mb-3" style="width: 100%;"><tbody>`;
            personelHtml += `<tr><td style="width: 30%;">Nama</td><td style="width: 5%;">:</td><td>${personel.nama || '-'}</td></tr>`;
            if (personel.type === 'pegawai') {
                personelHtml += `<tr><td>NIP</td><td>:</td><td>${personel.nip || '-'}</td></tr>`;
                personelHtml += `<tr><td>Pangkat/Golongan</td><td>:</td><td>${(personel.pangkat || '-') + ' / ' + (personel.golongan || '-')}</td></tr>`;
                personelHtml += `<tr><td>Jabatan</td><td>:</td><td>${personel.jabatan || '-'}</td></tr>`;
            } else { // Mahasiswa
                personelHtml += `<tr><td>NIM</td><td>:</td><td>${personel.nim || '-'}</td></tr>`;
                personelHtml += `<tr><td>Jurusan</td><td>:</td><td>${personel.jurusan || '-'}</td></tr>`;
            }
            personelHtml += `</tbody></table>`;
        });
        personelPreviewContainer.html(personelHtml);

    } else if (personnelCount > 1 && personnelCount <= 5) {
        // TAMPILAN TABEL UNTUK 2-5 ORANG (BISA CAMPURAN)
        const pegawai = selectedPersonel.filter(p => p.type === 'pegawai');
        const mahasiswa = selectedPersonel.filter(p => p.type === 'mahasiswa');

        // Buat tabel untuk Pegawai jika ada
        if (pegawai.length > 0) {
            personelHtml += `<h6>Pegawai yang Ditugaskan:</h6>`;
            personelHtml += `<table class="table table-bordered table-sm" style="font-size: 11pt;"><thead class="text-center"><tr><th>Nama</th><th>NIP</th><th>Pangkat</th><th>Golongan</th><th>Jabatan</th></tr></thead><tbody>`;
            pegawai.forEach(p => {
                personelHtml += `<tr><td>${p.nama || '-'}</td><td>${p.nip || '-'}</td><td>${p.pangkat || '-'}</td><td>${p.golongan || '-'}</td><td>${p.jabatan || '-'}</td></tr>`;
            });
            personelHtml += `</tbody></table>`;
        }

        // Buat tabel untuk Mahasiswa jika ada
        if (mahasiswa.length > 0) {
            personelHtml += `<h6 style="margin-top: 15px;">Mahasiswa yang Ditugaskan:</h6>`;
            personelHtml += `<table class="table table-bordered table-sm" style="font-size: 11pt;"><thead class="text-center"><tr><th>Nama</th><th>NIM</th><th>Jurusan</th><th>Prodi</th></tr></thead><tbody>`;
            mahasiswa.forEach(p => {
                personelHtml += `<tr><td>${p.nama || '-'}</td><td>${p.nim || '-'}</td><td>${p.jurusan || '-'}</td><td>${p.prodi || '-'}</td></tr>`;
            });
            personelHtml += `</tbody></table>`;
        }
        personelPreviewContainer.html(personelHtml);

    } else if (personnelCount > 5) {
        pElement.html('Direktur Politeknik Negeri Bandung menugaskan kepada yang namanya tercantum di dalam lampiran pada surat tugas ini');
        
        const ITEMS_PER_PAGE = 9;
        
        // --- BACA DATA SEKALI DAN SIMPAN KE VARIABEL (DI LUAR FUNGSI) ---
        const kementerian = $('#attachment-preview-container').data('kementerian') || 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI';
        const direktur = $('#attachment-preview-container').data('direktur') || 'Marwansyah, S.E., M.Si., Ph.D.';
        const nip = $('#attachment-preview-container').data('nip') || '1964050419900310021';
        
        // --- FUNGSI UNTUK MEMBUAT TEMPLATE HALAMAN LAMPIRAN ---
        const createAttachmentPageHtml = (isLastPage) => {
            const signatureHtml = isLastPage ? `
                <table class="table table-borderless table-sm mt-5" style="width: 100%;">
                    <tr valign="top">
                        <td style="width: 50%;"></td>
                        <td style="width: 50%; text-align: left; vertical-align: bottom;">
                            <p class="mb-1">Bandung, ${formattedDate}</p>
                            <p class="mb-1">Direktur,</p><div style="height: 60px;"></div>
                            <p class="mb-0">${direktur}</p><p class="mb-0">NIP ${nip}</p>
                        </td>
                    </tr>
                </table>` : '';

            // Perhatikan: Tidak ada kop surat di sini, hanya judul lampiran
            return `
                <div class="document-container surat-tugas-body" style="min-height: auto; margin-bottom: 2rem; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    <div class="surat-tugas-content">
                        <p style="text-align: left; margin-top: 0;">Lampiran: ${nomorSuratDisplay}</p>
                        <div class="personnel-attachment-list"></div>
                        ${signatureHtml}
                    </div>
                </div>`;
        };

        // --- GABUNGKAN SEMUA PERSONEL, LALU BAGI MENJADI HALAMAN-HALAMAN ---
        const allPersonnelChunks = [];
        let currentChunk = [];
        const pegawai = selectedPersonel.filter(p => p.type === 'pegawai');
        const mahasiswa = selectedPersonel.filter(p => p.type === 'mahasiswa');

        // Gabungkan semua ke dalam satu array untuk dipecah
        const allData = [...pegawai, ...mahasiswa];

        for (let i = 0; i < allData.length; i += ITEMS_PER_PAGE) {
            allPersonnelChunks.push(allData.slice(i, i + ITEMS_PER_PAGE));
        }

        // --- RENDER SETIAP HALAMAN ---
        allPersonnelChunks.forEach((chunk, pageIndex) => {
            const isLastPage = pageIndex === allPersonnelChunks.length - 1;
            const pageHtml = $(createAttachmentPageHtml(isLastPage));
            const contentArea = pageHtml.find('.personnel-attachment-list');
            
            const pegawaiInChunk = chunk.filter(p => p.type === 'pegawai');
            const mahasiswaInChunk = chunk.filter(p => p.type === 'mahasiswa');
            
            let tableHtml = '';

            if (pegawaiInChunk.length > 0) {
                tableHtml += `<h6>1. Pegawai</h6><table class="table table-bordered table-sm"><thead><tr><th>Nama</th><th>NIP</th><th>Pangkat</th><th>Golongan</th><th>Jabatan</th></tr></thead><tbody>`;
                pegawaiInChunk.forEach(p => {
                    tableHtml += `<tr><td>${p.nama}</td><td>${p.nip}</td><td>${p.pangkat}</td><td>${p.golongan}</td><td>${p.jabatan}</td></tr>`;
                });
                tableHtml += `</tbody></table>`;
            }

            if (mahasiswaInChunk.length > 0) {
                tableHtml += `<h6 style="margin-top:20px;">2. Mahasiswa</h6><table class="table table-bordered table-sm"><thead><tr><th>Nama</th><th>NIM</th><th>Jurusan</th><th>Prodi</th></tr></thead><tbody>`;
                mahasiswaInChunk.forEach(p => {
                    tableHtml += `<tr><td>${p.nama}</td><td>${p.nim}</td><td>${p.jurusan}</td><td>${p.prodi}</td></tr>`;
                });
                tableHtml += `</tbody></table>`;
            }
            
            contentArea.html(tableHtml);
            attachmentPreviewContainer.append(pageHtml);
        });

        attachmentPreviewContainer.show();
        // ===================================================================
        // <-- AKHIR LOGIKA BARU -->
        // ===================================================================
    }
    
    
    // Pindah ke langkah preview
    currentStep = 3;
    showStep(currentStep);
});
    $('#btn-kembali').on('click', () => {
        // Jika kita sedang berada di langkah 3 (preview) dan akan kembali,
        // maka kita perlu mereset tampilan lampiran.
        if (currentStep === 3) {
            const attachmentPreviewContainer = $('#attachment-preview-container');
            const personelPreviewContainer = $('#personnel-preview-container');
            const pElement = personelPreviewContainer.closest('.surat-tugas-content').find('p').first();

            // 1. Sembunyikan container lampiran
            attachmentPreviewContainer.hide();

            // 2. Kembalikan teks paragraf di surat utama ke teks aslinya
            pElement.text('Direktur memberi tugas kepada:');
        }
        currentStep--;
        showStep(currentStep);
    });
    
});

// ... (sisa kode Anda dari `let selectedPersonel` sampai akhir) ...

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

    const lokasiPreviewContainer = $('#lokasi_kegiatan_preview');
    lokasiPreviewContainer.empty(); // Kosongkan dulu
    
    let lokasiHtml = '<ol style="margin: 0; padding-left: 1.2em;">';
    let lokasiAda = false;

    // Ambil semua input lokasi yang ada di form
    $('.lokasi-entry').each(function() {
        const tempat = $(this).find('input[name*="[tempat]"]').val();
        const alamat = $(this).find('input[name*="[alamat]"]').val();
        
        if (tempat || alamat) {
            lokasiAda = true;
            lokasiHtml += `<li style="margin-bottom: 5px;">
                                ${tempat ? `<strong>${tempat}</strong>` : ''}
                                ${tempat && alamat ? '<br>' : ''}
                                ${alamat ? alamat.replace(/\n/g, '<br>') : ''}
                           </li>`;
        }
    });

    lokasiHtml += '</ol>';

    if (lokasiAda) {
        lokasiPreviewContainer.html(lokasiHtml);
    } else {
        lokasiPreviewContainer.text('-'); // Tampilkan strip jika tidak ada lokasi
    }
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