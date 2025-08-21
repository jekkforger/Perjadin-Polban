@extends('layouts.admin.layout')

@section('title', 'Akun Pengguna')
@section('admin_content')
<div class="admin-container px-4 py-3">
    <h1 class="admin-page-title mb-4">Akun Pengguna</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus me-1"></i> Tambah Akun
            </a>
        </div>

        {{-- Tabel Data Akun --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover no-vertical-borders">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Kode Pengusul</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $roleLabels[$user->role] ?? $user->role }}</td>
                            <td>
                                {{-- Tampilkan kode_pengusul jika role adalah pengusul --}}
                                @if($user->role == 'pengusul')
                                    {{ $user->kode_pengusul ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal" data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada data akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center mt-3">
                {{-- Kiri: Form Rows per Page --}}
                <form method="GET" class="d-flex align-items-center">
                    <label for="perPage" class="me-2 mb-0">Rows per page:</label>
                    <select name="per_page" id="perPage" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach ([10, 20, 50, 100] as $size)
                            <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </form>

                {{-- Tengah: Info Jumlah --}}
                <div class="text-muted small">
                    Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} data
                </div>

                {{-- Kanan: Pagination --}}
                <div>
                    {{ $users->appends(request()->all())->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Hapus --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" id="deleteForm">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Hapus Akun</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <p>Apakah Anda yakin ingin menghapus akun <strong id="userName"></strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Hapus</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/admin.js') }}"></script>
@endpush
