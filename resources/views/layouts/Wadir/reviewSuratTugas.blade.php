{{-- resources/views/layouts/Wadir/reviewSuratTugas.blade.php --}}
@extends('layouts.Wadir.layout')

@section('title', 'Review Surat Tugas')
@section('wadir_content')

@php
  use Illuminate\Support\Facades\Storage;
@endphp
<div class="container-fluid">
  <h1 class="mb-4">Review Surat Tugas</h1>

  <div class="card shadow mb-4 p-0">
    <div class="document-container surat-tugas-body">
      <!-- =========== HEADER HALAMAN =========== -->
      <div class="surat-tugas-header">
        <img src="{{ asset('img/polban.png') }}" alt="POLBAN Logo" />
        <div class="surat-tugas-header-text">
          <h1>{{ $suratSettings->nama_kementerian ?? ''}}</h1>
          <h2>POLITEKNIK NEGERI BANDUNG</h2>
          <p>Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012, Kotak Pos 1234,</p>
          <p>Telepon: (022) 2013789, Faksimile: (022) 2013889</p>
          <p>Laman: <a href="https://www.polban.ac.id" target="_blank">www.polban.ac.id</a>,
            Pos Elektronik: polban@polban.ac.id</p>
        </div>
      </div>
      <hr class="surat-tugas-header-line" />

      <!-- =========== ISI UTAMA HALAMAN (DIPERBAIKI) =========== -->
      <div class="surat-tugas-content">

        {{-- Judul dan Nomor Surat --}}
        <div
          style="width: fit-content; margin-left: auto; margin-right: auto; transform: translateX(30px); text-align: left;">
          <h4 class="surattugas" style="text-decoration: underline; margin-bottom: 4px;">SURAT TUGAS</h4>
          <h4 class="nomor" style="margin-top: 0;">Nomor: </h4>
        </div>


        <p style="margin-bottom: 10px; text-align: justify;">
          Direktur memberi tugas kepada:
        </p>

        {{-- Daftar Personel --}}
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
          <td style="padding: 2px 0;">{{ ($personel->pangkat ?? '-') . ' / ' . ($personel->golongan ?? '-') }}
          </td>
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
          Untuk mengikuti kegiatan <span class="fw-bold">{{ $suratTugas->perihal_tugas }}</span>, diselenggarakan oleh
          <span class="fw-bold">{{ $suratTugas->nama_penyelenggara }}</span> pada:
        </p>

        <!-- Detail Kegiatan -->
        <table class="table table-borderless table-sm" style="width: 100%; font-size: 11pt; line-height: 1.6;">
          <tbody>
            <tr>
              <td style="width: 30%; padding: 2px 0; vertical-align: top;">Hari / Tanggal</td>
              <td style="width: 5%; padding: 2px 0; vertical-align: top;">:</td>
              <td style="padding: 2px 0; vertical-align: top;">
                @if ($suratTugas->tanggal_berangkat->isSameDay($suratTugas->tanggal_kembali))
          {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }}
        @else
          {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }} →
          {{ $suratTugas->tanggal_kembali->translatedFormat('l, j F Y') }}
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

        <!-- =========== FOOTER =========== -->
        <table class="table table-borderless table-sm mt-5" style="width: 100%;">
          <tr style="vertical-align: top;">
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
            <td style="width: 50%; text-align: left; vertical-align: top; font-size: 11pt;">
              <p style="margin-bottom: 2px;">Bandung, {{ \Carbon\Carbon::now()->translatedFormat('j F Y') }}</p>
              <p style="margin-bottom: 2px;">Direktur,</p>
              <div class="signature-area" style="position: relative; height: 80px;">
                @if($suratTugas->wadir_signature_data)
              @php
            $wadirPosition = $suratTugas->wadir_signature_position ?? ['x' => 0, 'y' => -40, 'width' => 70, 'height' => 50];
          @endphp
              <div
                style="position: absolute; left: {{ $wadirPosition['x'] }}px; top: {{ $wadirPosition['y'] }}px; width: {{ $wadirPosition['width'] }}px; height: {{ $wadirPosition['height'] }}px; z-index: 9;">
                <img src="{{ $suratTugas->wadir_signature_data }}" alt="Paraf Wadir"
                style="width: 100%; height: 100%; object-fit: contain;">
              </div>
        @elseif($suratTugas->wadirApprover && $suratTugas->wadirApprover->para_file_path)
          <div style="position: absolute; left: 0px; top: -40px; width: 70px; height: 50px; z-index: 9;">
            <img src="{{ Storage::url($suratTugas->wadirApprover->para_file_path) }}" alt="Paraf Wadir"
            style="width: 100%; height: 100%; object-fit: contain;">
          </div>
        @endif
              </div>
              <p style="font-weight: bold; margin: 0;">{{ $suratSettings->nama_direktur ?? '' }}</p>
              <p style="margin: 0;">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>


  {{-- Form Aksi Wadir --}}
  <div class="card-footer bg-light p-4">
    <h4>Catatan / Komentar (Jika Perlu Revisi/Ditolak)</h4>
    <form action="{{ route('wadir.process.review.surat_tugas', $suratTugas->surat_tugas_id) }}" method="POST"
      id="wadirReviewForm">
      @csrf
      {{-- Input tersembunyi untuk sumber dana yang diperbarui --}}
      <input type="hidden" name="updated_sumber_dana" id="updated_sumber_dana_input"
        value="{{ $suratTugas->sumber_dana }}">

      <div class="mb-3">
        <textarea name="catatan_revisi" class="form-control @error('catatan_revisi') is-invalid @enderror" rows="3"
          placeholder="Masukkan catatan atau alasan penolakan/revisi...">{{ old('catatan_revisi', $suratTugas->catatan_revisi) }}</textarea>
        @error('catatan_revisi')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button type="submit" name="action" value="revert" class="btn btn-warning">
          <i class="fas fa-redo"></i> Kembalikan untuk Revisi
        </button>
        <button type="submit" name="action" value="reject" class="btn btn-danger">
          <i class="fas fa-times-circle"></i> Tolak
        </button>
        <button type="button" class="btn btn-success" id="btnSetujui">
          <i class="fas fa-check-circle"></i> Setujui
        </button>
      </div>
    </form>
    <div class="mt-3">
      <a href="{{ route('wadir.dashboard') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke
        Dashboard</a>
    </div>
  </div>
