@extends('layouts.pengusul.layout')

@section('title', 'Status')
@section('pengusul_content')
<div class="pengusul-container px-4 py-3">
    <h1 class="pengusul-page-title mb-4">Status</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <form action="{{ route('pengusul.status') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari..." value="{{ request('search') }}">
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
                            <th>Tanggal Pengusulan</th>
                            <th>Tanggal Berangkat</th>
                            <th>Nomor Surat Usulan</th>
                            <th>Sumber Dana</th>
                            <th>Surat Undangan</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($statusPengusulan as $index => $st)
                        <tr>
                            <td>{{ $loop->iteration + ($statusPengusulan->currentPage() - 1) * $statusPengusulan->perPage() }}</td>
                            <td>{{ $st->created_at->format('d M Y') }}</td>
                            <td>{{ $st->tanggal_berangkat->format('d M Y') }}</td>
                            {{-- PERBAIKAN DI SINI: ganti $item menjadi $st --}}
                            <td>{{ $st->nomor_surat_usulan_jurusan }}</td>
                            <td>{{ $st->sumber_dana }}</td>
                            <td class="text-center">
                                @if ($st->path_file_surat_usulan)
                                    <p style="margin-top: 20px;">
                                        <a href="{{ Storage::url($st->path_file_surat_usulan) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-file-alt"></i> Unduh Surat Undangan
                                        </a>
                                    </p>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = '';
                                    $showReasonButton = false;
                                    switch ($st->status_surat) {
                                        case 'draft': $badgeClass = 'bg-secondary'; break;
                                        case 'pending_wadir_review': $badgeClass = 'bg-warning text-dark'; break;
                                        case 'approved_by_wadir': $badgeClass = 'bg-info'; break;
                                        case 'rejected_by_wadir': $badgeClass = 'bg-danger'; $showReasonButton = true; break;
                                        case 'reverted_by_wadir': $badgeClass = 'bg-info text-dark'; $showReasonButton = true; break;
                                        case 'approved_by_direktur': $badgeClass = 'bg-success'; break;
                                        case 'rejected_by_direktur': $badgeClass = 'bg-danger'; $showReasonButton = true; break;
                                        case 'reverted_by_direktur': $badgeClass = 'bg-info text-dark'; $showReasonButton = true; break;
                                        case 'diterbitkan': $badgeClass = 'bg-primary'; break;
                                        case 'laporan_selesai': $badgeClass = 'bg-success'; break;
                                        default: $badgeClass = 'bg-light text-dark'; break;
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', Str::title($st->status_surat)) }}</span>
                                @if ($showReasonButton && $st->catatan_revisi)
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="showReason('{{ addslashes($st->catatan_revisi) }}')">Lihat Alasan</button>
                                @endif
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if ($st->status_surat == 'draft' || Str::contains($st->status_surat, 'reverted'))
                                    <a href="#" class="btn btn-sm btn-outline-warning ms-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada pengusulan dengan status ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-3">
                {{ $statusPengusulan->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function showReason(reason) {
    Swal.fire({
        title: 'Alasan Penolakan/Revisi',
        html: `<p>${reason}</p>`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
}
</script>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengusul_content.css') }}">
@endpush