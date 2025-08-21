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
    Untuk mengikuti kegiatan <span class="fw-bold">{{ $suratTugas->perihal_tugas }}</span>, diselenggarakan oleh <span class="fw-bold">{{ $suratTugas->nama_penyelenggara }}</span> pada:
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
    <form action="{{ route('wadir.process.review.surat_tugas', $suratTugas->surat_tugas_id) }}" method="POST">
      @csrf
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
        <button type="button" class="btn btn-success" id="btnSetujui"> {{-- Ubah type ke button --}}
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
</div>

{{-- MODAL KONFIRMASI SUMBER DANA --}}
<div class="modal fade" id="modalKonfirmasiSumberDana" tabindex="-1" aria-labelledby="modalKonfirmasiSumberDanaLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKonfirmasiSumberDanaLabel">Konfirmasi Persetujuan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah sumber dana sudah sesuai?</p>
        <p class="text-muted small">Sumber Dana saat ini: <strong>{{ $suratTugas->sumber_dana }}</strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnEditSumberDana">Edit Sumber Dana</button>
        <button type="button" class="btn btn-success" id="btnKonfirmasiSetuju">Ya</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL EDIT SUMBER DANA --}}
<div class="modal fade" id="modalEditSumberDana" tabindex="-1" aria-labelledby="modalEditSumberDanaLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditSumberDanaLabel">Pilih Sumber Dana</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="sumber_dana_baru" id="radioRM" value="RM" {{ $suratTugas->sumber_dana == 'RM' ? 'checked' : '' }}>
          <label class="form-check-label" for="radioRM">
            RM
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="sumber_dana_baru" id="radioPNBP" value="PNBP" {{ $suratTugas->sumber_dana == 'PNBP' ? 'checked' : '' }}>
          <label class="form-check-label" for="radioPNBP">
            PNBP
          </label>
        </div>
        {{-- Anda bisa tambahkan opsi lain jika ada, contoh: 'Polban', 'Penyelenggara', 'Polban dan Penyelenggara' --}}
        {{-- PENTING: Anda perlu logika untuk memetakan opsi lama ke RM/PNBP jika diperlukan --}}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
        <button type="button" class="btn btn-success" id="btnSimpanSumberDana">Paraf</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL SIGNATURE PAD --}}
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
                <small>Nomor:
                  {{ $suratTugas->nomor_surat_usulan_jurusan }}/PL12.C01/KP/{{ $suratTugas->created_at->format('Y') }}</small>
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
                <strong>Kegiatan:</strong> {{ $suratTugas->perihal_tugas }}<br>
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
                <div style="margin-top: 60px;">
                  <div>Maryani, S.E., M.Si., Ph.D.</div>
                  <div>NIP 196405041990032001</div>
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

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/paraf_digital.css') }}">
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
  <script src="{{ asset('js/paraf_digital.js') }}"></script>
@endpush