</div>


{{-- MODAL 1: KONFIRMASI SUMBER DANA --}}
<div class="modal fade" id="modalKonfirmasiSumberDana" tabindex="-1" aria-labelledby="modalKonfirmasiSumberDanaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKonfirmasiSumberDanaLabel">Konfirmasi Persetujuan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah sumber dana sudah sesuai?</p>
        <p class="text-muted small">Sumber Dana saat ini: <strong id="currentSumberDanaText">{{ $suratTugas->sumber_dana }}</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnEditSumberDana">Edit Sumber Dana</button>
        <button type="button" class="btn btn-success" id="btnLanjutKonfirmasiAkhir">Ya</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL 2: EDIT SUMBER DANA --}}
<div class="modal fade" id="modalEditSumberDana" tabindex="-1" aria-labelledby="modalEditSumberDanaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditSumberDanaLabel">Pilih Sumber Dana</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="sumber_dana_baru" id="radioRM" value="RM">
          <label class="form-check-label" for="radioRM">RM</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="sumber_dana_baru" id="radioPNBP" value="PNBP">
          <label class="form-check-label" for="radioPNBP">PNBP</label>
        </div>
        {{-- Anda bisa menambahkan opsi lain jika diperlukan --}}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="btnSimpanDanKonfirmasi">Simpan & Setujui</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL 3: KONFIRMASI AKHIR --}}
