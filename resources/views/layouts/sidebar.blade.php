<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/sditlogo.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/sditlogo.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/sditlogo1.png') }}" alt="" height="32">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/sditlogo1.png') }}" alt="" height="97"><br>
                <h5 style="color: white">MA'HAD ALI BIN ABI THALIB</h5>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user"
                    src="@if (Auth::user()->avatar != '') {{ URL::asset('images/' . Auth::user()->avatar) }}@else{{ URL::asset('build/images/users/avatar-1.jpg') }} @endif"
                    alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">{{ Auth::user()->name }}</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i
                            class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span
                            class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <!-- item-->
            <h6 class="dropdown-header">Welcome {{ Auth::user()->name }}!</h6>
            <a class="dropdown-item" href="pages-profile"><i
                    class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Profile</span></a>
            <a class="dropdown-item" href="apps-chat"><i
                    class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Messages</span></a>
            <a class="dropdown-item" href="apps-tasks-kanban"><i
                    class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Taskboard</span></a>
            <a class="dropdown-item" href="pages-faqs"><i
                    class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Help</span></a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="pages-profile"><i
                    class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Balance :
                    <b>$5971.67</b></span></a>
            <a class="dropdown-item" href="pages-profile-settings"><span
                    class="badge bg-success-subtle text-success mt-1 float-end">New</span><i
                    class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Settings</span></a>
            <a class="dropdown-item" href="auth-lockscreen-basic"><i
                    class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock
                    screen</span></a>

            <a class="dropdown-item " href="javascript:void();"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                    class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span
                    key="t-logout">@lang('translation.logout')</span></a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                {{-- Admin Disini --}}
                
                @if(auth()->user()->role === 'admin')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="/">
                        <i class="ri-apps-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebardatapegawai" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebardatapegawai">
                        <i class="ri-folder-user-line"></i> <span>Data Pegawai</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebardatapegawai">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="/admin-data-ptk" class="nav-link">Daftar Pegawai</a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin-tambah-pegawai" class="nav-link">Tambah Pegawai</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarabsensi" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarabsensi">
                        <i class="ri-map-pin-user-line"></i> <span>Lokasi & Absensi</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarabsensi">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="/admin-setting-absensi" class="nav-link">Jam Kerja & Lokasi</a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin-absensi-harian" class="nav-link">Absensi Harian</a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin-absensi-rekap" class="nav-link">Rekap Kehadiran</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span>Keuangan</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarkitir" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarkitir">
                        <i class="ri-wallet-line"></i> <span>Kitir Gaji</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarkitir">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin-data-kitir.edit') }}" class="nav-link">Kitir Gaji Bulanan</a>
                            </li>
                            <li class="nav-item">
                                <a href="/admin-skala-gaji" class="nav-link">Skala Gaji</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="admin-kasbon">
                        <i class="ri-refund-2-line"></i> <span>Kasbon</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span>Manajemen</span></li>
                

                <li class="nav-item">
                    <a class="nav-link menu-link" href="/public/arsip-kitir">
                        <i class="ri-wallet-line"></i> <span>Arsip Kitir Excel</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="/arsip-rekap-absensi">
                        <i class="ri-wallet-line"></i> <span>Arsip Kehadiran Excel</span>
                    </a>
                </li>
                @endif

                @if(auth()->user()->role === 'operator')
                <li class="menu-title"><span>Operator Sekolah</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->is('admin-setting-absensi') ? 'active' : '' }}" href="/admin-setting-absensi">
                        <i class="ri-map-pin-user-line"></i> <span>Pengaturan Absensi</span>
                    </a>
                </li>
                @endif

                <!--<li class="nav-item">-->
                <!--    <a class="nav-link menu-link" href="#sidebarpengaturan" data-bs-toggle="collapse" role="button"-->
                <!--        aria-expanded="false" aria-controls="sidebarpengaturan">-->
                <!--        <i class="ri-pencil-ruler-2-line"></i> <span>Pengaturan</span>-->
                <!--    </a>-->
                <!--    <div class="collapse menu-dropdown" id="sidebarpengaturan">-->
                <!--        <ul class="nav nav-sm flex-column">-->
                <!--            <li class="nav-item">-->
                <!--                <a href=""  class="nav-link">Manajemen Users</a>-->
                <!--            </li>-->
                <!--            <li class="nav-item">-->
                <!--                <a href=""  class="nav-link">Role dan Akses</a>-->
                <!--            </li>-->
                <!--            <li class="nav-item">-->
                <!--                <a href=""  class="nav-link">Tahun Ajaran</a>-->
                <!--            </li>-->
                <!--        </ul>-->
                <!--    </div>-->
                <!--</li>-->

                <!--<li class="nav-item">-->
                <!--    <a class="nav-link menu-link" href="#sidebarlaporan" data-bs-toggle="collapse" role="button"-->
                <!--        aria-expanded="false" aria-controls="sidebarlaporan">-->
                <!--        <i class="ri-file-list-3-line"></i> <span>Laporan</span>-->
                <!--    </a>-->
                <!--    <div class="collapse menu-dropdown" id="sidebarlaporan">-->
                <!--        <ul class="nav nav-sm flex-column">-->
                <!--            <li class="nav-item">-->
                <!--                <a href="" class="nav-link">Laporan Gaji Bulanan</a>-->
                <!--            </li>-->
                <!--            <li class="nav-item">-->
                <!--                <a href="" class="nav-link">Laporan Kasbon</a>-->
                <!--            </li>-->
                <!--            <li class="nav-item">-->
                <!--                <a href="" class="nav-link">Laporan Kehadiran</a>-->
                <!--            </li>-->
                <!--            <li class="nav-item">-->
                <!--                <a href="" class="nav-link">Cetak Slip Gaji</a>-->
                <!--            </li>-->
                <!--        </ul>-->
                <!--    </div>-->
                <!--</li>-->

               

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
