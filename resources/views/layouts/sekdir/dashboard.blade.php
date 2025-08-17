@extends('layouts.sekdir.layout')

@section('title', 'Dashboard')
@section('sekdir_content')
<div class="dashboard-container px-4 py-3">
    <h1 class="dashboard-page-title mb-4">Dashboard</h1>

    {{-- Kartu Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('history.index') }}" class="text-decoration-none">
            <div class="p-4 shadow-sm bg-white rounded text-center dashboard-card">
                <p class="fw-semibold mb-1">Total Usulan</p>
                <h5 class="fw-bold mb-2">{{ $totalUsulan ?? 0 }}</h5>
                <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
            </div>
        </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('sekdir.nomorSurat') }}" class="text-decoration-none">
            <div class="p-4 shadow-sm bg-white rounded text-center dashboard-card">
                <p class="fw-semibold mb-1">Usulan Baru</p>
                <h5 class="fw-bold mb-2">{{ $usulanBaru ?? 0 }}</h5>
                <i class="bi bi-file-earmark-plus fs-4 text-warning"></i>
            </div>
        </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('history.index') }}" class="text-decoration-none">
            <div class="p-4 shadow-sm bg-white rounded text-center dashboard-card">
                <p class="fw-semibold mb-1">Bertugas</p>
                <h5 class="fw-bold mb-2">{{ $sedangBertugas ?? 0 }}</h5>
                <i class="bi bi-people fs-4 text-info"></i>
            </div>
        </div>
    </a>
    </div>

    {{-- Tabel Detail --}}
    <div class="p-4 shadow-sm bg-white rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0">Detail Pengajuan</h5>
            <form action="{{ route('sekdir.dashboard') }}" method="GET" class="d-flex" style="width: 300px;">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Tanggal Berangkat</th>
                        <th>Nomor Surat</th>
                        <th>Sumber Dana</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detailList as $index => $surat)
                    <tr>
                        <td>{{ $loop->iteration + ($detailList->currentPage() - 1) * $detailList->perPage() }}</td>
                        <td>{{ $surat->created_at->format('d M Y') }}</td>
                        <td>{{ $surat->tanggal_berangkat->format('d M Y') }}</td>
                        {{-- Tampilkan nomor resmi jika ada, jika tidak, tampilkan nomor usulan --}}
                        <td>{{ $surat->nomor_surat_tugas_resmi ?? $surat->nomor_surat_usulan_jurusan }}</td>
                        <td>{{ $surat->sumber_dana }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data untuk ditampilkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-end mt-3">
            {{ $detailList->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sekdir.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endpush