<div class="modal fade" id="modalKonfirmasiAkhir" tabindex="-1" aria-labelledby="modalKonfirmasiAkhirLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKonfirmasiAkhirLabel">Konfirmasi Akhir</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Anda akan menyetujui surat tugas ini. Apakah Anda yakin ingin melanjutkan?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="btnSubmitPersetujuan">Ya, Setujui</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/paraf_digital.css') }}">
@endpush

@endsection

@push('scripts')
{{-- Skrip untuk mengontrol alur modal baru --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi semua modal
    const modalKonfirmasiSumberDana = new bootstrap.Modal(document.getElementById('modalKonfirmasiSumberDana'));
    const modalEditSumberDana = new bootstrap.Modal(document.getElementById('modalEditSumberDana'));
    const modalKonfirmasiAkhir = new bootstrap.Modal(document.getElementById('modalKonfirmasiAkhir'));

    // Elemen-elemen yang akan dimanipulasi
    const form = document.getElementById('wadirReviewForm');
    const updatedSumberDanaInput = document.getElementById('updated_sumber_dana_input');
    const currentSumberDanaText = document.getElementById('currentSumberDanaText');

    // Tombol-tombol pemicu
    const btnSetujui = document.getElementById('btnSetujui');
    const btnEditSumberDana = document.getElementById('btnEditSumberDana');
    const btnLanjutKonfirmasiAkhir = document.getElementById('btnLanjutKonfirmasiAkhir');
    const btnSimpanDanKonfirmasi = document.getElementById('btnSimpanDanKonfirmasi');
    const btnSubmitPersetujuan = document.getElementById('btnSubmitPersetujuan');

    // 1. Saat tombol "Setujui" utama diklik
    btnSetujui.addEventListener('click', function () {
        // Tampilkan modal pertama untuk konfirmasi sumber dana
        modalKonfirmasiSumberDana.show();
    });

    // 2. Saat tombol "Edit Sumber Dana" di modal pertama diklik
    btnEditSumberDana.addEventListener('click', function () {
        // Sembunyikan modal pertama dan tampilkan modal edit
        modalKonfirmasiSumberDana.hide();
        
        // Set radio button di modal edit sesuai dengan nilai saat ini dari input tersembunyi
        const currentSumberDana = updatedSumberDanaInput.value;
        const radioToCheck = document.querySelector(`input[name="sumber_dana_baru"][value="${currentSumberDana}"]`);
        if(radioToCheck) {
            radioToCheck.checked = true;
        }

        modalEditSumberDana.show();
    });

    // 3. Saat tombol "Ya" (Lanjut) di modal pertama diklik
    btnLanjutKonfirmasiAkhir.addEventListener('click', function() {
        modalKonfirmasiSumberDana.hide();
        modalKonfirmasiAkhir.show();
    });

    // 4. Saat tombol "Simpan & Setujui" di modal edit diklik
    btnSimpanDanKonfirmasi.addEventListener('click', function () {
        // Ambil nilai baru dari radio button
        const newSumberDana = document.querySelector('input[name="sumber_dana_baru"]:checked').value;
        
        // Perbarui nilai di input tersembunyi DAN teks di modal pertama
        updatedSumberDanaInput.value = newSumberDana;
        currentSumberDanaText.textContent = newSumberDana;

        // Sembunyikan modal edit dan tampilkan modal konfirmasi akhir
        modalEditSumberDana.hide();
        modalKonfirmasiAkhir.show();
    });

    // 5. Saat tombol "Ya, Setujui" di modal konfirmasi akhir diklik
    btnSubmitPersetujuan.addEventListener('click', function () {
        // Tambahkan input action 'approve' ke form dan submit
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'approve';
        form.appendChild(actionInput);

        // Nonaktifkan tombol untuk mencegah double-submit
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyetujui...';

        form.submit();
    });
});
</script>
@endpush