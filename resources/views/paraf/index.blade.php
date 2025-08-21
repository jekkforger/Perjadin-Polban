{{-- resources/views/paraf/index.blade.php --}}
@extends('layouts.main')

@section('title', 'Paraf Digital')

{{-- Menyertakan sidebar yang sesuai --}}
@section('sidebar')
    @php
        $userRole = null;
        $roleDisplayName = 'Pengguna';
        if (\Auth::check()) {
            $userRole = \Auth::user()->role;
            $loginController = new \App\Http\Controllers\Auth\LoginController();
            $roleDisplayName = $loginController->getRoleDisplayName($userRole);
        }
    @endphp
    @if ($userRole)
        @if (in_array($userRole, ['wadir_1', 'wadir_2', 'wadir_3', 'wadir_4']))
            @include('layouts.wadir.partials.sidebar', ['userRole' => $userRole, 'roleDisplayName' => $roleDisplayName])
        @elseif ($userRole == 'direktur')
            @include('layouts.direktur.partials.sidebar', ['userRole' => $userRole, 'roleDisplayName' => $roleDisplayName])
        {{-- Tambahkan kondisi untuk role lain jika mereka bisa mengakses halaman Paraf --}}
        @else
            <div></div>
        @endif
    @else
        <div></div>
    @endif
@endsection

@section('content')
<div class="paraf-container px-4 py-3">
    <h1 class="paraf-page-title mb-4">Paraf Digital</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        @if ($hasParafs === false) {{-- Tampilan Default: Belum ada paraf --}}
            <div class="card-body text-center py-5">
                <img src="{{ asset('img/nodata_paraf.png') }}" alt="Upload Icon" class="paraf-no-document-icon mb-3">
                <p class="paraf-no-document-text mb-2">Belum ada paraf digital yang diunggah.</p>
                <a href="#" class="paraf-upload-link" data-bs-toggle="modal" data-bs-target="#uploadParafModal">Upload Paraf</a> {{-- Link memicu modal --}}
            </div>
        @else
        {{-- Tampilan Paraf yang Sudah Diupload (Grid Card) --}}
        <div class="row paraf-documents-grid g-4">
            @foreach ($digitalParafs as $paraf)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                <div class="document-card card shadow h-100">
                    <div class="card-body d-flex flex-column align-items-center text-center">
                        <img src="{{ asset('img/signature.jpg') }}" alt="Paraf Icon" class="document-card-icon mb-3">
                        
                        <h5 class="document-card-title mb-1">{{ $paraf->original_name }}</h5>
                        <p class="document-card-info mb-1">Diunggah: {{ $paraf->created_at->format('d M Y') }}</p>
                        
                        <div class="document-actions mt-3">
                            <a href="{{ Storage::url($paraf->file_path) }}" target="_blank" class="btn btn-primary btn-sm me-2">Lihat</a>
                            {{-- **PERBAIKAN: Tombol Hapus memicu modal** --}}
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteParafModal" 
                                    data-paraf-id="{{ $paraf->id }}" data-paraf-name="{{ $paraf->original_name }}">
                                Hapus
                            </button>
                            {{-- Form delete yang sebenarnya (tersembunyi) --}}
                            <form id="delete-paraf-form-{{ $paraf->id }}" action="{{ route('wadir.paraf.delete', $paraf->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Modal Upload File --}}
<div class="modal fade" id="uploadParafModal" tabindex="-1" aria-labelledby="uploadParafModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadParafModalLabel">Upload Paraf Digital</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="parafUploadForm" action="{{ route('wadir.paraf.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- Input File Utama --}}
                    <div class="mb-3 paraf-drop-area">
                        <label for="paraf_file" class="form-label paraf-drop-label text-center">
                            <i class="bi bi-cloud-arrow-up paraf-drop-icon mb-2"></i>
                            <p class="mb-1">Tarik & lepas file di sini atau <span class="text-primary paraf-browse-link">Telusuri</span></p>
                            <small class="text-muted">Mendukung file PNG atau JPG (maks 2MB)</small>
                        </label>
                        <input class="form-control @error('paraf_file') is-invalid @enderror" type="file" id="paraf_file" name="paraf_file" hidden required accept=".png,.jpg,.jpeg">
                        @error('paraf_file')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    
                    {{-- Preview File Yang Dipilih --}}
                    <div id="file-preview-area" class="mb-3" style="display: none;">
                        <div class="alert alert-info py-2">
                            File terpilih: <span id="selected-file-name" class="fw-bold"></span>
                            <button type="button" class="btn-close float-end" aria-label="Remove file" id="remove-file-btn"></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitUploadParafBtn">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- **BARU DITAMBAHKAN: Modal Konfirmasi Hapus** --}}
<div class="modal fade" id="deleteParafModal" tabindex="-1" aria-labelledby="deleteParafModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteParafModalLabel">Konfirmasi Hapus Paraf</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus paraf ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteParafBtn">Hapus</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/paraf.css') }}">
@endpush

@push('scripts')
    {{-- Variabel showUploadModalOnLoad perlu didefinisikan di sini agar tersedia di paraf.js --}}
    <script>
        var showUploadModalOnLoad = {{ session('showUploadModal') ? 'true' : 'false' }};
    </script>
    <script src="{{ asset('js/paraf.js') }}"></script>
@endpush