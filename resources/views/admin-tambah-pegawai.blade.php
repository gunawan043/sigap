@extends('layouts.master')
@section('title')
    @lang('Tambah Pegawai')
@endsection
@section('content')
@php
    use App\Models\PersentaseGaji;
    use App\Models\GajiPokok;
    use App\Models\Pendidikan;
    use App\Models\Tunjangan;
    use App\Models\Masakerja;
    use App\Models\SkalaGajiGuru;
    use App\Models\SkalaGajiAdmin;
@endphp
<div class="position-relative mx-n4 mt-n4">
    <div class="profile-wid-bg profile-setting-img">
        <img src="{{ URL::asset('build/images/bc-ali.jpg') }}" class="profile-wid-img" alt="">
    </div>
</div>

<form method="POST" action="/admin-post/data-ptk" enctype="multipart/form-data">
    @csrf
<div class="row mt-n5">
    <div class="col-xxl-3">
        <div class="card mt-n5">
            <div class="card-body p-4">
                <div class="text-center">
                    <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                        <img src="{{ URL::asset('build/images/users/default.svg') }}" class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow" alt="user-profile-image">
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
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content">
                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="name" name="name" required placeholder="Nama">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="jk" class="form-label">Jenis Kelamin</label>
                                        <select class="form-control" data-choices1 name="jk"
                                            id="jk">
                                            <tbody>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="Laki-Laki">Laki-Laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required placeholder="Tempat Lahir">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                        <input type="text" class="form-control" data-provider="flatpickr" id="tanggal_lahir" name="tanggal_lahir" data-date-format="d M, Y" required placeholder="Pilih Tanggal" />
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <input type="text" class="form-control" id="alamat" name="alamat" required placeholder="Alamat">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="no_hp" class="form-label">No. HP</label>
                                        <input type="number" class="form-control" id="no_hp" name="no_hp" required placeholder="Nomor Handphone">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="dana_bos" class="form-label">Dana BOS</label>
                                        <input type="number" class="form-control" id="dana_bos" name="dana_bos" required placeholder="Dana BOS">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="pendidikan" class="form-label">Memiliki Sertifikasi</label>
                                        <select class="form-control" data-choices1 name="sertifikasi" required
                                            id="sertifikasi">
                                            <tbody>
                                            <option value="">Memiliki Sertifikasi?</option>
                                            <option value="0">Tidak</option>
                                            <option value="1">Iya</option>
                                        </select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="pendidikan" class="form-label">Pendidikan</label>
                                        <select class="form-control" data-choices1 name="pendidikan" required
                                            id="pendidikan">
                                            <tbody>
                                            <option value="">Pilih Pendidikan</option>
                                            <option value="D0">D0</option>
                                            <option value="D1">D1</option>
                                            <option value="D2">D2</option>
                                            <option value="D3">D3</option>
                                            <option value="D4">D4</option>
                                            <option value="S1">S1</option>
                                            <option value="S2">S2</option>
                                            <option value="S3">S3</option>
                                        </select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="awal_masuk" class="form-label">Awal Masuk</label>
                                        <input type="text" class="form-control" data-provider="flatpickr" id="awal_masuk" name="awal_masuk" data-date-format="d M, Y" required placeholder="Pilih Tanggal" />
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    @php
                                        $tunjangan = Tunjangan::get();
                                    @endphp
                                    <div class="mb-3">
                                        <label for="subjek" class="form-label">Subjek</label>
                                        <select class="form-control" data-choices1 name="subjek"
                                            id="subjek">
                                            <tbody>
                                            <option value="">Pilih Tunjangan</option>
                                            @foreach ($tunjangan as $tj) 
                                            <option value="{{$tj->Kode}}">{{$tj->Kode}} - {{$tj->Subjek}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="induk" class="form-label">Induk</label>
                                        <select class="form-control" data-choices1 name="induk"
                                            id="induk">
                                            <tbody>
                                            <option value="">Pilih Induk</option>
                                            <option value="Yayasan">Yayasan</option>
                                            <option value="Sekolah">Sekolah</option>
                                            <option value="TPQ">TPQ</option>
                                            <option value="Admin">Admin</option>
                                        </select>
                                    </div>
                                </div>
                    
                                <div class="col-lg-12">
                                    <div class="hstack gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <!--end col-->
</div>
</form>
<!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/profile-setting.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
@endsection
