<!-- Sidebar -->
<div class="sidebar" class="sticky-top" id="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('img/polban2.png') }}" alt="Polban Logo" class="sidebar-logo">
        <div class="sidebar-title">
            <span class="app-name">Aplikasi SPPD</span>
            <span class="institution-name">Polban</span>
        </div>
    </div>
        <nav class="nav flex-column">
            <a href="{{ route('admin.pegawai') }}" class="nav-link {{ request()->is('pegawai') ? 'active' : '' }}">
                <span class="icon">
                    <i class="bi bi-people-fill"></i>
                </span>
                <span class="description">Pegawai</span>
            </a>
            <a href="{{ route('admin.mahasiswa') }}" class="nav-link {{ request()->is('mahasiswa') ? 'active' : '' }}">
                <span class="icon">
                    <i class="bi bi-people"></i>
                </span>
                <span class="description">Mahasiswa</span>
            </a>
            <a href="{{ route('admin.akun') }}" class="nav-link {{ request()->is('akun') ? 'active' : '' }}">
                <span class="icon">
                    <i class="bi bi-person"></i>
                </span>
                <span class="description">Akun</span>
            </a>
            <a href="{{ route('admin.edit') }}" class="nav-link {{ request()->is('template') ? 'active' : '' }}">
                <span class="icon">
                    <i class="bi bi-file-earmark"></i>
                </span>
                <span class="description">Template Surat</span>
            </a>
        </nav>
    </div>

<!-- Toggle Button -->
<button id="toggle-btn" class="toggle-btn">
    <i class="bi bi-list"></i>
</button>
