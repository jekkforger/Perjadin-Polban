@extends('layouts.pelaksana.layout')

@section('title', 'Penugasan')
@section('pelaksana_content')
<div class="pelaksana-container px-4 py-3">
    <h1 class="pelaksana-page-title mb-4">Penugasan</h1>

    <div class="p-4 shadow-sm bg-white rounded">
        <h5 class="mb-3">Detail</h5>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pelaksana_content.css') }}">
@endpush