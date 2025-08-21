@extends('layouts.admin.layout')

@section('title', 'Template Surat Tugas')

@section('admin_content')
<div class="admin-container px-4 py-3">
    <h1 class="admin-page-title mb-4">Template Surat Tugas</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Terjadi kesalahan validasi. Mohon periksa kembali input Anda.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form id="templateSettingsForm" action="{{ route('admin.update') }}" method="POST">
                @csrf
                @method('PUT') {{-- Gunakan metode PUT untuk update --}}

                <div class="mb-3">
                    <label for="nama_kementerian" class="form-label">Nama Kementerian</label>
                    <input type="text" class="form-control" id="nama_kementerian" name="nama_kementerian"
                        value="{{ old('nama_kementerian', $settings->nama_kementerian ?? '') }}" required disabled> {{-- Default disabled --}}
                    @error('nama_kementerian')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nama_direktur" class="form-label">Nama Direktur</label>
                    <input type="text" class="form-control" id="nama_direktur" name="nama_direktur"
                        value="{{ old('nama_direktur', $settings->nama_direktur ?? '') }}" required disabled> {{-- Default disabled --}}
                    @error('nama_direktur')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nip_direktur" class="form-label">NIP Direktur</label>
                    <input type="text" class="form-control" id="nip_direktur" name="nip_direktur"
                        value="{{ old('nip_direktur', $settings->nip_direktur ?? '') }}" required disabled> {{-- Default disabled --}}
                    @error('nip_direktur')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tembusan_default" class="form-label">Tembusan Default (Pisahkan dengan koma)</label>
                    <textarea class="form-control" id="tembusan_default" name="tembusan_default" rows="3" disabled>{{ old('tembusan_default', implode(', ', $settings->tembusan_default ?? [])) }}</textarea> {{-- Default disabled --}}
                    <small class="form-text text-muted">Contoh: Para Wakil Direktur, Ketua Jurusan, Bagian Keuangan</small>
                    @error('tembusan_default')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="button" id="editSaveButton" class="btn btn-primary">Edit</button> {{-- Tombol Edit/Simpan --}}
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@push('scripts')
    {{-- Memanggil file JavaScript khusus pengaturan admin --}}
    <script src="{{ asset('js/admin.js') }}"></script>
@endpush
