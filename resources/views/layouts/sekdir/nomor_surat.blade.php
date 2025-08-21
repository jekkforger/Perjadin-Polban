@extends('layouts.sekdir.layout')

@section('title', 'Daftar Penomoran Surat')

@section('sekdir_content')
<div class="sekdir-container px-4 py-3">
    <h1 class="sekdir-page-title mb-4">Nomor Surat</h1>
    
    <div class="p-4 shadow-sm bg-white rounded">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Antrean Surat Tugas Menunggu Penomoran Resmi</h6>
            {{-- Search Bar --}}
            <form action="{{ route('sekdir.nomorSurat') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2"
                    placeholder="Cari Kegiatan..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i> Cari
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th> {{-- Tambahkan No --}}
                            <th>Tanggal Pengusulan</th>
                            <th>Tanggal Berangkat</th>
                            <th>Nomor Surat Pengusulan</th>
                            <th>Sumber Dana</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarSurat as $index => $surat) {{-- Tambahkan index --}}
                        <tr>
                            <td>{{ $loop->iteration + ($daftarSurat->currentPage() - 1) * $daftarSurat->perPage() }}</td>
                            <td>{{ $surat->created_at->format('d M Y') }}</td>
                            <td>{{ $surat->tanggal_berangkat->format('d M Y') }}</td>
                            <td>{{ $surat->nomor_surat_usulan_jurusan }}</td>
                            <td>{{ $surat->sumber_dana }}</td>
                            <td class="text-center">
                                <a href="{{ route('sekdir.nomorSurat.review', $surat->surat_tugas_id) }}" class="btn btn-info btn-sm" title="Lihat Detail & Beri Nomor">
                                    <i class="bi bi-eye-fill"></i> Review & Nomor
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada surat yang menunggu penomoran saat ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="d-flex justify-content-end mt-3">
                {{ $daftarSurat->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sekdir_content.css') }}">
@endpush