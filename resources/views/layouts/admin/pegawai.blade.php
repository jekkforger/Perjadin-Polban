@extends('layouts.admin.layout')

@section('title', 'Data Pegawai')
@section('admin_content')
<div class="admin-container px-4 py-3">
    <h1 class="admin-page-title mb-4">Data Pegawai</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <div class="table-responsive">
            <table class="table table-bordered table-hover no-vertical-borders">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Pangkat</th>
                        <th>Golongan</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawai as $item)
                        <tr>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->nip }}</td>
                            <td>{{ $item->pangkat }}</td>
                            <td>{{ $item->golongan }}</td>
                            <td>{{ $item->jabatan }}</td>
                            <td>
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input 
                                        class="form-check-input toggle-aktif" 
                                        type="checkbox" 
                                        role="switch"
                                        data-id="{{ $item->id }}"
                                        {{ $item->aktif ?? true ? 'checked' : '' }}
                                    >
                                    <span class="ms-2 status-label">{{ $item->aktif ?? true ? 'Aktif' : 'Tidak Aktif' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data pegawai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/admin.js') }}"></script>
@endpush