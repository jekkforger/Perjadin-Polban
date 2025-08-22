@extends('layouts.admin.layout')

@section('title', 'Data Mahasiswa')
@section('admin_content')
<div class="admin-container px-4 py-3">
    <h1 class="admin-page-title mb-4">Data Mahasiswa</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importMahasiswaModal">
                <i class="fas fa-file-excel me-2"></i>Upload Excel
            </button>
        </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover no-vertical-borders">
            <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Jurusan</th>
                    <th>Prodi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mahasiswa as $item)
                    <tr>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->nim }}</td>
                        <td>{{ $item->jurusan }}</td>
                        <td>{{ $item->prodi }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada data mahasiswa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- Modal Upload Mahasiswa --}}
<div class="modal fade" id="importMahasiswaModal" tabindex="-1" aria-labelledby="importMahasiswaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importMahasiswaModalLabel">Impor Data Mahasiswa dari Excel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div class="mb-3">
                <label for="file_mahasiswa" class="form-label">Pilih file (.xlsx atau .csv)</label>
                <input class="form-control" type="file" id="file_mahasiswa" name="file_mahasiswa" required accept=".xlsx, .csv">
            </div>
            <div class="alert alert-info small">
                <strong>Catatan:</strong>
                <ul>
                    <li>Pastikan file Excel Anda memiliki header kolom: <strong>nama, nim, jurusan, prodi</strong>.</li>
                    <li>Sistem akan memperbarui data jika NIM sudah ada, dan membuat data baru jika NIM belum ada.</li>
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