{{-- resources/views/layouts/sekdir/review.blade.php --}}
@extends('layouts.sekdir.layout')

@section('title', 'Penomoran Surat Tugas Resmi')

@section('sekdir_content')
<div class="sekdir-container px-4 py-3">
    <h1 class="sekdir-page-title mb-4">Penomoran Surat Tugas</h1>

    <div class="card shadow mb-4">
        <div class="card-body p-5">
            {{-- =================================================================== --}}
            {{-- PRATINJAU SURAT UTAMA --}}
            {{-- =================================================================== --}}
            <div class="paper-preview-wrapper">
                    <div class="document-container surat-tugas-body" style="min-height: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                <div class="surat-tugas-header">
                    <img src="{{ asset('img/polban.png') }}" alt="POLBAN Logo" />
                    <div class="surat-tugas-header-text">
                        <h1>{{ $suratSettings->nama_kementerian ?? 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI' }}</h1>
                        <h2>POLITEKNIK NEGERI BANDUNG</h2>
                        <p>Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012, Kotak Pos 1234,</p>
                        <p>Telepon: (022) 2013789, Faksimile: (022) 2013889</p>
                        <p>Laman: <a href="https://www.polban.ac.id" target="_blank">www.polban.ac.id</a>, Pos Elektronik: polban@polban.ac.id</p>
                    </div>
                </div>
                <hr class="surat-tugas-header-line" />

                <div class="surat-tugas-content">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h4 class="surattugas" style="text-decoration: underline; margin-bottom: 4px;">SURAT TUGAS</h4>
                        <h4 class="nomor" style="margin-top: 0;">
                            Nomor: <span id="nomorSuratPreview" class="fw-bold text-danger">{{ $suratTugas->nomor_surat_tugas_resmi ?? '[Nomor Resmi Belum Diinput]' }}</span>
                        </h4>
                    </div>
                    
                    @include('layouts.pengusul.partials._personnel_display', ['suratTugas' => $suratTugas])

                    {{-- @if ($suratTugas->detailPelaksanaTugas->count() <= 5) --}}
                        <p style="margin-top: 20px; margin-bottom: 10px;">
                            Untuk mengikuti kegiatan <strong>{{ $suratTugas->perihal_tugas }}</strong>, diselenggarakan oleh <strong>{{ $suratTugas->nama_penyelenggara }}</strong> pada:
                        </p>
                        <table class="table table-borderless table-sm">
                            <tbody>
                                <tr>
                                    <td style="width: 30%; vertical-align: top;">Hari / Tanggal</td><td style="width: 5%; vertical-align: top;">:</td>
                                    <td>
                                        @if ($suratTugas->tanggal_berangkat->isSameDay($suratTugas->tanggal_kembali))
                                            {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }}
                                        @else
                                            {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }} s.d. {{ $suratTugas->tanggal_kembali->translatedFormat('l, j F Y') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top;">Tempat Kegiatan</td><td style="vertical-align: top;">:</td>
                                    <td>
                                        <ol style="margin: 0; padding-left: 1.2em;">
                                            @foreach ($suratTugas->lokasi_kegiatan as $lokasi)
                                                <li style="margin-bottom: 5px;">
                                                    <strong>{{ $lokasi['tempat'] }}</strong><br>
                                                    {!! nl2br(e($lokasi['alamat'])) !!}
                                                </li>
                                            @endforeach
                                        </ol>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    {{-- @endif --}}
                    
                    <p style="margin-top: 20px;">Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>

                    <table class="table table-borderless table-sm mt-5">
                        <tr valign="top">
                            <td style="width: 50%; vertical-align: bottom;">
                                <div>
                                    <p class="fw-bold mb-1">Tembusan:</p>
                                    <ol class="mb-0" style="padding-left: 20px;">
                                        @forelse ($suratSettings->tembusan_default ?? [] as $tembusan)
                                            <li>{{ $tembusan }}</li>
                                        @empty
                                            <li>-</li>
                                        @endforelse
                                    </ol>
                                </div>
                            </td>
                            <td style="width: 50%; text-align: left; vertical-align: bottom; position: relative;">
                                @if($suratTugas->wadir_signature_data)
                                    @php $wadirPosition = $suratTugas->wadir_signature_position ?? ['x' => -80, 'y' => -15, 'width' => 70, 'height' => 50]; @endphp
                                    <div style="position: absolute; left: {{ $wadirPosition['x'] }}px; top: {{ $wadirPosition['y'] }}px; width: {{ $wadirPosition['width'] }}px; height: {{ $wadirPosition['height'] }}px; z-index: 9;">
                                        <img src="{{ $suratTugas->wadir_signature_data }}" alt="Paraf Wadir" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                @elseif($suratTugas->wadirApprover && $suratTugas->wadirApprover->para_file_path)
                                    <div style="position: absolute; left: -80px; top: -15px; width: 70px; height: 50px; z-index: 9;">
                                        <img src="{{ Storage::url($suratTugas->wadirApprover->para_file_path) }}" alt="Paraf Wadir" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                @endif
                                <p class="mb-1">Bandung, {{ $suratTugas->tanggal_paraf_wadir ? $suratTugas->tanggal_paraf_wadir->translatedFormat('j F Y') : now()->translatedFormat('j F Y') }}</p>
                                <p class="mb-1">Direktur,</p>
                                <div style="height: 60px;"></div>
                                <p class="fw-bold mb-0" style="margin-top: -5px;">{{ $suratSettings->nama_direktur ?? '' }}</p>
                                <p class="mb-0">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            </div>
        </div>

        {{-- =================================================================== --}}
        {{-- LOGIKA UNTUK MENAMPILKAN LAMPIRAN --}}
        {{-- =================================================================== --}}
        @if ($suratTugas->detailPelaksanaTugas->count() > 5)
            @php
                $personnels = $suratTugas->detailPelaksanaTugas;
                $pegawai = $personnels->where('personable_type', \App\Models\Pegawai::class);
                $mahasiswa = $personnels->where('personable_type', \App\Models\Mahasiswa::class);
                $itemsPerPage = 9;
                $allPersonnel = $pegawai->merge($mahasiswa);
                $allPersonnelChunks = $allPersonnel->chunk($itemsPerPage);
            @endphp
            
            @foreach ($allPersonnelChunks as $pageIndex => $chunk)
                <div class="card-body p-5" style="margin-top: 2rem;">
                    <div class="document-container surat-tugas-body" style="min-height: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                        <div class="surat-tugas-content">
                            {{-- <h4 style="text-align: left; margin-bottom: 5px; font-weight: normal;">Lampiran Surat Tugas</h4> --}}
                            <p style="text-align: left; margin-top: 0;">Lampiran: <span class="fw-bold text-danger">[Nomor Resmi Belum Diinput]</span></p>
                            
                            @php
                                $pegawaiInChunk = $chunk->where('personable_type', \App\Models\Pegawai::class);
                                $mahasiswaInChunk = $chunk->where('personable_type', \App\Models\Mahasiswa::class);
                            @endphp

                            @if ($pegawaiInChunk->isNotEmpty())
                                <p>1. Pegawai</p>
                                <table class="table table-bordered table-sm">
                                    <thead><tr class="text-center"><th>Nama</th><th>NIP</th><th>Pangkat</th><th>Golongan</th><th>Jabatan</th></tr></thead>
                                    <tbody>
                                        @foreach ($pegawaiInChunk as $detail)
                                            <tr><td>{{ $detail->personable->nama }}</td><td>{{ $detail->personable->nip }}</td><td>{{ $detail->personable->pangkat }}</td><td>{{ $detail->personable->golongan }}</td><td>{{ $detail->personable->jabatan }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @if ($mahasiswaInChunk->isNotEmpty())
                                <p>2. Mahasiswa</p>
                                <table class="table table-bordered table-sm">
                                    <thead><tr class="text-center"><th>Nama</th><th>NIM</th><th>Jurusan</th><th>Prodi</th></tr></thead>
                                    <tbody>
                                        @foreach ($mahasiswaInChunk as $detail)
                                            <tr><td>{{ $detail->personable->nama }}</td><td>{{ $detail->personable->nim }}</td><td>{{ $detail->personable->jurusan }}</td><td>{{ $detail->personable->prodi }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @if ($loop->last)
                                <table class="table table-borderless table-sm mt-5">
                                    <tr valign="top">
                                        <td style="width: 50%;"></td>
                                        <td style="width: 50%; text-align: left; vertical-align: bottom;">
                                            <p class="mb-1">Bandung, {{ $suratTugas->tanggal_paraf_wadir ? $suratTugas->tanggal_paraf_wadir->translatedFormat('j F Y') : now()->translatedFormat('j F Y') }}</p>
                                            <p class="mb-1">Direktur,</p>
                                            <div style="height: 60px;"></div>
                                            <p class="fw-bold mb-0" style="margin-top: -5px;">{{ $suratSettings->nama_direktur ?? '' }}</p>
                                            <p class="mb-0">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Panel Aksi Sekdir --}}
        <div class="card-footer bg-light p-3 text-center">
            <button type="button" class="btn btn-primary btn-lg" id="btnBukaModalInput">
                <i class="bi bi-pencil-square"></i> Input Nomor Surat Resmi
            </button>
            <button type="button" class="btn btn-success btn-lg" id="btnBukaModalKonfirmasi" style="display:none;">
                <i class="bi bi-check-circle"></i> Konfirmasi & Teruskan ke Direktur
            </button>
        </div>

        <div class="mt-3">
            <a href="{{ route('sekdir.nomorSurat') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
{{-- =================================================================== --}}
{{--   BAGIAN 3: MODAL (POPUP) UNTUK ALUR KERJA                        --}}
{{-- =================================================================== --}}

<!-- Modal 1: Untuk Menginput Nomor Surat FINAL -->
<div class="modal fade" id="inputNomorModal" tabindex="-1" aria-labelledby="inputNomorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="inputNomorModalLabel">Input Nomor Surat Resmi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Format Nomor Surat Tugas Final:</label>
          <div class="d-flex align-items-center gap-2 flex-wrap">
              {{-- Input Nomor Urut dengan saran --}}
              <input type="number" id="inputNomorUrut" class="form-control" placeholder="Nomor Urut" required style="width: 100px;" value="{{ $saranNomorUrut }}">
              /
              {{-- Kode Unit Kerja (otomatis dari config/surat.php atau default) --}}
              <input type="text" id="inputKodeUnit" class="form-control" value="PL1" readonly style="width: 80px;">
              /
              {{-- Kode Perihal (otomatis dari surat tugas) --}}
              <input type="text" id="inputKodePerihal" class="form-control" value="{{ $suratTugas->kode_perihal ?? 'RT.01.00' }}" readonly style="width: 100px;">
              /
              {{-- Pilihan Tahun --}}
              <select id="inputTahun" class="form-select" required style="width: 100px;">
                  @foreach($tahunList as $tahun)
                      <option value="{{ $tahun }}" {{ $tahun == now()->year ? 'selected' : '' }}>{{ $tahun }}</option>
                  @endforeach
              </select>
          </div>
          {{-- Tampilkan saran nomor berdasarkan data dari Controller --}}
          <div class="form-text mt-2">
              Nomor urut terakhir yang digunakan tahun ini: <strong>{{ $nomorTerakhir }}</strong>.
              Saran nomor berikutnya: <strong class="text-success">{{ $saranNomorUrut }}</strong>.
          </div>
          @error('nomor_urutan_surat')
              <div class="text-danger small mt-2">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
        <button type="button" class="btn btn-primary" id="btnSimpanPreview">Terapkan ke Pratinjau</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal 2: Untuk Konfirmasi Final Sebelum Submit -->
<div class="modal fade" id="konfirmasiModal" tabindex="-1" aria-labelledby="konfirmasiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="konfirmasiModalLabel">Konfirmasi Final</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p>Anda akan menyimpan dan meneruskan surat dengan nomor resmi berikut:</p>
        <p class="lead text-center fw-bold bg-light p-2 rounded" id="nomorFinalKonfirmasi"></p>
        <p class="mt-3">Apakah Anda sudah yakin? Nomor ini bersifat final.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak, Cek Lagi</button>
        <button type="button" class="btn btn-success" id="btnSubmitFinal">Ya, Simpan & Teruskan</button>
      </div>
    </div>
  </div>
</div>

{{-- Form Tersembunyi untuk Proses Submit ke Server --}}
{{-- Nama input "nomor_surat_tugas_resmi" harus cocok dengan validasi di Controller --}}
{{-- Form Tersembunyi untuk Proses Submit ke Server (DENGAN INPUT TAMBAHAN) --}}
<form id="assignNumberForm" action="{{ route('sekdir.nomorSurat.assign', $suratTugas->surat_tugas_id) }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="nomor_urutan_surat" id="hiddenNomorUrut">
    <input type="hidden" name="kode_unit_kerja" id="hiddenKodeUnit">
    <input type="hidden" name="kode_perihal" id="hiddenKodePerihal">
    <input type="hidden" name="tahun_nomor_surat" id="hiddenTahun">
</form>
@endsection

@push('styles')
    {{-- {{-- <link rel="stylesheet" href="{{ asset('css/ttd_digital.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('css/sekdir_content.css') }}"> 
    <link rel="stylesheet" href="{{ asset('css/pengusul_content.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('css/template-surat.css') }}"> --}}
    
    {{-- CSS khusus untuk memperbaiki tampilan history/show --}}
    {{-- <style>
        /* Wrapper untuk pratinjau kertas - menyesuaikan dengan pengusul/preview-surat */
        .paper-preview-wrapper {
            width: 100%;
            max-width: 900px; /* Batasi lebar maksimal */
            margin: 0 auto 2rem auto; /* Center dan beri margin bottom */
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        /* Override document-container untuk ukuran yang lebih proporsional */
        .paper-preview-wrapper .document-container {
            width: 100%;
            max-width: none;
            min-height: auto;
            padding: 40px 60px; /* Padding yang lebih proporsional */
            margin: 0;
            box-shadow: none;
            border-radius: 0;
            background: white;
            font-family: 'Times New Roman', serif;
            font-size: 12pt; /* Ukuran font yang lebih besar */
            line-height: 1.6;
        }
        
        /* Perbaiki header surat */
        .paper-preview-wrapper .surat-tugas-header {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .paper-preview-wrapper .surat-tugas-header img {
            width: 80px;
            height: auto;
            flex-shrink: 0;
        }
        
        .paper-preview-wrapper .surat-tugas-header-text {
            flex: 1;
            text-align: center;
            font-size: 11pt;
        }
        
        .paper-preview-wrapper .surat-tugas-header-text h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 3px 0;
            line-height: 1.2;
        }
        
        .paper-preview-wrapper .surat-tugas-header-text h2 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0 0 8px 0;
            line-height: 1.2;
        }
        
        .paper-preview-wrapper .surat-tugas-header-text p {
            font-size: 10pt;
            margin: 1px 0;
            line-height: 1.3;
        }
        
        .paper-preview-wrapper .surat-tugas-header-line {
            border: 0;
            border-top: 2px solid #000;
            margin: 15px 0 25px 0;
        }
        
        /* Perbaiki konten surat */
        .paper-preview-wrapper .surat-tugas-content {
            font-size: 12pt;
            line-height: 1.6;
        }
        
        .paper-preview-wrapper .surat-tugas-content h4 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
            line-height: 1.3;
        }
        
        .paper-preview-wrapper .surat-tugas-content p {
            margin-bottom: 12px;
            text-align: justify;
        }
        
        /* Perbaiki tabel */
        .paper-preview-wrapper .table {
            font-size: 12pt;
            line-height: 1.5;
        }
        
        .paper-preview-wrapper .table td {
            padding: 4px 8px;
            border: none;
            vertical-align: top;
        }
        
        .paper-preview-wrapper .table-bordered td,
        .paper-preview-wrapper .table-bordered th {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11pt;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .paper-preview-wrapper .document-container {
                padding: 20px 30px;
                font-size: 11pt;
            }
            
            .paper-preview-wrapper .surat-tugas-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 15px;
            }
            
            .paper-preview-wrapper .surat-tugas-header img {
                width: 70px;
            }
        }
        
        @media print {
            .paper-preview-wrapper {
                box-shadow: none;
                margin: 0;
                max-width: none;
            }
            
            .paper-preview-wrapper .document-container {
                padding: 2cm 3cm;
                font-size: 12pt;
            }
        }
    </style> --}}
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="{{ asset('js/ttd_digital.js') }}"></script>
@endpush

{{-- =================================================================== --}}
{{--   BAGIAN 4: JAVASCRIPT UNTUK MEMBUAT SEMUANYA INTERAKTIF          --}}
{{-- =================================================================== --}}
@push('scripts')
{{-- Pastikan Bootstrap JS sudah di-load dari layout utama (`layouts.main`) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputModalEl = document.getElementById('inputNomorModal');
    const konfirmasiModalEl = document.getElementById('konfirmasiModal');
    if (!inputModalEl || !konfirmasiModalEl) return;

    const inputModal = new bootstrap.Modal(inputModalEl);
    const konfirmasiModal = new bootstrap.Modal(konfirmasiModalEl);

    // Referensi ke tombol-tombol
    const btnBukaModalInput = document.getElementById('btnBukaModalInput');
    const btnBukaModalKonfirmasi = document.getElementById('btnBukaModalKonfirmasi');
    const btnSimpanPreview = document.getElementById('btnSimpanPreview');
    const btnSubmitFinal = document.getElementById('btnSubmitFinal');
    
    // Referensi ke input di dalam modal
    const inputNomorUrut = document.getElementById('inputNomorUrut');
    const inputKodeUnit = document.getElementById('inputKodeUnit');
    const inputKodePerihal = document.getElementById('inputKodePerihal');
    const inputTahun = document.getElementById('inputTahun');

    // Referensi ke elemen tampilan dan form
    const previewSpan = document.getElementById('nomorSuratPreview');
    const konfirmasiSpan = document.getElementById('nomorFinalKonfirmasi');
    const finalForm = document.getElementById('assignNumberForm');

    // Referensi ke hidden inputs di form
    const hiddenNomorUrut = document.getElementById('hiddenNomorUrut');
    const hiddenKodeUnit = document.getElementById('hiddenKodeUnit');
    const hiddenKodePerihal = document.getElementById('hiddenKodePerihal');
    const hiddenTahun = document.getElementById('hiddenTahun');
    
    let nomorResmiValue = ''; // Variabel untuk menyimpan nomor sementara

    // Alur saat tombol "Input Nomor Surat Resmi" diklik
    btnBukaModalInput.addEventListener('click', () => { inputModal.show(); });

    // Alur saat tombol "Terapkan ke Pratinjau" di modal diklik
    btnSimpanPreview.addEventListener('click', () => {
        // Bangun nomor surat lengkap dari input yang terpisah
        const nomorUrut = inputNomorUrut.value.trim();
        const kodeUnit = inputKodeUnit.value.trim();
        const kodePerihal = inputKodePerihal.value.trim();
        const tahun = inputTahun.value.trim();
        
        if (nomorUrut === '') {
            alert('Nomor urut tidak boleh kosong.');
            return;
        }

        nomorResmiValue = `${nomorUrut}/${kodeUnit}/${kodePerihal}/${tahun}`;
        
        // Perbarui tampilan
        previewSpan.textContent = nomorResmiValue;
        previewSpan.classList.remove('text-danger');
        previewSpan.classList.add('text-dark');

        // Ganti tombol
        btnBukaModalInput.style.display = 'none';
        btnBukaModalKonfirmasi.style.display = 'inline-block';
        inputModal.hide();
    });

    // Alur saat tombol "Konfirmasi & Teruskan" diklik
    btnBukaModalKonfirmasi.addEventListener('click', () => {
        konfirmasiSpan.textContent = nomorResmiValue;
        konfirmasiModal.show();
    });

    // Alur saat tombol "Ya, Simpan & Teruskan" di modal konfirmasi diklik
    btnSubmitFinal.addEventListener('click', function () {
        // Isi nilai ke input tersembunyi di form utama
        hiddenNomorUrut.value = inputNomorUrut.value.trim();
        hiddenKodeUnit.value = inputKodeUnit.value.trim();
        hiddenKodePerihal.value = inputKodePerihal.value.trim();
        hiddenTahun.value = inputTahun.value.trim();

        // Nonaktifkan tombol dan submit form
        this.disabled = true;
        this.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Memproses...`;
        finalForm.submit();
    });
});
</script>
@endpush