{{-- resources/views/pengusul/partials/surat-tugas-preview.blade.php --}}

<div class="document-container surat-tugas-body form-step">
    <!-- =========== HEADER =========== -->
    <div class="surat-tugas-header">
        <img src="{{ asset('img/polban.png') }}" alt="POLBAN Logo" />
        <div class="surat-tugas-header-text">
            <h1>{{ $suratSettings->nama_kementerian ?? 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI' }}</h1>
            <h2>POLITEKNIK NEGERI BANDUNG</h2>
            <p>Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012, Kotak Pos 1234,</p>
            <p>Telepon: (022) 2013789, Faksimile: (022) 2013889</p>
            <p>Laman: <a href="https://www.polban.ac.id" target="_blank">www.polban.ac.id</a>,
                Pos Elektronik: polban@polban.ac.id</p>
        </div>
    </div>
    <hr class="surat-tugas-header-line" />

    <!-- =========== ISI UTAMA HALAMAN =========== -->
    <div class="surat-tugas-content">

      <div style="width: fit-content; margin-left: auto; margin-right: auto; transform: translateX(30px); text-align: left;">
        <h4 class="surattugas" style="text-decoration: margin-bottom: 4px;">SURAT TUGAS</h4>
        {{-- Nomor surat akan diisi dinamis oleh JS --}}
        <h4 class="nomor" style="margin-top: 0;">Nomor: <span id="nomor_surat_display"></span></h4>
      </div>

      <p style="margin-bottom: 10px;">
        Direktur memberi tugas kepada:
      </p>

      {{-- Ini adalah tempat daftar personel terpilih akan di-inject oleh JavaScript --}}
      <div class="personel-list" id="daftar_personel_surat_tugas" style="margin-bottom: 15px;">
        {{-- Konten diisi oleh JavaScript --}}
      </div>

      <p style="margin-top: 20px; margin-bottom: 10px;">
        Untuk mengikuti kegiatan <span class="fw-bold" id="nama_kegiatan_display_text"></span>, diselenggarakan oleh
        <span class="fw-bold" id="nama_penyelenggara_display"></span> pada:
      </p>

      <!-- Detail Kegiatan -->
      <table class="table table-borderless table-sm" style="width: 100%;">
        <tbody>
          <tr>
            <td style="width: 30%; vertical-align: top;">Hari / Tanggal</td>
            <td style="width: 5%; vertical-align: top;">:</td>
            <td id="tanggal_pelaksanaan_display"></td>
          </tr>
          <tr>
            <td style="vertical-align: top;">Tempat</td>
            <td style="vertical-align: top;">:</td>
            {{-- ID diubah menjadi satu untuk menampung gabungan data --}}
            <td id="tempat_dan_alamat_display"></td>
          </tr>
        </tbody>
      </table>

      <p style="margin-top: 20px;">
        Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
      </p>

      <!-- =========== FOOTER GRID (Tembusan + Signature) =========== -->
      <table class="table table-borderless table-sm mt-5" style="width: 100%;">
        <tr valign="top">
          <!-- Kolom Tembusan -->
          <td style="width: 50%; vertical-align: bottom;">
            <div>
              <p class="fw mb-1">Tembusan:</p>
              <ol class="mb-0">
                @forelse ($suratSettings->tembusan_default ?? [] as $tembusan)
                  <li>{{ $tembusan }}</li>
                @empty
                  <li>-</li>
                @endforelse
              </ol>
            </div>
          </td>

          <!-- Kolom Tanda Tangan Direktur -->
          <td style="width: 50%; text-align: center; vertical-align: bottom;">
            <p class="mb-1">Bandung, <span id="tanggal_surat_formatted_display"></span></p>
            <p class="mb-1">Direktur,</p>

            {{-- Placeholder untuk Tanda Tangan --}}
            <div style="height: 60px;"></div>

            {{-- Nama Direktur & NIP dari Database --}}
            <p class="fw mb-0" style="margin-top: -5px;">{{ $suratSettings->nama_direktur ?? '' }}</p>
            <p class="mb-0">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
          </td>
        </tr>
      </table>

    </div>
    <!-- =========== AKHIR ISI UTAMA =========== -->
</div>

<!-- Pindahkan Tombol Kembali dan Usulkan di luar surat-tugas-body -->
<div class="p-4 d-flex justify-content-between surat-tugas-actions">
    <button type="button" class="btn btn-secondary" id="back-to-form">Kembali</button>
    <button type="button" class="btn btn-primary" id="submit-surat">Usulkan</button>
</div>

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/template-surat.css') }}">
@endpush