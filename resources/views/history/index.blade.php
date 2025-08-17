@extends('layouts.main')

@section('title', 'History Surat Tugas')

@section('sidebar')
    {{-- Blok sidebar dinamis tetap sama --}}
    @php
        $loginController = new \App\Http\Controllers\Auth\LoginController();
        $userRole = Auth::user()->role;
        $roleDisplayName = $loginController->getRoleDisplayName($userRole);
    @endphp
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
    <h1 class="pengusul-page-title mb-4">History Surat Tugas</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0">Riwayat Lengkap</h5>
            <form action="{{ route('history.index') }}" method="GET" class="d-flex" style="width: 300px;">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    {{-- =================================================================== --}}
                    {{-- <-- AWAL PERUBAHAN HEADER TABEL --> --}}
                    {{-- =================================================================== --}}
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Tanggal Pengusulan</th>
                        <th>Tanggal Berangkat</th>
                        <th>Nomor Surat Pengantar</th>
                        <th>Nomor Surat Tugas</th>
                        <th>Tanggal Diterbitkan</th>
                        <th>Diusulkan Kepada</th>
                        <th>Status</th>
                        <th style="width: 15%;">Aksi</th>
                    </tr>
                    {{-- =================================================================== --}}
                    {{-- <-- AKHIR PERUBAHAN HEADER TABEL --> --}}
                    {{-- =================================================================== --}}
                </thead>
                <tbody>
                    @forelse ($suratTugasList as $index => $surat)
                    <tr>
                        {{-- =================================================================== --}}
                        {{-- <-- AWAL PERUBAHAN ISI TABEL --> --}}
                        {{-- =================================================================== --}}
                        <td>{{ $loop->iteration + ($suratTugasList->currentPage() - 1) * $suratTugasList->perPage() }}</td>
                        <td>{{ $surat->created_at->format('d M Y') }}</td>
                        <td>{{ $surat->tanggal_berangkat->format('d M Y') }}</td>
                        <td>{{ $surat->nomor_surat_usulan_jurusan }}</td>
                        <td>{{ $surat->nomor_surat_tugas_resmi ?? '-' }}</td>
                        <td>{{ $surat->tanggal_persetujuan_direktur ? $surat->tanggal_persetujuan_direktur->format('d M Y') : '-' }}</td>
                        <td>{{ $surat->diusulkan_kepada }}</td>
                        <td>
                            @php
                                $badgeClass = 'bg-secondary';
                                switch ($surat->status_surat) {
                                    case 'draft': $badgeClass = 'bg-secondary'; break;
                                    case 'pending_wadir_review': $badgeClass = 'bg-warning text-dark'; break;
                                    case 'approved_by_wadir': $badgeClass = 'bg-info'; break;
                                    case 'rejected_by_wadir': $badgeClass = 'bg-danger'; break;
                                    case 'reverted_by_wadir': $badgeClass = 'bg-info text-dark'; break;
                                    case 'approved_by_direktur': $badgeClass = 'bg-success'; break;
                                    case 'rejected_by_direktur': $badgeClass = 'bg-danger'; break;
                                    case 'reverted_by_direktur': $badgeClass = 'bg-warning text-dark'; break;
                                    case 'diterbitkan': $badgeClass = 'bg-primary'; break;
                                    case 'laporan_selesai': $badgeClass = 'bg-success'; break;
                                    default: $badgeClass = 'bg-light text-dark'; break;
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', Str::title($surat->status_surat)) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('history.show', $surat->surat_tugas_id) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            {{-- Tampilkan tombol Download PDF jika statusnya sudah melewati tahap Sekdir --}}
                            @if (in_array($surat->status_surat, ['pending_direktur_review', 'diterbitkan']))
                                <a href="{{ route('history.download_pdf', $surat->surat_tugas_id) }}" class="btn btn-sm btn-success" title="Unduh PDF">
                                    <i class="fas fa-download"></i>
                                </a>
                            @endif
                        </td>
                        {{-- =================================================================== --}}
                        {{-- <-- AKHIR PERUBAHAN ISI TABEL --> --}}
                        {{-- =================================================================== --}}
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data riwayat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $suratTugasList->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengusul_content.css') }}">
@endpush