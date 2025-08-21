@extends('layouts.pelaksana.layout')

@section('title', 'Dashboard Pelaksana')

@section('pelaksana_content')
<div class="dashboard-container px-4 py-3">
    <h1 class="dashboard-page-title mb-4">Dashboard</h1>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="p-4 shadow-sm bg-white rounded text-center dashboard-card">
                <p class="fw-semibold mb-1">Total Penugasan</p>
                <h5 class="fw-bold mb-2">{{ $totalPenugasan ?? 0 }}</h5>
                <i class="bi bi-file-earmark fs-4 text-primary"></i>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-4 shadow-sm bg-white rounded text-center dashboard-card">
                <p class="fw-semibold mb-1">Penugasan Baru</p>
                <h5 class="fw-bold mb-2">{{ $penugasanBaru ?? 0 }}</h5>
                <i class="bi bi-file-earmark-plus fs-4 text-success"></i>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-4 shadow-sm bg-white rounded text-center dashboard-card">
                <p class="fw-semibold mb-1">Laporan Belum Selesai</p>
                <h5 class="fw-bold mb-2">{{ $laporanBelumSelesai ?? 0 }}</h5>
                <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
            </div>
        </div>
    </div>

    <div class="card shadow p-4">
        <h5 class="mb-3">Daftar Tugas Saya</h5>

        {{-- Search Bar --}}
        <div class="d-flex justify-content-end mb-3">
            <form action="{{ route('pelaksana.dashboard') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari Tugas..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kegiatan</th>
                        <th>Tanggal Pelaksanaan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarTugas as $index => $tugas)
                    <tr>
                        <td>{{ $loop->iteration + ($daftarTugas->currentPage() - 1) * $daftarTugas->perPage() }}</td>
                        <td>{{ $tugas->perihal_tugas }}</td>
                        <td>
                            @if ($tugas->tanggal_berangkat->isSameDay($tugas->tanggal_kembali))
                                {{ $tugas->tanggal_berangkat->format('d M Y') }}
                            @else
                                {{ $tugas->tanggal_berangkat->format('d M Y') }} → {{ $tugas->tanggal_kembali->format('d M Y') }}
                            @endif
                        </td>
                        <td>
                            @if($tugas->laporanPerjalananDinas)
                                @php
                                    $status = trim($tugas->laporanPerjalananDinas->status_laporan);
                                    $statusLower = \Illuminate\Support\Str::lower($status);
                                    $pesan = $tugas->laporanPerjalananDinas->catatan_revisi;
                                @endphp

                                @if($statusLower === 'diterima')
                                    <span class="badge bg-success">Diterima BKU</span>
                                @elseif($statusLower === 'dikembalikan')
                                    <span class="badge bg-warning text-dark">Dikembalikan</span>

                                    @if($pesan)
                                        <!-- Tombol Lihat Pesan -->
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-1"
                                            data-bs-toggle="modal" data-bs-target="#pesanModal{{ $tugas->surat_tugas_id }}">
                                            Lihat Pesan
                                        </button>

                                        <!-- Modal Pesan -->
                                        <div class="modal fade" id="pesanModal{{ $tugas->surat_tugas_id }}" tabindex="-1" aria-labelledby="pesanModalLabel{{ $tugas->surat_tugas_id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="pesanModalLabel{{ $tugas->surat_tugas_id }}">Pesan BKU</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>{{ $pesan }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                @else
                                    <span class="badge bg-info text-dark">{{ $status }}</span>
                                @endif
                            @else
                                <span class="badge bg-danger">Belum Upload</span>
                            @endif
                        </td>
                        <td>
                            @if($tugas->status_surat == 'diterbitkan')
                            <!-- isi disini -->
                                <a href="{{ route('pelaksana.download_pdf', $tugas->surat_tugas_id) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-print"></i> Print Surat
                                </a>
                            @else
                                <a href="#" class="btn btn-sm btn-info">Detail</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Anda belum memiliki penugasan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $daftarTugas->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pelaksana.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endpush

@push('scripts')
    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
