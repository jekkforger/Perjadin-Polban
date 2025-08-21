{{-- FILE: resources/views/layouts/pengusul/show.blade.php --}}

@extends('layouts.pengusul.layout')

@section('title', 'Detail Surat Tugas')
@section('pengusul_content')
<div class="pengusul-container px-4 py-3">
    <h1 class="pengusul-page-title mb-4">Detail Surat Tugas</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <a href="{{ route('pengusul.history') }}" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Kembali ke History
        </a>

        {{-- Pratinjau Dokumen Surat Tugas --}}
        <div class="document-container surat-tugas-body">
            <!-- Header -->
            <div class="surat-tugas-header">
                <img src="{{ asset('img/polban.png') }}" alt="POLBAN Logo" />
                <div class="surat-tugas-header-text">
                    <h1>{{ $suratSettings->nama_kementerian ?? ''}}</h1>
                    <h2>POLITEKNIK NEGERI BANDUNG</h2>
                    <p>Jalan Gegerkalong Hilir, Desa Ciwaruga, Bandung 40012, Kotak Pos 1234,</p>
                    <p>Telepon: (022) 2013789, Faksimile: (022) 2013889</p>
                    <p>Laman: <a href="https://www.polban.ac.id">www.polban.ac.id</a>, Pos Elektronik: polban@polban.ac.id</p>
                </div>
            </div>
            <hr class="surat-tugas-header-line" />

            <!-- Isi Surat -->
            <div class="surat-tugas-content">
                <div style="text-align: center;">
                    <h4 class="surattugas" style="text-decoration: underline; margin-bottom: 4px;">SURAT TUGAS</h4>
                    <p class="nomor" style="margin-top: 0;">Nomor: {{ $suratTugas->nomor_surat_tugas_resmi ?? '[Belum Diterbitkan]' }}</p>
                </div>

                <p>Direktur memberi tugas kepada:</p>

                {{-- Detail Pelaksana --}}
                @foreach ($suratTugas->detailPelaksanaTugas as $detail)
                    @php $personel = $detail->personable; @endphp
                    <table class="table table-borderless table-sm mb-3" style="width: 100%;">
                        <tbody>
                            <tr>
                                <td style="width: 30%;">Nama</td>
                                <td style="width: 5%;">:</td>
                                <td>{{ $personel->nama ?? '-' }}</td>
                            </tr>
                            @if ($detail->personable_type === \App\Models\Pegawai::class)
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
                
                <p>Untuk mengikuti kegiatan <strong>{{ $suratTugas->perihal_tugas }}</strong>, diselenggarakan oleh <strong>{{ $suratTugas->nama_penyelenggara }}</strong> pada:</p>
                
                <table class="table table-borderless table-sm">
                    <tbody>
                        <tr>
                            <td style="width: 30%;">Hari/Tanggal</td>
                            <td style="width: 5%;">:</td>
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
                <p>Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengusul_content.css') }}">
    <link rel="stylesheet" href="{{ asset('css/template-surat.css') }}">
@endpush