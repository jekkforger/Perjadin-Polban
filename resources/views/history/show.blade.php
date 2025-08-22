{{-- FILE: resources/views/history/show.blade.php (VERSI YANG DIPERBAIKI) --}}

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
<div class="pengusul-container px-4 py-3">
    <h1 class="pengusul-page-title mb-4">Detail Surat Tugas</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <a href="{{ route('history.index') }}" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Kembali ke History
        </a>

        {{-- =================================================================== --}}
        {{-- PRATINJAU SURAT UTAMA (MENGGUNAKAN STYLING YANG SAMA SEPERTI PREVIEW) --}}
        {{-- =================================================================== --}}
        <div class="paper-preview-wrapper">
            <div class="document-container surat-tugas-body">
                <!-- =========== HEADER =========== -->
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

                <!-- =========== ISI UTAMA HALAMAN =========== -->
                <div class="surat-tugas-content">
                    <div style="width: fit-content; margin-left: auto; margin-right: auto; transform: translateX(30px); text-align: left;">
                        <h4 class="surattugas" style="text-decoration: none; margin-bottom: 4px;">SURAT TUGAS</h4>
                        <h4 class="nomor" style="margin-top: 0;">Nomor: {{ $suratTugas->nomor_surat_tugas_resmi }}</h4>
                    </div>
                    
                    {{-- Panggil partial untuk menampilkan personel --}}
                    @include('layouts.pengusul.partials._personnel_display', ['suratTugas' => $suratTugas])

                    {{-- Tampilkan bagian detail kegiatan --}}
                    <p style="margin-top: 20px; margin-bottom: 10px;">
                        Untuk mengikuti kegiatan <strong>{{ $suratTugas->perihal_tugas }}</strong>, diselenggarakan oleh <strong>{{ $suratTugas->nama_penyelenggara }}</strong> pada:
                    </p>
                    <table class="table table-borderless table-sm" style="width: 100%;">
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

                    {{-- Footer Surat Utama --}}
                    <table class="table table-borderless table-sm mt-5" style="width: 100%;">
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
                                <p class="mb-1">Bandung, {{ $suratTugas->tanggal_persetujuan_direktur ? $suratTugas->tanggal_persetujuan_direktur->translatedFormat('j F Y') : now()->translatedFormat('j F Y') }}</p>
                                <p class="mb-1">Direktur,</p>
                                @if ($suratTugas->direktur_signature_data)
                                    <img src="{{ $suratTugas->direktur_signature_data }}" alt="Tanda Tangan Direktur" style="height: 60px; margin-bottom: 5px;">
                                @else
                                    <div style="height: 60px;"></div>
                                @endif
                                <p class="fw-bold mb-0" style="margin-top: -5px;">{{ $suratSettings->nama_direktur ?? '' }}</p>
                                <p class="mb-0">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
                            </td>
                        </tr>
                    </table>
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
                $itemsPerPage = 15;
                
                // Gabungkan semua personel untuk dipecah per halaman
                $allPersonnel = $pegawai->merge($mahasiswa);
                $allPersonnelChunks = $allPersonnel->chunk($itemsPerPage);
            @endphp
            
            @foreach ($allPersonnelChunks as $pageIndex => $chunk)
                <div class="paper-preview-wrapper" style="margin-top: 2rem;">
                    <div class="document-container surat-tugas-body" style="min-height: auto;">
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

                            {{-- Tampilkan tanda tangan HANYA di halaman lampiran terakhir --}}
                            @if ($loop->last)
                                <table class="table table-borderless table-sm mt-5">
                                    <tr valign="top">
                                        <td style="width: 50%;"></td>
                                        <td style="width: 50%; text-align: left; vertical-align: bottom;">
                                            <p class="mb-1">Bandung, {{ $suratTugas->tanggal_persetujuan_direktur ? $suratTugas->tanggal_persetujuan_direktur->translatedFormat('j F Y') : now()->translatedFormat('j F Y') }}</p>
                                            <p class="mb-1">Direktur,</p>
                                            @if ($suratTugas->direktur_signature_data)
                                                <img src="{{ $suratTugas->direktur_signature_data }}" alt="Tanda Tangan Direktur" style="height: 60px; margin-bottom: 5px;">
                                            @else
                                                <div style="height: 60px;"></div>
                                            @endif
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
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengusul_content.css') }}">
    <link rel="stylesheet" href="{{ asset('css/template-surat.css') }}">
    
    {{-- CSS khusus untuk memperbaiki tampilan history/show --}}
    <style>
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
    </style>
@endpush