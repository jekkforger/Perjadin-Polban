{{-- FILE: resources/views/history/show.blade.php (VERSI FINAL YANG SUDAH DIRAPIKAN) --}}

@extends('layouts.main')

@section('title', 'Detail Surat Tugas')

@section('sidebar')
    @php
        $loginController = new \App\Http\Controllers\Auth\LoginController();
        $userRole = Auth::user()->role;
        $roleDisplayName = $loginController->getRoleDisplayName($userRole);
    @endphp

    {{-- Logika untuk menampilkan sidebar yang sesuai dengan peran pengguna --}}
    @if (in_array($userRole, ['wadir_1', 'wadir_2', 'wadir_3', 'wadir_4']))
        @include('layouts.Wadir.partials.sidebar', ['userRole' => $userRole, 'roleDisplayName' => $roleDisplayName])
    @elseif ($userRole == 'pengusul')
        @include('layouts.pengusul.partials.sidebar', ['userRole' => $userRole, 'roleDisplayName' => $roleDisplayName])
    @elseif ($userRole == 'pelaksana')
        @include('layouts.pelaksana.partials.sidebar', ['userRole' => $userRole, 'roleDisplayName' => $roleDisplayName])
    @elseif ($userRole == 'direktur')
        @include('layouts.direktur.partials.sidebar', ['userRole' => $userRole, 'roleDisplayName' => $roleDisplayName])
    @elseif ($userRole == 'bku')
        @include('layouts.bku.partials.sidebar', ['userRole' => $userRole, 'roleDisplayName' => $roleDisplayName])
    @elseif ($userRole == 'sekdir')
        @include('layouts.sekdir.partials.sidebar', ['userRole' => $userRole, 'roleDisplayName' => $roleDisplayName])
    @endif
@endsection

@section('content')
{{-- =================================================================== --}}
{{-- <-- AWAL PERBAIKAN STRUKTUR --> --}}
{{-- =================================================================== --}}

{{-- 1. Tambahkan DIV PEMBUNGKUS UTAMA dengan class yang benar --}}
<div class="pengusul-container px-4 py-3">
    <h1 class="pengusul-page-title mb-4">Detail Surat Tugas</h1>

    {{-- 2. Letakkan SEMUA konten lainnya di dalam div ini --}}
    <div class="p-4 shadow-sm bg-white rounded">
        <a href="{{ route('history.index') }}" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>

        <div class="document-container surat-tugas-body">
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
                <h4 class="surattugas" style="text-decoration: underline; margin-bottom: 4px;">SURAT TUGAS</h4>
                <h4 class="nomor" style="margin-top: 0;">Nomor: {{ $suratTugas->nomor_surat_tugas_resmi ?? '[Belum Diterbitkan]' }}</h4>
              </div>

              <p style="margin-bottom: 10px;">
                Direktur memberi tugas kepada:
              </p>

              {{-- Daftar Personel dari Database --}}
              <div class="personel-list" style="margin-bottom: 15px;">
                  @foreach ($suratTugas->detailPelaksanaTugas as $detail)
                      @php
                          $personel = $detail->personable;
                          $isPegawai = $detail->personable_type === \App\Models\Pegawai::class;
                      @endphp
                      <table class="table table-borderless table-sm mb-3" style="width: 100%;">
                          <tbody>
                              <tr>
                                  <td style="width: 30%;">Nama</td>
                                  <td style="width: 5%;">:</td>
                                  <td>{{ $personel->nama ?? '-' }}</td>
                              </tr>
                              @if ($isPegawai)
                                  <tr><td>NIP</td><td>:</td><td>{{ $personel->nip ?? '-' }}</td></tr>
                                  <tr><td>Pangkat/Golongan</td><td>:</td><td>{{ ($personel->pangkat ?? '-') . ' / ' . ($personel->golongan ?? '-') }}</td></tr>
                                  <tr><td>Jabatan</td><td>:</td><td>{{ $personel->jabatan ?? '-' }}</td></tr>
                              @else
                                  <tr><td>NIM</td><td>:</td><td>{{ $personel->nim ?? '-' }}</td></tr>
                                  <tr><td>Jurusan</td><td>:</td><td>{{ $personel->jurusan ?? '-' }}</td></tr>
                              @endif
                          </tbody>
                      </table>
                  @endforeach
              </div>

              <p style="margin-top: 20px; margin-bottom: 10px;">
                Untuk mengikuti kegiatan <span class="fw-bold">{{ $suratTugas->perihal_tugas }}</span>, diselenggarakan oleh
                <span class="fw-bold">{{ $suratTugas->nama_penyelenggara }}</span> pada:
              </p>

              <!-- Detail Kegiatan dari Database -->
              <table class="table table-borderless table-sm" style="width: 100%;">
                <tbody>
                  <tr>
                    <td style="width: 30%; vertical-align: top;">Hari / Tanggal</td>
                    <td style="width: 5%; vertical-align: top;">:</td>
                    <td>
                        @if ($suratTugas->tanggal_berangkat->isSameDay($suratTugas->tanggal_kembali))
                            {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }}
                        @else
                            {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }} s.d. {{ $suratTugas->tanggal_kembali->translatedFormat('l, j F Y') }}
                        @endif
                    </td>
                  </tr>
                  <tr>
                    <td style="vertical-align: top;">Tempat</td>
                    <td style="vertical-align: top;">:</td>
                    <td>
                        {{ $suratTugas->tempat_kegiatan }}<br>
                        {!! nl2br(e($suratTugas->alamat_kegiatan)) !!}
                    </td>
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
                    <p class="mb-1">Bandung, {{ $suratTugas->tanggal_persetujuan_direktur ? $suratTugas->tanggal_persetujuan_direktur->translatedFormat('j F Y') : now()->translatedFormat('j F Y') }}</p>
                    <p class="mb-1">Direktur,</p>
                    
                    {{-- Tampilkan tanda tangan digital jika ada --}}
                    @if ($suratTugas->direktur_signature_data)
                        <img src="{{ $suratTugas->direktur_signature_data }}" alt="Tanda Tangan Direktur" style="height: 60px; margin-bottom: 5px;">
                    @else
                        <div style="height: 60px;"></div>
                    @endif

                    <p class="fw mb-0" style="margin-top: -5px;">{{ $suratSettings->nama_direktur ?? '' }}</p>
                    <p class="mb-0">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
                  </td>
                </tr>
              </table>

            </div>
        </div>
    </div>
</div>

{{-- =================================================================== --}}
{{-- <-- AKHIR PERBAIKAN STRUKTUR --> --}}
{{-- =================================================================== --}}
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengusul_content.css') }}">
    <link rel="stylesheet" href="{{ asset('css/template-surat.css') }}">
@endpush