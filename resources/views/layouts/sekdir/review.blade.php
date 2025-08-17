{{-- resources/views/layouts/sekdir/review.blade.php --}}
@extends('layouts.sekdir.layout')

@section('title', 'Penomoran Surat Tugas Resmi')

@section('sekdir_content')
<div class="sekdir-container px-4 py-3">
    <h1 class="sekdir-page-title mb-4">Nomor Surat</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        {{-- =================================================================== --}}
        {{--   BAGIAN 1: PRATINJAU DOKUMEN SURAT TUGAS (STRUKTUR DIPERBAIKI)   --}}
        {{-- =================================================================== --}}
        
        <div class="document-container surat-tugas-body">
            
            <!-- =========== HEADER HALAMAN =========== -->
            <div class="surat-tugas-header">
                <img src="{{ asset('img/polban.png') }}" alt="POLBAN Logo" />
                <div class="surat-tugas-header-text">
                    <h1>{{ $suratSettings->nama_kementerian ?? ''}}</h1>
                    <h2>POLITEKNIK NEGERI BANDUNG</h2>
                    <p>Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012, Kotak Pos 1234,</p>
                    <p>Telepon: (022) 2013789, Faksimile: (022) 2013889</p>
                    <p>Laman: <a href="https://www.polban.ac.id" target="_blank">www.polban.ac.id</a>, Pos Elektronik: polban@polban.ac.id</p>
                </div>
            </div>
            <hr class="surat-tugas-header-line" />

            <!-- =========== ISI UTAMA HALAMAN =========== -->
            <div class="surat-tugas-content">

                {{-- Judul dan Nomor Surat --}}
                <div style="width: fit-content; margin-left: auto; margin-right: auto; transform: translateX(30px); text-align: left;">
  <h4 class="surattugas" style="text-decoration: margin-bottom: 4px;">SURAT TUGAS</h4>
  <h4 class="nomor" style="margin-top: 0;">
    Nomor: <span id="nomorSuratPreview" class="fw-bold text-danger">{{ $suratTugas->nomor_surat_tugas_resmi ?? '[Nomor Resmi Belum Diinput]' }}</span>
