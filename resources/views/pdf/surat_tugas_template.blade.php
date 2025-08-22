
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Tugas - {{ $suratTugas->nomor_surat_tugas_resmi }}</title>
    <style>
        @page { margin: 2.5cm 3cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.6; color: #000; }
        table { border-collapse: collapse; width: 100%; }
        .surat-tugas-header .logo-cell { width: 90px; vertical-align: top; }
        .surat-tugas-header img { width: 80px; }
        .surat-tugas-header .text-cell { text-align: center; }
        .surat-tugas-header-text h1 { font-size: 14pt; font-weight: bold; margin: 0; line-height: 1.1; text-transform: uppercase; }
        .surat-tugas-header-text h2 { font-size: 12pt; font-weight: bold; margin: 3px 0 0; line-height: 1.1; text-transform: uppercase; }
        .surat-tugas-header-text p { font-size: 9.5pt; margin: 1px 0; line-height: 1.4; }
        hr.header-line { border: 0; border-top: 1.5px solid #000; margin-top: 5px; margin-bottom: 20px; }
        .surat-tugas-title-wrapper h3 { font-size: 13pt; font-weight: bold; margin: 0; text-transform: uppercase; text-decoration: underline; }
        .surat-tugas-title-wrapper .nomor { font-size: 11pt; margin-top: 2px; }
        .detail-table td { padding: 1px 0; vertical-align: top; }
        .detail-label { width: 160px; }
        .detail-separator { width: 10px; }
        .footer-table { margin-top: 20px; }
        .footer-table .tembusan-cell { width: 50%; vertical-align: bottom; font-size: 10pt; }
        .footer-table .signature-cell { width: 50%; vertical-align: bottom; text-align: left; }
        .tembusan-list { padding-left: 20px; margin: 0; }
        .signature-block { position: relative; height: 60px; }
        .page-break { page-break-after: always; }
        .attachment-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11pt; }
        .attachment-table th, .attachment-table td { border: 1px solid black; padding: 5px; text-align: left; vertical-align: top; }
        .attachment-table th { background-color: #f2f2f2; text-align: center; }
    </style>
</head>
<body>
    {{-- =================================================================== --}}
    {{-- HALAMAN UTAMA SURAT TUGAS --}}
    {{-- =================================================================== --}}
    <div class="surat-tugas-header">
        <table>
            <tr>
                <td class="logo-cell"><img src="{{ public_path('img/polban.png') }}" alt="Logo"/></td>
                <td class="text-cell">
                    <div class="surat-tugas-header-text">
                        <h1>{{ $suratSettings->nama_kementerian ?? 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI' }}</h1>
                        <h2>POLITEKNIK NEGERI BANDUNG</h2>
                        <p>Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012, Kotak Pos 1234<br>
                        Telepon: (022) 2013789, Faksimile: (022) 2013889<br>
                        Laman: www.polban.ac.id, Pos Elektronik: polban@polban.ac.id</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <hr class="header-line" />
    <div class="surat-tugas-content">
        <div class="surat-tugas-title-wrapper" style="text-align: center;">
            <h3>SURAT TUGAS</h3>
            <p class="nomor">Nomor: {{ $suratTugas->nomor_surat_tugas_resmi }}</p>
        </div>

        @include('layouts.pengusul.partials._personnel_display', ['suratTugas' => $suratTugas])

        {{-- @if ($suratTugas->detailPelaksanaTugas->count() <= 5) --}}
            <p>Untuk mengikuti kegiatan <strong>{{ $suratTugas->perihal_tugas }}</strong>, diselenggarakan oleh <strong>{{ $suratTugas->nama_penyelenggara }}</strong> pada:</p>
            <table class="detail-table">
                <tr>
                    <td class="detail-label">Hari/tanggal</td><td class="detail-separator">:</td>
                    <td>
                        @if ($suratTugas->tanggal_berangkat->isSameDay($suratTugas->tanggal_kembali))
                            {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }}
                        @else
                            {{ $suratTugas->tanggal_berangkat->translatedFormat('l, j F Y') }} s.d. {{ $suratTugas->tanggal_kembali->translatedFormat('l, j F Y') }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="detail-label" style="vertical-align: top;">Tempat Kegiatan</td><td class="detail-separator" style="vertical-align: top;">:</td>
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
            </table>
        {{-- @endif --}}

        <p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>

        <table class="footer-table">
            <tr>
                <td class="tembusan-cell">
                    @php $tembusan_list = $suratSettings->tembusan_default ?? []; @endphp
                    @if(!empty($tembusan_list))
                    <p style="margin-bottom: 5px;">Tembusan:</p>
                    <ol class="tembusan-list">
                        @foreach ($tembusan_list as $tembusan)
                            <li>{{ $tembusan }}</li>
                        @endforeach
                    </ol>
                    @endif
                </td>
                <td class="signature-cell">
                    <p>Bandung, {{ \Carbon\Carbon::parse($suratTugas->tanggal_persetujuan_direktur)->translatedFormat('j F Y') }}</p>
                    <p style="margin-bottom: 2px;">Direktur,</p>

                    <div class="signature-block">
                        @if($suratTugas->direktur_signature_data)
                            @php $pos = $suratTugas->direktur_signature_position ?? ['x'=>0, 'y'=>-10, 'width'=>100, 'height'=>60]; @endphp
                            <img src="{{ $suratTugas->direktur_signature_data }}" style="position: absolute; left: {{ $pos['x'] }}px; top: {{ $pos['y'] }}px; width: {{ $pos['width'] }}px; height: {{ $pos['height'] }}px;">
                        @endif
                        @if($suratTugas->wadir_signature_data)
                            @php $wpos = $suratTugas->wadir_signature_position ?? ['x'=>-80, 'y'=>-15, 'width'=>70, 'height'=>50]; @endphp
                            <img src="{{ $suratTugas->wadir_signature_data }}" style="position: absolute; left: {{ $wpos['x'] }}px; top: {{ $wpos['y'] }}px; width: {{ $wpos['width'] }}px; height: {{ $wpos['height'] }}px;">
                        @elseif($suratTugas->wadirApprover && $suratTugas->wadirApprover->para_file_path && file_exists(storage_path('app/public/' . $suratTugas->wadirApprover->para_file_path)))
                            <img src="{{ storage_path('app/public/' . $suratTugas->wadirApprover->para_file_path) }}" style="position: absolute; left: -80px; top: -15px; width: 70px; height: 50px;">
                        @endif
                    </div>

                    <p style="margin: 0; font-weight: bold;">{{ $suratSettings->nama_direktur ?? '[Nama Direktur]' }}</p>
                    <p style="margin: 0;">NIP {{ $suratSettings->nip_direktur ?? '[NIP Direktur]' }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- =================================================================== --}}
    {{-- LOGIKA UNTUK MEMBUAT HALAMAN LAMPIRAN --}}
    {{-- =================================================================== --}}
    @if ($suratTugas->detailPelaksanaTugas->count() > 5)
        <div class="page-break"></div>

        @php
            $personnels = $suratTugas->detailPelaksanaTugas;
            $pegawai = $personnels->where('personable_type', \App\Models\Pegawai::class);
            $mahasiswa = $personnels->where('personable_type', \App\Models\Mahasiswa::class);
        @endphp

        <div class="surat-tugas-content">
            <p style="text-align: left; margin-top: 0; margin-bottom: 10px;">
                Lampiran: {{ $suratTugas->nomor_surat_tugas_resmi }}<br>
            </p>

            @if ($pegawai->isNotEmpty())
                <p>1. Pegawai</p>
                <table class="attachment-table">
                    <thead>
                        <tr><th>Nama</th><th>NIP</th><th>Pangkat</th><th>Golongan</th><th>Jabatan</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($pegawai as $detail)
                            <tr><td>{{ $detail->personable->nama }}</td><td>{{ $detail->personable->nip }}</td><td>{{ $detail->personable->pangkat }}</td><td>{{ $detail->personable->golongan }}</td><td>{{ $detail->personable->jabatan }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if ($mahasiswa->isNotEmpty())
                <p style="margin-top: 20px;">2. Mahasiswa</p>
                <table class="attachment-table">
                    <thead>
                        <tr><th>Nama</th><th>NIM</th><th>Jurusan</th><th>Prodi</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($mahasiswa as $detail)
                            <tr><td>{{ $detail->personable->nama }}</td><td>{{ $detail->personable->nim }}</td><td>{{ $detail->personable->jurusan }}</td><td>{{ $detail->personable->prodi }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <table class="footer-table" style="margin-top: 40px;">
                <tr>
                    <td style="width: 50%;"></td>
                    <td class="signature-cell">
                        <p>Bandung, {{ \Carbon\Carbon::parse($suratTugas->tanggal_persetujuan_direktur)->translatedFormat('j F Y') }}</p>
                        <p style="margin-bottom: 2px;">Direktur,</p>
                        <div class="signature-block">
                            @if($suratTugas->direktur_signature_data)
                                <img src="{{ $suratTugas->direktur_signature_data }}" style="position: absolute; left: 0; top: -10px; width: 100px; height: 60px;">
                            @endif
                        </div>
                        <p style="margin: 0; font-weight: bold;">{{ $suratSettings->nama_direktur ?? '' }}</p>
                        <p style="margin: 0;">NIP {{ $suratSettings->nip_direktur ?? '' }}</p>
                    </td>
                </tr>
            </table>
        </div>
    @endif
</body>
</html>


