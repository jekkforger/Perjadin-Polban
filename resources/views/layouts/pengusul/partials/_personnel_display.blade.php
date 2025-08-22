@php
    $personnels = $suratTugas->detailPelaksanaTugas;
    $personnelCount = $personnels->count();
@endphp

@if ($personnelCount > 0 && $personnelCount <= 1)
    {{-- TAMPILAN UNTUK 1 ORANG (LAYOUT VERTIKAL) --}}
    @foreach ($personnels as $detail)
        @php
            $personel = $detail->personable;
            $isPegawai = $detail->personable_type === \App\Models\Pegawai::class;
        @endphp
        <table class="table table-borderless table-sm mb-3" style="width: 100%;">
            <tbody>
                <tr><td style="width: 30%;">Nama</td><td style="width: 5%;">:</td><td>{{ $personel->nama ?? '-' }}</td></tr>
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

@elseif ($personnelCount > 1 && $personnelCount <= 5)
    {{-- =================================================================== --}}
    {{-- <-- AWAL BLOK KODE YANG DIPERBAIKI UNTUK KASUS CAMPURAN --> --}}
    {{-- =================================================================== --}}
    @php
        $pegawai = $personnels->where('personable_type', \App\Models\Pegawai::class);
        $mahasiswa = $personnels->where('personable_type', \App\Models\Mahasiswa::class);
    @endphp

    {{-- Tampilkan tabel Pegawai jika ada --}}
    @if ($pegawai->isNotEmpty())
        <h6>Pegawai yang Ditugaskan:</h6>
        <table class="table table-bordered table-sm" style="font-size: 11pt;">
            <thead class="text-center">
                <tr><th>Nama</th><th>NIP</th><th>Pangkat</th><th>Golongan</th><th>Jabatan</th></tr>
            </thead>
            <tbody>
                @foreach ($pegawai as $detail)
                    <tr>
                        <td>{{ $detail->personable->nama ?? '-' }}</td>
                        <td>{{ $detail->personable->nip ?? '-' }}</td>
                        <td>{{ $detail->personable->pangkat ?? '-' }}</td>
                        <td>{{ $detail->personable->golongan ?? '-' }}</td>
                        <td>{{ $detail->personable->jabatan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    {{-- Tampilkan tabel Mahasiswa jika ada --}}
    @if ($mahasiswa->isNotEmpty())
        <h6 style="margin-top: 15px;">Mahasiswa yang Ditugaskan:</h6>
        <table class="table table-bordered table-sm" style="font-size: 11pt;">
            <thead class="text-center">
                <tr><th>Nama</th><th>NIM</th><th>Jurusan</th><th>Prodi</th></tr>
            </thead>
            <tbody>
                @foreach ($mahasiswa as $detail)
                    <tr>
                        <td>{{ $detail->personable->nama ?? '-' }}</td>
                        <td>{{ $detail->personable->nim ?? '-' }}</td>
                        <td>{{ $detail->personable->jurusan ?? '-' }}</td>
                        <td>{{ $detail->personable->prodi ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    {{-- =================================================================== --}}
    {{-- <-- AKHIR BLOK KODE YANG DIPERBAIKI --> --}}
    {{-- =================================================================== --}}

@elseif ($personnelCount > 5)
    {{-- TAMPILAN UNTUK LEBIH DARI 5 ORANG (TERLAMPIR) --}}
    <p style="text-align: justify;">
        Direktur Politeknik Negeri Bandung menugaskan kepada yang namanya tercantum di dalam lampiran pada surat tugas ini.
    </p>
@else
    <p class="text-muted">(Tidak ada personel yang ditugaskan)</p>
@endif