@extends('layouts.admin.layout')

@section('title', 'Tambah Akun')

@section('admin_content')
<div class="admin-container px-4 py-3">
    <h1 class="admin-page-title mb-4 fw-bold">Tambah Akun</h1>

    <div class="bg-white rounded shadow-sm p-4">
        <a href="{{ route('admin.akun') }}" class="btn btn-link p-0 mb-3">
            <i class="bi bi-arrow-left"></i>
        </a>

        <form action="{{ route('admin.store') }}" method="POST" class="row g-4">
            @csrf

            {{-- Kiri --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Nama" required>
                </div>

                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                        <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3 position-relative">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Konfirmasi Password" required>
                        <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;">
                            <i class="bi bi-eye-slash" id="toggleConfirmPasswordIcon"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>

            {{-- Kanan --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Email" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select" onchange="togglePengusulFields()" required>
                        <option value="" disabled selected>Role</option>
                        <option value="pengusul">Pengusul</option>
                        <option value="wadir_1">Wakil Direktur I</option>
                        <option value="wadir_2">Wakil Direktur II</option>
                        <option value="wadir_3">Wakil Direktur III</option>
                        <option value="wadir_4">Wakil Direktur IV</option>
                        <option value="direktur">Direktur</option>
                        <option value="sekdir">Sekretaris Direktur</option>
                        <option value="bku">BKU</option>
                        <option value="admin">Admin</option>
                        <option value="pelaksana">Pelaksana</option>
                    </select>
                </div>

                {{-- Tambahan untuk role pengusul --}}
                <div class="mb-3 d-none" id="kode-pengusul-field">
                    <label for="kode_pengusul" class="form-label">Kode Pengusul</label>
                    <input type="text" name="kode_pengusul" id="kode_pengusul" class="form-control" placeholder="Kode pengusul">
                </div>

                <div class="mb-3 d-none" id="unit-kerja-field">
                    <label for="nama_unit_kerja" class="form-label">Nama Unit Kerja</label>
                    <input type="text" name="nama_unit_kerja" id="nama_unit_kerja" class="form-control" placeholder="Nama Unit kerja">
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

    <script>
        function togglePengusulFields() {
            const role = document.getElementById('role').value;
            const isPengusul = role === 'pengusul';

            document.getElementById('kode-pengusul-field').classList.toggle('d-none', !isPengusul);
            document.getElementById('unit-kerja-field').classList.toggle('d-none', !isPengusul);
        }
    </script>
@endpush
