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
            <a href="{{ route('pengusul.dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                <span class="icon">
                    <i class="bi bi-columns-gap"></i>
                </span>
                <span class="description">Dashboard</span>
            </a>
            <a href="{{ route('pengusul.new.pengusulan') }}" class="nav-link {{ request()->is('pengusulan') ? 'active' : '' }}">
                <span class="icon">
                    <i class="bi bi-file-earmark-medical"></i>
                </span>
                <span class="description">Pengusulan</span>
            </a>
            <a href="{{ route('pengusul.status') }}" class="nav-link {{ request()->is('status') ? 'active' : '' }}">
                <span class="icon">
                    <i class="bi bi-graph-up"></i>
                </span>
                <span class="description">Status</span>
            </a>
            <a href="{{ route('pengusul.draft') }}" class="nav-link {{ request()->is('draft') ? 'active' : '' }}">
                <span class="icon">
                    <i class="bi bi-file-earmark"></i>
                </span>
                <span class="description">Draft</span>
            </a>
            <a href="{{ route('history.index') }}" class="nav-link {{ Request::routeIs('history.index') ? 'active' : '' }}">
                <span class="icon">
                    <i class="bi bi-clock-history"></i>
                </span>
                <span class="description">History</span>
            </a>
            {{-- Ganti Role Link (dengan Submenu) --}}
            @php
                $rolesWithSwitch = ['sekdir'];
            @endphp
            @if (isset($userRole) && in_array($userRole, $rolesWithSwitch))
            <a href="#submenu" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenu">
                <span class="icon">
                    <i class="bi bi-arrow-repeat"></i>
                </span>
                <span class="description">Ganti Role <i class="bi bi-caret-down"></i></span>
            </a>
            <div class="sub-menu collapse" id="submenu">
                <a href="{{ route('sekdir.dashboard') }}" class="nav-link sub-nav-link">
                    <span class="description">
                        {{ $roleDisplayName }}
                    </span>
                </a>
                <a href="{{ route('pengusul.dashboard') }}" class="nav-link sub-nav-link">
                    <span class="description">
                        Pengusul
                    </span>
                </a>
            </div>
            @endif
        </nav>
    </div>

<!-- Toggle Button -->
<button id="toggle-btn" class="toggle-btn">
    <i class="bi bi-list"></i>
</button>
