@extends('layouts.direktur.layout')

@section('title', 'Dashboard')
@section('direktur_content')
<div class="dashboard-container px-4 py-3">
    <h1 class="dashboard-page-title mb-4">Dashboard</h1>

    {{-- Kotak Info Dashboard --}}
    <div class="row g-3 mb-4">
        {{-- Total Usulan -> mengarah ke History --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('history.index') }}" class="text-decoration-none">
                <div class="p-4 shadow-sm bg-white rounded text-center dashboard-card">
                    <p class="fw-semibold mb-1">Total Usulan</p>
                    <h5 class="fw-bold mb-2">{{ $dashboardStats['total_pengusulan'] }}</h5>
                    <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                </div>
            </a>
        </div>
        
        {{-- Bertugas -> mengarah ke History --}}
        <div class="col-6 col-md-3">
            <a href="{{ route('history.index') }}" class="text-decoration-none">
                <div class="p-4 shadow-sm bg-white rounded text-center dashboard-card">
                    <p class="fw-semibold mb-1">Bertugas</p>
                    <h5 class="fw-bold mb-2">{{ $dashboardStats['bertugas'] }}</h5>
                    <i class="bi bi-people fs-4 text-info"></i>
                </div>
            </a>
        </div>
    </div>
    {{-- Tabel Detail Pengusulan --}}
    <div class="p-4 shadow-sm bg-white rounded text-left">
        <div class="card-header py-3 d-flex justify-content-between align-items-center table-header-flex">
            <h6 class="m-0 font-weight-bold text-gray-800 table-title">Detail</h6>
            <form action="{{ route('direktur.dashboard') }}" method="GET" class="table-search-box input-group w-auto">
                <input type="text" name="search" class="form-control form-control-sm custom-search-input" placeholder="Search..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm custom-search-button"><i class="fas fa-search"></i></button>
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered dashboard-data-table" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tanggal Berangkat</th>
                            <th>Nomor Surat</th>
                            <th>Sumber Dana</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengusulanDetails as $detail)
                        <tr>
                            <td>{{ $detail->tanggal_berangkat->format('d M Y') }}</td>
                            <td>{{ $detail->nomor_surat_usulan_jurusan }}</td> {{-- Contoh: gunakan nomor surat usulan --}}
                            <td>{{ $detail->sumber_dana }}</td>
                            <td>
                                @php
                                    $badgeClass = '';
                                    switch ($detail->status_surat) {
                                        case 'approved_by_wadir': $badgeClass = 'bg-info'; break;
                                        case 'rejected_by_direktur': $badgeClass = 'bg-danger'; break;
                                        case 'reverted_by_direktur': $badgeClass = 'bg-warning text-dark'; break;
                                        case 'diterbitkan': $badgeClass = 'bg-success'; break;
                                        default: $badgeClass = 'bg-secondary'; break;
                                    }
                                @endphp
                                <span class="badge status-badge {{ $badgeClass }}">{{ str_replace('_', ' ', Str::title($detail->status_surat)) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('direktur.review.surat_tugas', $detail->surat_tugas_id) }}" class="btn btn-info btn-sm custom-view-button">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data pengusulan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Bagian pagination/rows per page --}}
            <div class="d-flex justify-content-between align-items-center mt-3 table-pagination-controls">
                <div>
                    Rows per page: 
                    <form action="{{ route('direktur.dashboard') }}" method="GET" id="direkturPerPageForm" class="d-inline-block">
                        <select name="per_page" class="form-select form-select-sm d-inline-block w-auto custom-pagination-select" onchange="this.form.submit()">
                            @foreach([10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    </form>
                </div>
                <div>
                    <span class="me-2">Showing {{ $pengusulanDetails->firstItem() ?? 0 }} - {{ $pengusulanDetails->lastItem() ?? 0 }} of {{ $pengusulanDetails->total() }}</span>
                    {{ $pengusulanDetails->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/direktur.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endpush