<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
    <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
        <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true" data-kt-scroll-activate="true"
            data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-save-state="true">

            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">

                @php $role = auth()->user()?->role; @endphp

                {{-- ============ ADMIN ============ --}}
                @if ($role === 'admin')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <span class="menu-icon"><i class="bi bi-speedometer2 fs-2"></i></span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div>

                    <div class="menu-content">
                        <div class="separator my-3"></div>
                        <span class="menu-heading text-uppercase text-muted fs-8">Master Data</span>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" href="{{ route('admin.clients.index') }}">
                            <span class="menu-icon"><i class="bi bi-building fs-2"></i></span>
                            <span class="menu-title">Klien</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                            <span class="menu-icon"><i class="bi bi-people fs-2"></i></span>
                            <span class="menu-title">Karyawan</span>
                        </a>
                    </div>

                    <div class="menu-content">
                        <div class="separator my-3"></div>
                        <span class="menu-heading text-uppercase text-muted fs-8">Proyek</span>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}" href="{{ route('admin.projects.index') }}">
                            <span class="menu-icon"><i class="bi bi-kanban fs-2"></i></span>
                            <span class="menu-title">Manajemen Proyek</span>
                        </a>
                    </div>

                {{-- ============ PM ============ --}}
                @elseif ($role === 'pm')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('pm.dashboard') ? 'active' : '' }}" href="{{ route('pm.dashboard') }}">
                            <span class="menu-icon"><i class="ki-duotone ki-element-11 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div>

                    <div class="menu-content">
                        <div class="separator my-3"></div>
                        <span class="menu-heading text-uppercase text-muted fs-8">Proyek</span>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('pm.projects.*') ? 'active' : '' }}" href="{{ route('pm.projects.index') }}">
                            <span class="menu-icon"><i class="ki-duotone ki-briefcase fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                            <span class="menu-title">Proyek Saya</span>
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
