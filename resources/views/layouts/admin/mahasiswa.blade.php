@extends('layouts.admin.layout')

@section('title', 'Data Mahasiswa')
@section('admin_content')
<div class="admin-container px-4 py-3">
    <h1 class="admin-page-title mb-4">Data Mahasiswa</h1>

    <div class="p-4 shadow-sm bg-white rounded">
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
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush