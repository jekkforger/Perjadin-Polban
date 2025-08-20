@extends('layouts.admin.layout')

@section('title', 'Data Pegawai')
@section('admin_content')
<div class="admin-container px-4 py-3">
    <h1 class="admin-page-title mb-4">Data Pegawai</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importPegawaiModal">
                <i class="fas fa-file-excel me-2"></i>Upload Excel
            </button>
        </div>
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

<div class="modal fade" id="importPegawaiModal" tabindex="-1" aria-labelledby="importPegawaiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importPegawaiModalLabel">Impor Data Pegawai dari Excel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.pegawai.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div class="mb-3">
                <label for="file_pegawai" class="form-label">Pilih file (.xlsx atau .csv)</label>
                <input class="form-control" type="file" id="file_pegawai" name="file_pegawai" required accept=".xlsx, .csv">
            </div>
            <div class="alert alert-info small">
                <strong>Catatan:</strong>
                <ul>
                    <li>Pastikan file Excel Anda memiliki header kolom: <strong>nama, nip, pangkat, golongan, jabatan</strong>.</li>
                    <li>Sistem akan memperbarui data jika NIP sudah ada, dan membuat data baru jika NIP belum ada.</li>
                </ul>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Impor</button>
        </div>
      </form>
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