@extends('layouts.master-mobile')
@section('title')
    @lang('translation.profile')
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}">
@endsection
@section('content')
@php
    use App\Models\Tunjangan;
    $subjek = Tunjangan::where('Kode', $users->subjek ?? '-' )->first();
@endphp

    <div class="profile-foreground position-relative mx-n4 mt-n5">
        <div class="profile-wid-bg mt-n5">
            <img src="{{ URL::asset('build/images/bc-ali.jpg') }}" alt="" class="profile-wid-img" />
        </div>
    </div>
    
    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper ">
        <div class="row g-4">
            <div class="col-auto">
                <div class="avatar-lg">
                    <img src="{{ URL::asset('build/images/users/' . ($users->avatar ?? 'default.svg')) }}" alt="user-img" class="img-thumbnail rounded-circle" />
                </div>
            </div>
            <!--end col-->
            <div class="col">
                <div class="p-2">
                    <h3 class="text-white mb-1">{{ $users->name ?? '-' }}</h3>
                    <p class="text-white text-opacity-75">{{ $users->ttl ?? '-' }}</p>
                    <div class="hstack text-white-50 gap-1">
                        <div class="me-2"><i
                                class="ri-map-pin-user-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>{{ $users->alamat ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
            @if (session('success'))
                <div class="alert alert-success alert-border-left alert-dismissible fade show material-shadow" role="alert">
                    <i class="ri-check-double-line me-3 align-middle fs-16"></i>
                    <strong>Success</strong> - {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

        </div>
        <!--end row-->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div>
                <div class="d-flex profile-wrapper">
                    <div class="flex-shrink-0">
                        <a href="/edit-profile-aku-ini/ptk/{{ $users->id ?? '-' }}" class="btn btn-success"><i
                                class="ri-edit-box-line align-bottom"></i> Edit Profile</a>
                    </div>
                </div>
                <!-- Tab panes -->
                <div class="tab-content pt-4 text-muted">
                    <div class="tab-pane active" id="overview-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-xxl-3">
                                
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Info</h5>
                                        <div class="table-responsive">
                                            <table class="table table-borderless mb-0">
                                                <tbody style="line-height: 1">
                                                    <tr>
                                                        <th class="ps-0" scope="row">Nama</th>
                                                        <td class="text-muted">: {{ $users->name ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">TTL</th>
                                                        <td class="text-muted">: {{ $users->ttl ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Jenis Kelamin</th>
                                                        <td class="text-muted">: {{ $users->jk ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">No HP</th>
                                                        <td class="text-muted">: {{ $users->no_hp ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Pendidikan</th>
                                                        <td class="text-muted">: {{ $users->pendidikan ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Awal Masuk</th>
                                                        <td class="text-muted">: {{ $users->awal_masuk ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Satuan Kerja</th>
                                                        <td class="text-muted">: {{ $subjek->Subjek ?? '-' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->

                            </div>
                        </div>
                        <!--end row-->
                    </div>
                </div>
                <!--end tab-content-->
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/profile.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
