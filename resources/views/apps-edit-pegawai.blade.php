@extends('layouts.master-mobile')
@section('title')
    @lang('Tambah Pegawai')
@endsection
@section('content')
@php
    use App\Models\PersentaseGaji;
    use App\Models\GajiPokok;
    use App\Models\Pendidikan;
    use App\Models\Tunjangan;
    use App\Models\MasaKerja;
    use App\Models\SkalaGajiGuru;
    use App\Models\SkalaGajiAdmin;
@endphp
<div class="position-relative mx-n4" style="margin-top: -100px">
    <div class="profile-wid-bg profile-setting-img">
        <img src="{{ URL::asset('build/images/bc-ali.jpg') }}" class="profile-wid-img" alt="">
    </div>
</div>

<div class="row mt-n5">
    <form method="POST" action="{{ route('admin-data-ptk.updateApps', $users->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')    
    <div class="col-xxl-3">
        <div class="card mt-n5">
            <div class="card-body p-4">
                <div class="text-center">
                    <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                        <img src="{{ URL::asset('build/images/users/' . ($users->avatar ?? 'default.svg')) }}" class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow" alt="user-profile-image">
                        <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                            <input id="profile-img-file-input" name="avatar" type="file" class="profile-img-file-input">
                            <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                <span class="avatar-title rounded-circle bg-light text-body material-shadow">
                                    <i class="ri-camera-fill"></i>
                                </span>
                            </label>
                        </div>
                    </div>
                    <h5 class="fs-16 mb-1">pilih Foto</h5>
                    <p class="text-muted mb-0">jpg, jpeg,png</p>
                </div>
            </div>
        </div>
    </div>
    <!--end col-->
    <div class="col-xxl-9">

        <div class="card mt-xxl-n5">
            <div class="card-header">
                @if (session('success'))
                <div class="alert alert-success alert-border-left alert-dismissible fade show material-shadow" role="alert">
                    <i class="ri-check-double-line me-3 align-middle fs-16"></i>
                    <strong>Success</strong> - {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                            <i class="fas fa-home"></i> Personal Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#passwordn" role="tab">
                            <i class="fas fa-home"></i> Password
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content">
                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama</label>
                                        <input disabled type="text" class="form-control" id="name" name="name" value="{{ $users->name ?? '-' }}" required placeholder="Nama">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <input type="text" class="form-control" value="{{ $users->alamat ?? '-' }}" id="alamat" name="alamat" required placeholder="Alamat">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="no_hp" class="form-label">No. HP</label>
                                        <input type="number" class="form-control" id="no_hp" value="{{ $users->no_hp ?? '-' }}" name="no_hp" required placeholder="Nomor Handphone">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-12">
                                    <div class="hstack gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        </div>
                        <div class="tab-pane" id="passwordn" role="tabpanel">
                            <form method="POST" action="{{ route('update.password', $users->id) }}">
                                @csrf
                                @method('PUT')                                                     
                                <div class="mb-3">
                                    <label class="form-label" for="password-input">Password Sebelumnya</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" name="current_password" class="form-control pe-5 password-input" placeholder="Enter password" required>
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button"
                                            id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                    </div>
                                </div>
                                                                                  
                                <div class="mb-3">
                                    <label class="form-label" for="password-input">Password Baru</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" name="password" class="form-control pe-5 password-input" onpaste="return false" placeholder="Enter password" id="password-input" aria-describedby="passwordInput" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button"
                                            id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="password-confirm">Konfirmasi Password Baru</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" name="password_confirmation" class="form-control pe-5 password-input" placeholder="Ulangi password baru" id="password-confirm" required>
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button"
                                            id="password-addon-confirm"><i class="ri-eye-fill align-middle"></i></button>
                                    </div>
                                </div>
                                

                                <div id="password-contain" class="p-3 bg-light mb-2 rounded">
                                    <h5 class="fs-13">Password Harus:</h5>
                                    <p id="pass-length" class="invalid fs-12 mb-2">Minimal <b>8 characters</b></p>
                                    <p id="pass-lower" class="invalid fs-12 mb-2">Terdapat <b>huruf kecil</b> antara (a-z)</p>
                                    <p id="pass-upper" class="invalid fs-12 mb-2">Terdapat <b>huruf kapital</b> antara (A-Z)</p>
                                    <p id="pass-number" class="invalid fs-12 mb-0">Terdapat <b>Angka</b> (0-9)</p>
                                </div>

                                <div class="mt-4">
                                    <button class="btn btn-success w-100" type="submit">Reset Password</button>
                                </div>

                            </form>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>
<!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/profile-setting.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
    <script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/passowrd-create.init.js') }}"></script>
@endsection
