@extends('layouts.pengusul.layout')

@section('title', 'Pengusulan')
@section('pengusul_content')
<div class="pengusul-container px-4 py-3">
  <h1 class="pengusul-page-title mb-4">Pengusulan</h1>

  <div class="p-4 shadow-sm bg-white rounded">
      <form id="pengusulanForm" action="{{ route('pengusul.store.pengusulan') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        @include('layouts.pengusul.form-input')
        @include('layouts.pengusul.pilih-pelaksana')
        @include('layouts.pengusul.preview-surat', ['suratSettings' => $suratSettings])

        {{-- =================================================================== --}}
        {{-- <-- AWAL BLOK TOMBOL BARU --> --}}
        {{-- =================================================================== --}}
        <div class="mt-4 d-flex justify-content-between">
            {{-- Tombol Kembali (muncul di langkah 2 & 3) --}}
            <button type="button" class="btn btn-secondary" id="btn-kembali" style="display: none;">Kembali</button>

            {{-- Grup Tombol di Kanan --}}
            <div class="ms-auto d-flex gap-2">
                {{-- Tombol Simpan Draft (muncul di langkah 2) --}}
                <button type="button" class="btn btn-warning" id="save-draft" style="display: none;">Simpan Draft</button>

                {{-- Tombol Lanjut ke Personel (muncul di langkah 1) --}}
                <button type="button" class="btn btn-primary" id="next-to-personel">Selanjutnya</button>
                
                {{-- Tombol Lanjut ke Preview (muncul di langkah 2) --}}
                <button type="button" class="btn btn-success" id="create-task" style="display: none;">Lanjut ke Preview</button>
                
                {{-- Tombol Usulkan (muncul di langkah 3) --}}
                <button type="button" class="btn btn-primary" id="submit-surat" style="display: none;">Usulkan</button>
            </div>
        </div>
        
      </form> {{-- End Form --}}
  </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengusul_content.css') }}">
@endpush

@push('scripts')
<script>
    // Variabel global dari Controller (ini sudah ada dan benar)
    var pembiayaanOld = @json(old('pembiayaan', 'Polban'));
    var paguDesentralisasiOld = @json(old('pagu_desentralisasi', false));
    var errorMessages = @json($errors->all() ?? []);
    @isset($draftData)
        var draftData = @json($draftData);
    @endisset
    @isset($selectedPersonel)
        var selectedPersonelData = @json($selectedPersonel);
    @endisset
    @isset($draft)
        var draftIdVar = @json($draft->surat_tugas_id ?? '');
    @endisset
    var pengusulanUpdateUrl = "{{ route('pengusul.update.pengusulan', ['draft_id' => ':draft_id']) }}";
    var pengusulanStoreUrl = "{{ route('pengusul.store.pengusulan') }}";
    var pengusulanDraftUrl = "{{ route('pengusul.draft') }}";
    var pengusulanStatusUrl = "{{ route('pengusul.status') }}";

    // ===================================================================
    // <-- AWAL BLOK KODE BARU YANG PERLU ANDA TAMBAHKAN -->
    // ===================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Cek apakah variabel draftData ada (artinya ini mode edit)
        @if(isset($draftData))
            console.log("Mode Edit Terdeteksi. Mengisi form dengan data draft...", draftData);
            
            // Mengisi form di Langkah 1
            $('#nama_kegiatan').val(draftData.nama_kegiatan);
            $('#tempat_kegiatan').val(draftData.tempat_kegiatan);
            $('#diusulkan_kepada').val(draftData.diusulkan_kepada);
            $('#nama_penyelenggara').val(draftData.nama_penyelenggara);
            $('#alamat_kegiatan').val(draftData.alamat_kegiatan);
            $('#provinsi').val(draftData.provinsi);
            $('#tanggal_pelaksanaan').val(draftData.tanggal_pelaksanaan);

            // Mengisi radio button pembiayaan
            $('input[name="pembiayaan_option"][value="' + draftData.pembiayaan + '"]').prop('checked', true);
            $('#pembiayaan_value').val(draftData.pembiayaan);

            // Mengisi checkbox pagu dan memicu event 'change' agar input nominal muncul jika perlu
            $('#pagu_desentralisasi_checkbox').prop('checked', draftData.pagu_desentralisasi).trigger('change');
            if (draftData.pagu_desentralisasi) {
                $('#pagu_nominal').val(draftData.pagu_nominal);
            }

            // Mengisi nomor surat usulan yang terpecah
            const nomorSuratParts = draftData.nomor_surat_usulan.split('/');
            if (nomorSuratParts.length === 4) {
                $('input[name="nomor_urutan_surat"]').val(nomorSuratParts[0]);
                $('select[name="tahun_nomor_surat"]').val(nomorSuratParts[3]);
            }
        @endif

        // Cek apakah variabel selectedPersonelData ada
        @if(isset($selectedPersonel))
            console.log("Mengisi personel yang sudah dipilih...", selectedPersonelData);

            // Loop melalui data personel yang sudah dipilih
            selectedPersonelData.forEach(function(personel) {
                // Cari checkbox yang sesuai berdasarkan tipe (pegawai/mahasiswa) dan ID
                const checkbox = document.querySelector(`.personel-checkbox[data-type="${personel.type}"][value="${personel.id}"]`);
                if (checkbox) {
                    checkbox.checked = true; // Centang checkbox-nya
                    updateSelectedPersonel(checkbox); // Panggil fungsi yang sama seperti saat user mengklik
                }
            });
        @endif
    });
    // ===================================================================
    // <-- AKHIR BLOK KODE BARU -->
    // ===================================================================

</script>
<script src="{{ asset('js/pengusulan.js') }}"></script>
@endpush