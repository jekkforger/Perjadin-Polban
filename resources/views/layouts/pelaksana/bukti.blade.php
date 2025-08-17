@extends('layouts.pelaksana.layout')

@section('title', 'Laporan & Bukti Perjalanan Dinas')

@section('pelaksana_content')
<div class="pelaksana-container px-4 py-3">
    <h1 class="pelaksana-page-title mb-4">Laporan & Bukti Perjalanan Dinas</h1>

    {{-- ALERT SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- ALERT ERROR --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi Kesalahan:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="p-4 shadow-sm bg-white rounded">
        <h5 class="mb-3">Daftar Tugas Saya</h5>

        {{-- Search --}}
        <div class="d-flex justify-content-end mb-3">
            <form action="{{ route('pelaksana.bukti') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari Tugas..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kegiatan</th>
                        <th>Tanggal Pengusulan</th>
                        <th>Tanggal Pelaksanaan</th>
                        <th>Nomor Surat Tugas</th>
                        <th>Sumber Dana</th>
                        <th>Aksi</th>
                        <th>Tanggungan Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarTugas as $index => $tugas)
                    <tr>
                        <td>{{ $loop->iteration + ($daftarTugas->currentPage() - 1) * $daftarTugas->perPage() }}</td>
                        <td>{{ $tugas->perihal_tugas }}</td>
                        <td>{{ $tugas->created_at->format('d M Y') }}</td>
                        <td>
                            @if ($tugas->tanggal_berangkat->isSameDay($tugas->tanggal_kembali))
                                {{ $tugas->tanggal_berangkat->format('d M Y') }}
                            @else
                                {{ $tugas->tanggal_berangkat->format('d M Y') }} → {{ $tugas->tanggal_kembali->format('d M Y') }}
                            @endif
                        </td>
                        <td>{{ $tugas->nomor_surat_tugas_resmi ?? '-' }}</td>
                        <td>{{ $tugas->sumber_dana ?? '-' }}</td>
                        <td>
                            @php
                                // Menggunakan Carbon untuk mempermudah perbandingan tanggal
                                $today = \Carbon\Carbon::now()->startOfDay();
                                $tanggalKembali = \Carbon\Carbon::parse($tugas->tanggal_kembali)->startOfDay();
                                $batasAkhirUpload = $tanggalKembali->copy()->addDays(5)->endOfDay();

                                // Cek apakah hari ini SETELAH atau SAMA DENGAN tanggal kembali
                                $sudahWaktunyaUpload = $today->isAfter($tanggalKembali) || $today->isSameDay($tanggalKembali);
                                
                                // Cek apakah hari ini SEBELUM batas akhir upload
                                $belumTerlambat = $today->isBefore($batasAkhirUpload);

                                // Tombol aktif jika KEDUA kondisi terpenuhi
                                $bisaUpload = $sudahWaktunyaUpload && $belumTerlambat;
                                
                                $tooltipMessage = '';
                                if (!$sudahWaktunyaUpload) {
                                    $tooltipMessage = 'Anda baru bisa upload bukti setelah tanggal pelaksanaan selesai.';
                                } elseif (!$belumTerlambat) {
                                    $tooltipMessage = 'Batas waktu upload bukti (5 hari setelah selesai) telah terlewat.';
                                }
                            @endphp

                            {{-- Tombol Upload dengan logika disabled dan tooltip --}}
                            <span 
                                @if(!$bisaUpload) 
                                    class="d-inline-block" tabindex="0" 
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top"
                                    title="{{ $tooltipMessage }}"
                                @endif
                            >
                                <button type="button"
                                    class="btn btn-sm btn-success mb-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#uploadBuktiModal-{{ $tugas->surat_tugas_id }}"
                                    @if(!$bisaUpload) disabled @endif>
                                    <i class="fas fa-upload"></i> Upload Bukti
                                </button>
                            </span>

                            {{-- Tombol Lihat Bukti (selalu aktif) --}}
                            <a href="{{ route('pelaksana.lihatBukti', $tugas->surat_tugas_id) }}" class="btn btn-info btn-sm mb-1">
                                <i class="fas fa-eye"></i> Lihat Bukti
                            </a>
                        </td>
                        <td>{{ $tugas->tanggungan_biaya ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Anda belum memiliki penugasan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $daftarTugas->links() }}
        </div>

        {{-- Modal Upload Bukti --}}
        @foreach ($daftarTugas as $tugas)
        <div class="modal fade" id="uploadBuktiModal-{{ $tugas->surat_tugas_id }}" tabindex="-1" aria-labelledby="uploadBuktiLabel-{{ $tugas->surat_tugas_id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded shadow-sm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadBuktiLabel-{{ $tugas->surat_tugas_id }}">Upload Laporan & Bukti</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">

                        <p class="mb-3 text-muted">
                        Agar tidak terjadi kesalahan upload Laporan Perjalanan Dinas dan Surat Visum terlebih dahulu, lalu upload bukti seperti boarding pass, tiket taksi, hotel, dll.
                        </p>

                        <form action="{{ route('pelaksana.uploadLampiran') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- WAJIB --}}
                            <input type="hidden" name="surat_tugas_id" value="{{ $tugas->surat_tugas_id }}">

                            {{-- Jenis Dokumen --}}
                            <div class="mb-3">
                                <label class="form-label d-block mb-2">Jenis Dokumen</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jenis_dokumen" id="dokumen1-{{ $tugas->surat_tugas_id }}" value="Laporan Perjalanan Dinas" required>
                                    <label class="form-check-label" for="dokumen1-{{ $tugas->surat_tugas_id }}">Laporan Perjalanan Dinas</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jenis_dokumen" id="dokumen2-{{ $tugas->surat_tugas_id }}" value="Surat Visum">
                                    <label class="form-check-label" for="dokumen2-{{ $tugas->surat_tugas_id }}">Surat Visum</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jenis_dokumen" id="dokumen3-{{ $tugas->surat_tugas_id }}" value="Bukti Perjalanan Dinas">
                                    <label class="form-check-label" for="dokumen3-{{ $tugas->surat_tugas_id }}">Bukti Perjalanan Dinas</label>
                                </div>
                            </div>

                            {{-- FILE UPLOAD --}}
                            <div class="mb-3 text-center">
                                <label for="file-{{ $tugas->surat_tugas_id }}"
                                    class="d-block border border-dashed p-4 rounded"
                                    style="cursor: pointer;">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i><br>
                                    <span class="text-primary">Pilih file</span>
                                    <input type="file" name="file[]" multiple id="file-{{ $tugas->surat_tugas_id }}" class="d-none file-input" accept=".jpg,.jpeg,.png,.pdf" required>
                                </label>
                                <div id="preview-nama-file-{{ $tugas->surat_tugas_id }}" class="mt-2 text-muted"></div>
                                <small class="text-muted d-block mt-2">Format: JPG, PNG, PDF. Maks 2MB per file.</small>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pelaksana_content.css') }}">
@endpush

@push('scripts')
<script>
    // Inisialisasi semua tooltip di halaman ini
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Skrip untuk preview nama file
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function(e) {
                const files = e.target.files;
                const idPart = e.target.id.split('-')[1];
                const previewId = 'preview-nama-file-' + idPart;
                let fileList = '';

                if (files.length > 0) {
                    fileList = '<ul class="text-start">';
                    for (let i = 0; i < files.length; i++) {
                        fileList += `<li>${files[i].name}</li>`;
                    }
                    fileList += '</ul>';
                } else {
                    fileList = 'Tidak ada file dipilih';
                }
                document.getElementById(previewId).innerHTML = fileList;
            });
        });
    });
</script>
@endpush