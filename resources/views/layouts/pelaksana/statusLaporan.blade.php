@extends('layouts.pelaksana.layout')

@section('title', 'Status Laporan')

@section('pelaksana_content')
<div class="pelaksana-container px-4 py-3">
    <h1 class="pelaksana-page-title mb-4">Laporan Perjalanan Dinas</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <h5 class="mb-3">Daftar Tugas Saya</h5>

        {{-- Search Bar --}}
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
                        <th>Tanggungan Biaya</th>
                        <th>Status</th>
                        <th>Aksi</th>
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
                        <td>{{ $tugas->nomor_surat ?? '-' }}</td>
                        <td>{{ $tugas->sumber_dana ?? '-' }}</td>
                        <td>{{ $tugas->tanggungan_biaya ?? '-' }}</td>
                        <td>
                            @if($tugas->laporanPerjalananDinas)
                                @php
                                    $status = trim($tugas->laporanPerjalananDinas->status_laporan ?? '');
                                    $statusLower = \Illuminate\Support\Str::lower($status);
                                @endphp

                                @if($statusLower === 'diterima')
                                    <span class="badge bg-success">Diterima BKU</span>
                                @elseif($statusLower === 'dikembalikan')
                                    <span class="badge bg-warning text-dark">Dikembalikan</span>
                                @else
                                    <span class="badge bg-info text-dark">{{ $status }}</span>
                                @endif
                            @else
                                <span class="badge bg-danger">Belum Upload</span>
                            @endif
                        </td>
                        <td>
                            @if($tugas->laporanPerjalananDinas)
                                @php
                                    $status = trim($tugas->laporanPerjalananDinas->status_laporan ?? '');
                                    $statusLower = \Illuminate\Support\Str::lower($status);
                                    $pesan = $tugas->laporanPerjalananDinas->catatan_verifikasi_bku ?? '';
                                @endphp

                                @if($statusLower === 'dikembalikan' && $pesan)
                                    <!-- Tombol Lihat Pesan -->
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2"
                                        data-bs-toggle="modal" data-bs-target="#pesanModal{{ $tugas->surat_tugas_id }}">
                                        Lihat Pesan
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="pesanModal{{ $tugas->surat_tugas_id }}"
                                        tabindex="-1"
                                        aria-labelledby="pesanModalLabel{{ $tugas->surat_tugas_id }}"
                                        aria-hidden="true">
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
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Anda belum memiliki penugasan.</td>
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
    <link rel="stylesheet" href="{{ asset('css/pelaksana_content.css') }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
