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
                    <div class="document-container surat-tugas-body" style="min-height: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
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
                    <div
                      style="width: fit-content; margin-left: auto; margin-right: auto; transform: translateX(30px); text-align: left;">
                      <h4 class="surattugas" style="text-decoration: underline; margin-bottom: 4px;">SURAT TUGAS</h4>
                      <h4 class="nomor" style="margin-top: 0;">Nomor: </h4>
                    </div>

                    
                    @include('layouts.pengusul.partials._personnel_display', ['suratTugas' => $suratTugas])

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
                            <td style="width: 50%; text-align: left; vertical-align: bottom;">
                                <p class="mb-1">Bandung, {{ now()->translatedFormat('j F Y') }}</p>
                                <p class="mb-1">Direktur,</p>
                                <div style="height: 60px;"></div>
                                <p class="fw-bold mb-0" style="margin-top: -5px;">{{ $suratSettings->nama_direktur ?? '' }}</p>
                                <p class="mb-0">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
                            </td>
                        </tr>
                    </table>
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
                            <p style="text-align: left; margin-top: 0;">Lampiran: [Akan Diberikan Kemudian]</p>
                            
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
                                            <p class="mb-1">Bandung, {{ now()->translatedFormat('j F Y') }}</p>
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
    <link rel="stylesheet" href="{{ asset('css/wadir_content.css') }}"> 
    
    {{-- <link rel="stylesheet" href="{{ asset('css/pengusul_content.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/template-surat.css') }}"> --}}
    
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