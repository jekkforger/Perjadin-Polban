@extends('layouts.direktur.layout')

@section('title', 'Review Surat Tugas')
@section('direktur_content')

<div class="direktur-container px-4 py-3">
    <h1 class="direktur-page-title mb-4">Review Surat Tugas</h1>

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
                        <h4 class="nomor" style="margin-top: 0;">Nomor: {{ $suratTugas->nomor_surat_tugas_resmi ?? '[Nomor Belum Diberikan]' }}</h4>
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
                                    <div style="position: absolute; left: {{ $wadirPosition['x'] }}px; top: {{ $wadirPosition['y'] }}px; width: {{ $wadirPosition['width'] }}px; height: {{ $wadirPosition['height'] }}px; z-index: 5;">
                                        <img src="{{ $suratTugas->wadir_signature_data }}" alt="Paraf Wadir" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                @elseif($suratTugas->wadirApprover && $suratTugas->wadirApprover->para_file_path)
                                    <div style="position: absolute; left: -80px; top: -15px; width: 70px; height: 50px; z-index: 5;">
                                        <img src="{{ Storage::url($suratTugas->wadirApprover->para_file_path) }}" alt="Paraf Wadir" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                @endif

                                <p class="mb-1">Bandung, {{ $suratTugas->tanggal_persetujuan_direktur ? $suratTugas->tanggal_persetujuan_direktur->translatedFormat('j F Y') : ($suratTugas->tanggal_penomoran_sekdir ? \Carbon\Carbon::parse($suratTugas->tanggal_penomoran_sekdir)->translatedFormat('j F Y') : now()->translatedFormat('j F Y')) }}</p>
                                <p class="mb-1">Direktur,</p>
                                
                                <div style="position: relative; height: 80px; width: 100%;">
                                    @if($suratTugas->direktur_signature_data)
                                        @php $position = $suratTugas->direktur_signature_position ?? ['x' => 0, 'y' => -10, 'width' => 100, 'height' => 60]; @endphp
                                        <div style="position: absolute; left: 50%; transform: translateX(-50%); top: {{ $position['y'] }}px; width: {{ $position['width'] }}px; height: {{ $position['height'] }}px;">
                                            <img src="{{ $suratTugas->direktur_signature_data }}" alt="Tanda Tangan Direktur" style="width: 100%; height: 100%; object-fit: contain;">
                                        </div>
                                    @endif
                                </div>
                                
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
                            <p style="text-align: left; margin-top: 0;">Lampiran: {{ $suratTugas->nomor_surat_tugas_resmi }}</p>
                            
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
                                <p">2. Mahasiswa</p>
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
                                            <p class="mb-1">Bandung, {{ $suratTugas->tanggal_persetujuan_direktur ? $suratTugas->tanggal_persetujuan_direktur->translatedFormat('j F Y') : now()->translatedFormat('j F Y') }}</p>
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

    {{-- Panel Aksi Direktur (Logika Baru) --}}
      <div class="card-footer bg-light p-4">
      {{-- Status Persetujuan Wadir & Sekdir (selalu tampil jika ada) --}}
      @if($suratTugas->wadir_signature_data || ($suratTugas->wadirApprover && $suratTugas->wadirApprover->para_file_path))
      <div class="alert alert-success mb-3">
        <i class="fas fa-check-circle"></i>
        <strong>Sudah Disetujui oleh Wadir:</strong>
        {{ $suratTugas->wadirApprover ? $suratTugas->wadirApprover->name : 'Wakil Direktur' }}
        @if($suratTugas->tanggal_paraf_wadir)
          pada {{ $suratTugas->tanggal_paraf_wadir->translatedFormat('j F Y \p\u\k\u\l H:i') }}
        @endif
        @if($suratTugas->wadir_signature_data)
          <br><small class="text-muted"><i class="fas fa-signature"></i> Menggunakan tanda tangan digital</small>
        @endif
      </div>
      @endif
      
      @if($suratTugas->nomor_surat_tugas_resmi && $suratTugas->sekdir_processor_id)
      <div class="alert alert-info mb-3">
        <i class="fas fa-check-circle"></i>
        <strong>Sudah Diberi Nomor Resmi oleh Sekdir:</strong>
        {{ $suratTugas->nomor_surat_tugas_resmi }}
        @if($suratTugas->tanggal_penomoran_sekdir)
          pada {{ \Carbon\Carbon::parse($suratTugas->tanggal_penomoran_sekdir)->translatedFormat('j F Y \p\u\k\u\l H:i') }}
        @endif
      </div>
      @endif

      {{-- KONDISI DIMULAI DI SINI --}}
      @if($suratTugas->status_surat != 'diterbitkan')
        {{-- JIKA BELUM DITERBITKAN, TAMPILKAN FORM AKSI --}}
        <h4>Keputusan Direktur</h4>
        <form action="{{ route('direktur.process.review.surat_tugas', $suratTugas->surat_tugas_id) }}" method="POST" id="direkturReviewForm">
          @csrf
          <div class="mb-3">
            <label for="catatan_revisi_direktur" class="form-label">Catatan / Komentar (Jika Perlu Revisi/Ditolak)</label>
            <textarea name="catatan_revisi" class="form-control @error('catatan_revisi') is-invalid @enderror" rows="3" placeholder="Masukkan catatan atau alasan penolakan/revisi...">{{ old('catatan_revisi', $suratTugas->catatan_revisi) }}</textarea>
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
            <button type="button" class="btn btn-success" id="btnApproveAndPublish">
              <i class="fas fa-check-circle"></i> Setujui & Terbitkan
            </button>
          </div>
        </form>

      @else
        {{-- JIKA SUDAH DITERBITKAN, TAMPILKAN PESAN SUKSES --}}
        <div class="alert alert-success text-center">
            <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Surat Diterbitkan</h4>
            <p class="mb-0">Surat tugas ini telah disetujui dan diterbitkan pada
                @if($suratTugas->tanggal_persetujuan_direktur)
                    <strong>{{ $suratTugas->tanggal_persetujuan_direktur->translatedFormat('j F Y \p\u\k\u\l H:i') }}.</strong>
                @else
                    tanggal yang tercatat.
                @endif
            </p>
            <p>Tidak ada tindakan lebih lanjut yang diperlukan.</p>
        </div>
      @endif
      {{-- AKHIR DARI KONDISI --}}

      {{-- Tombol "Kembali" selalu tampil --}}
      <div class="mt-3">
        <a href="{{ route('direktur.persetujuan') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Persetujuan</a>
      </div>
    </div>
    </div>
  </div>

  {{-- ======================================================= --}}
  {{--        AWAL DARI BLOK MODAL YANG HILANG                 --}}
  {{-- ======================================================= --}}
  {{-- MODAL SIGNATURE PAD (YANG HILANG) --}}
  <div class="modal fade" id="modalSignaturePad" tabindex="-1" aria-labelledby="modalSignaturePadLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
      <h5 class="modal-title" id="modalSignaturePadLabel">Tanda Tangan Digital</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="text-center mb-3">
        <p>Silakan buat tanda tangan digital Anda di area di bawah ini:</p>
      </div>
      <div class="signature-container" style="border: 2px solid #ccc; border-radius: 5px; background-color: #fff;">
        <canvas id="signature-pad" width="600" height="300" style="display: block; margin: 0 auto;"></canvas>
      </div>
      <div class="text-center mt-3">
        <button type="button" class="btn btn-outline-danger" id="clearSignature">
        <i class="fas fa-eraser"></i> Hapus
        </button>
      </div>
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button type="button" class="btn btn-success" id="saveSignature">
        <i class="fas fa-check"></i> Lanjut ke Posisi
      </button>
      </div>
    </div>
    </div>
  </div>
  {{-- ======================================================= --}}
  {{--          AKHIR DARI BLOK MODAL YANG HILANG              --}}
  {{-- ======================================================= --}}


  {{-- MODAL SIGNATURE POSITION --}}
  <div class="modal fade" id="modalSignaturePosition" tabindex="-1" aria-labelledby="modalSignaturePositionLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
      <h5 class="modal-title" id="modalSignaturePositionLabel">Atur Posisi Tanda Tangan</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
        <div class="col-md-8">
        <p class="text-muted">Seret tanda tangan ke posisi yang diinginkan pada dokumen:</p>
        <div id="documentPreview" style="
          border: 2px solid #ddd; 
          border-radius: 5px; 
          background: white; 
          position: relative; 
          min-height: 400px; 
          overflow: hidden;
          font-family: 'Times New Roman', serif;
          font-size: 11pt;
          padding: 20px;
        ">
          <!-- Mini preview dokumen -->
          <div style="text-align: center; margin-bottom: 15px;">
          <strong>SURAT TUGAS</strong><br>
          <small>Nomor: {{ $suratTugas->nomor_surat_tugas_resmi ?? '[Nomor Belum Diberikan]' }}</small>
          </div>

          <div style="margin-bottom: 20px;">
          <strong>Direktur memberi tugas kepada:</strong><br>
          @forelse ($suratTugas->detailPelaksanaTugas->take(2) as $detail)
            {{ $detail->personable->nama ?? '-' }}<br>
          @empty
            -
          @endforelse
          @if($suratTugas->detailPelaksanaTugas->count() > 2)
            <small>... dan {{ $suratTugas->detailPelaksanaTugas->count() - 2 }} lainnya</small>
          @endif
          </div>

          <div style="margin-bottom: 20px;">
          <strong>Tanggal:</strong> {{ $suratTugas->tanggal_berangkat->translatedFormat('j F Y') }} →
          {{ $suratTugas->tanggal_kembali->translatedFormat('j F Y') }}<br>
          <strong>Tempat:</strong> {{ $suratTugas->tempat_kegiatan }}
          </div>

          <!-- Area tanda tangan -->
          <div style="
          position: absolute; 
          bottom: 40px; 
          right: 40px; 
          text-align: left;
          min-width: 200px;
          ">
          <div style="margin-bottom: 5px;">{{ Carbon\Carbon::now()->translatedFormat('j F Y') }}</div>
          <div style="margin-bottom: 5px;">Direktur,</div>

          {{-- Wadir Signature Preview in Modal --}}
          @if($suratTugas->wadir_signature_data)
          @php
            $wadirPosition = $suratTugas->wadir_signature_position ?? ['x' => -80, 'y' => -15, 'width' => 70, 'height' => 50];
          @endphp
          <div style="position: absolute; 
            left: {{ $wadirPosition['x'] }}px; 
            top: {{ $wadirPosition['y'] + 15 }}px; 
            width: {{ $wadirPosition['width'] }}px; 
            height: {{ $wadirPosition['height'] }}px; 
            z-index: 5;
            opacity: 0.8;">
          <img src="{{ $suratTugas->wadir_signature_data }}" alt="Signature Wadir"
          style="width: 100%; height: 100%; object-fit: contain;">
          </div>
          @elseif($suratTugas->wadirApprover && $suratTugas->wadirApprover->para_file_path)
            <div style="position: absolute; 
              left: -80px; 
              top: 0px; 
              width: 70px; 
              height: 50px; 
              z-index: 5;
              opacity: 0.8;">
            <img src="{{ Storage::url($suratTugas->wadirApprover->para_file_path) }}" alt="Paraf Wadir"
            style="width: 100%; height: 100%; object-fit: contain;">
            </div>
          @endif

          <div style="margin-top: 60px;">
            <div>{{ $suratSettings->nama_direktur ?? '' }}</div>
            <div>NIP {{ $suratSettings->nip_direktur ?? '' }}</div>
          </div>
          </div>

          <!-- Draggable signature -->
          <div id="draggableSignature" style="
          position: absolute;
          top: 300px;
          right: 120px;
          width: 80px;
          height: 60px;
          border: 2px dashed #007bff;
          cursor: move;
          display: flex;
          align-items: center;
          justify-content: center;
          background: rgba(0, 123, 255, 0.1);
          border-radius: 4px;
          z-index: 10;
          ">
          <img id="signaturePreview" style="max-width: 100%; max-height: 100%; object-fit: contain;"
            alt="Signature Preview">
          </div>
        </div>
        </div>
        <div class="col-md-4">
        <h6>Kontrol Posisi</h6>
        <div class="mb-3">
          <label for="positionX" class="form-label">Posisi X (px)</label>
          <input type="range" class="form-range" id="positionX" min="-200" max="200" value="0">
          <small class="form-text text-muted" id="positionXValue">0px</small>
        </div>
        <div class="mb-3">
          <label for="positionY" class="form-label">Posisi Y (px)</label>
          <input type="range" class="form-range" id="positionY" min="-100" max="100" value="-15">
          <small class="form-text text-muted" id="positionYValue">-15px</small>
        </div>
        <div class="mb-3">
          <label for="signatureWidth" class="form-label">Lebar (px)</label>
          <input type="range" class="form-range" id="signatureWidth" min="40" max="150" value="80">
          <small class="form-text text-muted" id="signatureWidthValue">80px</small>
        </div>
        <div class="mb-3">
          <label for="signatureHeight" class="form-label">Tinggi (px)</label>
          <input type="range" class="form-range" id="signatureHeight" min="30" max="120" value="60">
          <small class="form-text text-muted" id="signatureHeightValue">60px</small>
        </div>
        <div class="mb-3">
          <button type="button" class="btn btn-outline-primary btn-sm" id="resetPosition">
          <i class="fas fa-undo"></i> Reset Posisi
          </button>
        </div>
        </div>
      </div>
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="backToSignature">
        <i class="fas fa-arrow-left"></i> Kembali ke Tanda Tangan
      </button>
      <button type="button" class="btn btn-success" id="finalSaveSignature">
        <i class="fas fa-check"></i> Simpan & Setujui
      </button>
      </div>
    </div>
    </div>
  </div>
  </div>
@endsection

@push('styles')
  {{-- <link rel="stylesheet" href="{{ asset('css/ttd_digital.css') }}"> --}}
  <link rel="stylesheet" href="{{ asset('css/direktur_content.css') }}">
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