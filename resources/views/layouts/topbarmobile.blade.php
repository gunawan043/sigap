<header id="page-topbaer" style="background: linear-gradient(90deg, #429a90, #2c6c66); box-shadow: none; padding-top:20px">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex mt-5 mb-5">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="index" class="logo logo-dark" style="color: white;">
                        <span class="logo-sm">
                            <img class="rounded-circle header-profile-user" src="{{ URL::asset('build/images/sditlogo.svg') }}" style="margin-right:10px; background:white; padding:2px" alt="" height="38" width="">SIGAP
                        </span>
                        <span class="logo-lg">
                            <img class="rounded-circle header-profile-user" src="{{ URL::asset('build/images/sditlogo.svg') }}" style="margin-right:10px; background:white; padding:2px" alt="" height="38" width="">SIGAP
                        </span>
                    </a>

                    <a href="index" class="logo logo-light" style="color: white;">
                        <span class="logo-sm">
                            <img class="rounded-circle header-profile-user" src="{{ URL::asset('build/images/sditlogo.svg') }}" style="margin-right:10px; background:white; padding:2px" alt="" height="38" width="">SIGAP
                        </span>
                        <span class="logo-lg">
                            <img class="rounded-circle header-profile-user" src="{{ URL::asset('build/images/sditlogo.svg') }}" style="margin-right:10px; background:white; padding:2px" alt="" height="38" width="">SIGAP
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                    <a href="index" class="logo logo-light" style="color: white;">
                        <span class="logo-sm">
                            <img class="rounded-circle header-profile-user" src="{{ URL::asset('build/images/sditlogo.svg') }}" style="margin-right:10px; background:white; padding:2px" alt="" height="38" width="">SIGAP
                        </span>
                        <span class="logo-lg">
                            <img class="rounded-circle header-profile-user" src="{{ URL::asset('build/images/sditlogo.svg') }}" style="margin-right:10px; background:white; padding:2px" alt="" height="38" width="">SIGAP
                        </span>
                    </a>
                </button>

            </div>

            <div class="d-flex align-items-center mt-5 mb-5">

                <div class="dropdown ms-sm-3 header-item topbar-user " style="background:#0000">
                    <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" style="margin-right:10px; background:white; padding:2px" src="@if (Auth::user()->avatar != ''){{ URL::asset('build/images/users/' . Auth::user()->avatar) }}@else{{ URL::asset('build/images/users/avatar-1.jpg') }}@endif" alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{Auth::user()->name}}</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome {{Auth::user()->name}}</h6>
                        <a class="dropdown-item" href="/profil-ptk/{{Auth::user()->id}}"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
                        {{-- <a class="dropdown-item" href="apps-chat"><i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a> --}}
                        {{-- <a class="dropdown-item" href="apps-tasks-kanban"><i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Taskboard</span></a> --}}
                        {{-- <a class="dropdown-item" href="pages-faqs"><i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Help</span></a> --}}
                        <div class="dropdown-divider"></div>
                        {{-- <a class="dropdown-item" href="pages-profile"><i class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Balance : <b>$5971.67</b></span></a> --}}
                        <a class="dropdown-item" href="/edit-profile-aku-ini/ptk/{{Auth::user()->id}}"><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Settings</span></a>
                        {{-- <a class="dropdown-item" href="auth-lockscreen-basic"><i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock screen</span></a> --}}
                        <a class="dropdown-item " href="javascript:void();" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="bx bx-power-off font-size-16 align-middle me-1"></i> <span key="t-logout">@lang('translation.logout')</span></a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
