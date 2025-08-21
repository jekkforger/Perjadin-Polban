@extends('layouts.direktur.layout')

@section('title', 'Persetujuan')
@section('direktur_content')
<div class="direktur-container px-4 py-3">
    <h1 class="direktur-page-title mb-4">Persetujuan</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <div class="card-header py-3">
            <form action="{{ route('direktur.persetujuan') }}" method="GET" class="d-flex align-items-center" style="max-width:350px;">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari Pengajuan..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Search</button>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pengusul</th>
                            <th>Wadir yang Memaraf</th>
                            <th>Nomor Surat Resmi</th> {{-- Tambahkan kolom ini --}}
                            <th>Nama Kegiatan</th>
                            <th>Tanggal Pelaksanaan</th>
                            <th>Sumber Dana</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suratTugasUntukReview as $index => $st)
                        <tr>
                            <td>{{ $loop->iteration + ($suratTugasUntukReview->currentPage() - 1) * $suratTugasUntukReview->perPage() }}</td>
                            <td>{{ $st->pengusul->name ?? 'N/A' }}</td>
                            <td>{{ $st->wadirApprover->name ?? 'N/A' }} ({{ $st->diusulkan_kepada }})</td>
                            <td>{{ $st->nomor_surat_tugas_resmi ?? '-' }}</td> {{-- Tampilkan nomor resmi --}}
                            <td>{{ $st->perihal_tugas }}</td>
                            <td>
                                @if ($st->tanggal_berangkat->isSameDay($st->tanggal_kembali))
                                    {{ $st->tanggal_berangkat->format('d/m/Y') }}
                                @else
                                    {{ $st->tanggal_berangkat->format('d/m/Y') }} → {{ $st->tanggal_kembali->format('d/m/Y') }}
                                @endif
                            </td>
                            <td>{{ $st->sumber_dana }}</td>
                            <td>
                                <a href="{{ route('direktur.review.surat_tugas', $st->surat_tugas_id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada pengajuan yang menunggu persetujuan Anda.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-3">
                    {{ $suratTugasUntukReview->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/direktur_content.css') }}">
@endpush