</h4>
</div>
                <p style="margin-bottom: 10px; text-align: justify;">
                    Direktur memberi tugas kepada:
                </p>

                {{-- Daftar Personel yang Ditugaskan (menggunakan format Tabel) --}}
                <div id="daftar_personel_surat_tugas" style="margin-bottom: 15px;">
                    @forelse ($suratTugas->detailPelaksanaTugas as $detail)
                        @php
                            $personel = $detail->personable;
                            $isPegawai = $detail->personable_type === \App\Models\Pegawai::class;
                        @endphp
                        <table class="table table-borderless table-sm mb-3" style="width: 100%; font-size: 11pt; line-height: 1.6;">
                            <tbody>
                                <tr>
                                    <td style="width: 30%; padding: 2px 0;">Nama</td>
                                    <td style="width: 5%; padding: 2px 0;">:</td>
                                    <td style="padding: 2px 0;">{{ $personel->nama ?? '-' }}</td>
                                </tr>
                                @if ($isPegawai)
                                    <tr>
                                        <td style="padding: 2px 0;">NIP</td>
                                        <td style="padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0;">{{ $personel->nip ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Pangkat / Golongan</td>
                                        <td style="padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0;">{{ ($personel->pangkat ?? '-') . ' / ' . ($personel->golongan ?? '-') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Jabatan</td>
                                        <td style="padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0;">{{ $personel->jabatan ?? '-' }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td style="padding: 2px 0;">NIM</td>
                                        <td style="padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0;">{{ $personel->nim ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Jurusan</td>
                                        <td style="padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0;">{{ $personel->jurusan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Program Studi</td>
                                        <td style="padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0;">{{ $personel->prodi ?? '-' }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    @empty
                        <p class="text-muted fst-italic">(Tidak ada personel yang ditugaskan)</p>
                    @endforelse
                </div>

                <p style="margin-top: 20px; margin-bottom: 10px; text-align: justify;">
                    Untuk mengikuti kegiatan <span class="fw-bold">{{ $suratTugas->perihal_tugas }}</span>, diselenggarakan oleh <span class="fw-bold">{{ $suratTugas->nama_penyelenggara ?? $suratTugas->sumber_dana }}</span> pada:
                </p>
                
                <!-- Detail Kegiatan (menggunakan format Tabel) -->
                <table class="table table-borderless table-sm" style="width: 100%; font-size: 11pt; line-height: 1.6;">
                    <tbody>
                        <tr>
                            <td style="width: 30%; padding: 2px 0; vertical-align: top;">Hari / Tanggal</td>
                            <td style="width: 5%; padding: 2px 0; vertical-align: top;">:</td>
                            <td style="padding: 2px 0; vertical-align: top;">
                                @if ($suratTugas->tanggal_berangkat->isSameDay($suratTugas->tanggal_kembali))
                                    {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }}
                                @else
                                    {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }} → {{ $suratTugas->tanggal_kembali->translatedFormat('l, j F Y') }}
                                @endif
                            </td>
                        </tr>
        <tr>
          <td>Tempat</td>
          <td>:</td>
          <td>
          {{ $suratTugas->tempat_kegiatan }}<br>
          {!! nl2br(e($suratTugas->alamat_kegiatan)) !!}
          </td>
        </tr>
        </tbody>
      </table>

                <p style="margin-top: 20px; text-align: justify;">
                    Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
                </p>

                <!-- =========== FOOTER GRID (menggunakan tabel) =========== -->
                <table class="table table-borderless table-sm mt-5" style="width: 100%;">
                    <tr style="vertical-align: top;">
                        <!-- Kolom Tembusan -->
                        <td style="width: 50%; vertical-align: bottom; font-size: 10pt;">
                            <div>
                                <p class="fw mb-1">Tembusan:</p>
                                <ol style="padding-left: 20px; margin: 0;">
                                    @forelse ($suratSettings->tembusan_default ?? [] as $tembusan)
                                        <li style="margin-bottom: 2px;">{{ $tembusan }}</li>
                                    @empty
                                        <li>-</li>
                                    @endforelse
                                </ol>
                            </div>
                        </td>

                        <!-- Kolom Tanda Tangan Direktur -->
                        <td style="width: 50%; text-align: left; vertical-align: bottom; position: relative; font-size: 11pt;">
                            {{-- Tampilkan paraf Wadir sebagai bukti alur kerja --}}
                            @if($suratTugas->wadir_signature_data)
                                @php
                                    $wadirPosition = $suratTugas->wadir_signature_position ?? ['x' => -80, 'y' => -15, 'width' => 70, 'height' => 50];
                                @endphp
                                {{-- PERBAIKAN: Hapus kelas CSS, gunakan inline style saja --}}
                                <div style="position: absolute; left: {{ $wadirPosition['x'] }}px; top: {{ $wadirPosition['y'] }}px; width: {{ $wadirPosition['width'] }}px; height: {{ $wadirPosition['height'] }}px; z-index: 9;">
                                    <img src="{{ $suratTugas->wadir_signature_data }}" alt="Paraf Wadir" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                            @elseif($suratTugas->wadirApprover && $suratTugas->wadirApprover->para_file_path)
                                {{-- PERBAIKAN: Hapus kelas CSS, gunakan inline style saja --}}
                                <div style="position: absolute; left: -80px; top: -15px; width: 70px; height: 50px; z-index: 9;">
                                    <img src="{{ Storage::url($suratTugas->wadirApprover->para_file_path) }}" alt="Paraf Wadir" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                            @endif
                            
                            <p style="margin-bottom: 2px;">Bandung, {{ $suratTugas->tanggal_paraf_wadir ? $suratTugas->tanggal_paraf_wadir->translatedFormat('j F Y') : now()->translatedFormat('j F Y') }}</p>
                            <p style="margin-bottom: 2px;">Direktur,</p>
                            
                            <div style="height: 60px;"></div> {{-- Ruang kosong untuk TTD Direktur nanti --}}
                            
                            <p style="font-weight: bold; margin: 0;">{{ $suratSettings->nama_direktur ?? '' }}</p>
                            <p style="margin: 0;">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
                        </td>
                    </tr>
                </table>
              </div>
        </div>

        {{-- =================================================================== --}}
        {{--   BAGIAN 2: PANEL AKSI INTERAKTIF UNTUK SEKDIR                      --}}
        {{-- =================================================================== --}}
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
    <link rel="stylesheet" href="{{ asset('css/ttd_digital.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sekdir_content.css') }}">
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