@extends('layouts.pengusul.layout')

@section('title', 'Pengusulan')
@section('pengusul_content')
<div class="pengusul-container px-4 py-3">
  <h1 class="pengusul-page-title mb-4">Pengusulan</h1>

  <div class="p-4 shadow-sm bg-white rounded">
      <form id="pengusulanForm" action="{{ route('pengusul.store.pengusulan') }}" method="POST" enctype="multipart/form-data">
        @csrf
        {{-- Step 1: Form informasi kegiatan --}}
        @include('layouts.pengusul.form-input')

        {{-- Step 2: Pilih pelaksana tugas --}}
        @include('layouts.pengusul.pilih-pelaksana')

        {{-- Step 3: Preview surat tugas (dan tombol-tombolnya) --}}
        @include('layouts.pengusul.preview-surat', ['suratSettings' => $suratSettings])
        
      </form> {{-- End Form --}}
  </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengusul_content.css') }}">
@endpush

@push('scripts')
<script>
    // const localProvincesPath = "{{ asset('json/provinsi.json') }}";
    // Sertakan variabel global JS jika perlu, misal untuk old value, draft id, atau url route
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
</script>
<script src="{{ asset('js/pengusulan.js') }}"></script>
@endpush

