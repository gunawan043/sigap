@extends('layouts.master')
@section('title') @lang('translation.basic-tables') @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Kitir Gaji @endslot
@slot('title') Skala Gaji @endslot
@endcomponent

@php
    use App\Models\PersentaseGaji;
    use App\Models\GajiPokok;
    use App\Models\Pendidikan;
    use App\Models\Tunjangan;
    use App\Models\Masakerja;
    use App\Models\SkalaGajiGuru;
    use App\Models\SkalaGajiAdmin;
@endphp

@if (session('success'))
<div class="alert alert-success alert-border-left alert-dismissible fade show material-shadow" role="alert">
    <i class="ri-check-double-line me-3 align-middle fs-16"></i>
    <strong>Success</strong> - {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Skala Gaji Guru</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="listjs-table" id="customerList">

                    <div class="table-responsive table-card mb-1">
                        {{-- <table class="table table-bordered align-middle table-nowrap" id="customerTable" style="line-height: 0.5"> --}}
                        <table class="table align-middle table-nowrap" id="customerTable" style="line-height: 0.5">
                            <thead>
                                <tr style="background: #077e05; color:white">
                                    @php
                                        $PersentaseGaji = PersentaseGaji::find(1);
                                    @endphp
                                    <th style="text-align: center" colspan="2" scope="col">%Naik</th>
                                    <th href="#" style="text-align:center" role="button" id="naik1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGaji->persentase_golongan_1 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPersen" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGaji->persentase_golongan_2 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPersen" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGaji->persentase_golongan_3 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPersen" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGaji->persentase_golongan_4 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPersen" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                </tr>

                                <tr style="background: #01ff34; color:black">
                                    @php
                                        $GajiPokok = GajiPokok::find(1);
                                    @endphp
                                    <th style="text-align: center" colspan="2" scope="col">POKOK</th>
                                    <th href="#" style="text-align:center" role="button" id="pokok1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $GajiPokok->nominal_golongan_1 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPokok" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="pokok2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $GajiPokok->nominal_golongan_2 ?? 'N/A' }} 
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPokok" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="pokok3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $GajiPokok->nominal_golongan_3 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPokok" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="pokok4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $GajiPokok->nominal_golongan_4 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPokok" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                </tr>

                                <tr style="background: #ffc801; color:black">
                                    <th style="text-align: center" scope="col">MK</th>
                                    <th style="text-align: center" scope="col">LV</th>
                                    <th href="#" style="text-align:center" role="button" id="naik1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol1
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol2 
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol3 
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol4 
                                    </th>
                                </tr>

                            </thead>
                            @php
                                $skalagajiguru1 = SkalaGajiGuru::take(6)->get();
                            @endphp
                            <tbody style="text-align: center">
                                @foreach ($skalagajiguru1 as $skg)
                                <tr>
                                    <td>{{$skg->masa_kerja}}</td>
                                    <td>{{$skg->level}}</td>
                                    <td>{{$skg->golongan_1}}</td>
                                    <td>{{$skg->golongan_2}}</td>
                                    <td>{{$skg->golongan_3}}</td>
                                    <td>{{$skg->golongan_4}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <table class="table align-middle table-nowrap" id="customerTable" style="line-height: 0.5">
                            <thead>
                                <tr style="background: #077e05; color:white">
                                    @php
                                        $PersentaseGajiG2 = PersentaseGaji::find(3);
                                    @endphp
                                    <th style="text-align: center" colspan="2" scope="col">%Naik</th>
                                    <th href="#" style="text-align:center" role="button" id="naik1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiG2->persentase_golongan_1 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPersen2" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiG2->persentase_golongan_2 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPersen2" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiG2->persentase_golongan_3 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPersen2" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiG2->persentase_golongan_4 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiGuruPersen2" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                </tr>

                                

                                <tr style="background: #ffc801; color:black">
                                    <th style="text-align: center" scope="col">MK</th>
                                    <th style="text-align: center" scope="col">LV</th>
                                    <th href="#" style="text-align:center" role="button" id="naik1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol1
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol2 
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol3 
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol4 
                                    </th>
                                </tr>

                            </thead>
                            @php
                                $skalagajiguru2 = SkalaGajiGuru::offset(6)->limit(10000)->get();

                            @endphp
                            <tbody style="text-align: center">
                                @foreach ($skalagajiguru2 as $skg)
                                <tr>
                                    <td>{{$skg->masa_kerja}}</td>
                                    <td>{{$skg->level}}</td>
                                    <td>{{$skg->golongan_1}}</td>
                                    <td>{{$skg->golongan_2}}</td>
                                    <td>{{$skg->golongan_3}}</td>
                                    <td>{{$skg->golongan_4}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="noresult" style="display: none">
                            <div class="text-center">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Sorry! No Result Found</h5>
                                <p class="text-muted mb-0">We've searched more than 150+ Orders We did not find any
                                    orders for you search.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end col -->

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Skala Gaji Admin</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="listjs-table" id="customerList">

                    <div class="table-responsive table-card mb-1">
                        {{-- <table class="table table-bordered align-middle table-nowrap" id="customerTable" style="line-height: 0.5"> --}}
                        <table class="table align-middle table-nowrap" id="customerTable" style="line-height: 0.5">
                            <thead>
                                <tr style="background: #077e05; color:white">
                                    @php
                                        $PersentaseGajiA = PersentaseGaji::find(2);
                                    @endphp
                                    <th style="text-align: center" colspan="2" scope="col">%Naik</th>
                                    <th href="#" style="text-align:center" role="button" id="naik1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiA->persentase_golongan_1 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPersen" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiA->persentase_golongan_2 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPersen" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiA->persentase_golongan_3 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPersen" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiA->persentase_golongan_4 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPersen" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                </tr>

                                <tr style="background: #01ff34; color:black">
                                    @php
                                        $GajiPokokA = GajiPokok::find(2);
                                    @endphp
                                    <th style="text-align: center" colspan="2" scope="col">POKOK</th>
                                    <th href="#" style="text-align:center" role="button" id="pokok1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $GajiPokokA->nominal_golongan_1 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPokok" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="pokok2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $GajiPokokA->nominal_golongan_2 ?? 'N/A' }} 
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPokok" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="pokok3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $GajiPokokA->nominal_golongan_3 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPokok" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="pokok4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $GajiPokokA->nominal_golongan_4 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPokok" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                </tr>

                                <tr style="background: #ffc801; color:black">
                                    <th style="text-align: center" scope="col">MK</th>
                                    <th style="text-align: center" scope="col">LV</th>
                                    <th href="#" style="text-align:center" role="button" id="naik1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol1
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol2 
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol3 
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol4 
                                    </th>
                                </tr>

                            </thead>

                            @php
                                $skalagajiAdmin = SkalaGajiAdmin::take(6)->get();
                            @endphp
                            <tbody style="text-align: center">
                                @foreach ($skalagajiAdmin as $skg)
                                <tr>
                                    <td>{{$skg->masa_kerja}}</td>
                                    <td>{{$skg->level}}</td>
                                    <td>{{$skg->golongan_1}}</td>
                                    <td>{{$skg->golongan_2}}</td>
                                    <td>{{$skg->golongan_3}}</td>
                                    <td>{{$skg->golongan_4}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <table class="table align-middle table-nowrap" id="customerTable" style="line-height: 0.5">
                            <thead>
                                <tr style="background: #077e05; color:white">
                                    @php
                                        $PersentaseGajiA2 = PersentaseGaji::find(4);
                                    @endphp
                                    <th style="text-align: center" colspan="2" scope="col">%Naik</th>
                                    <th href="#" style="text-align:center" role="button" id="naik1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiA2->persentase_golongan_1 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPersen2" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiA2->persentase_golongan_2 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPersen2" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiA2->persentase_golongan_3 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPersen2" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">{{ $PersentaseGajiA2->persentase_golongan_4 ?? 'N/A' }}
                                        <div class="dropdown">
                                            <ul class="dropdown-menu" aria-labelledby="naik1">
                                                <li><a type="button" class="btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SkalaGajiAdminPersen2" href="#">Edit</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                </tr>

                                <tr style="background: #ffc801; color:black">
                                    <th style="text-align: center" scope="col">MK</th>
                                    <th style="text-align: center" scope="col">LV</th>
                                    <th href="#" style="text-align:center" role="button" id="naik1" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol1
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik2" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol2 
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik3" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol3 
                                    </th>
                                    <th href="#" style="text-align:center" role="button" id="naik4" data-bs-toggle="dropdown" aria-expanded="false" scope="col">Gol4 
                                    </th>
                                </tr>

                            </thead>

                            @php
                                $skalagajiAdmin2 = SkalaGajiAdmin::offset(6)->limit(10000)->get();
                            @endphp
                            <tbody style="text-align: center">
                                @foreach ($skalagajiAdmin2 as $skg)
                                <tr>
                                    <td>{{$skg->masa_kerja}}</td>
                                    <td>{{$skg->level}}</td>
                                    <td>{{$skg->golongan_1}}</td>
                                    <td>{{$skg->golongan_2}}</td>
                                    <td>{{$skg->golongan_3}}</td>
                                    <td>{{$skg->golongan_4}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="noresult" style="display: none">
                            <div class="text-center">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Sorry! No Result Found</h5>
                                <p class="text-muted mb-0">We've searched more than 150+ Orders We did not find any
                                    orders for you search.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end col -->
</div>
<div class="row">

    <div class="col-xl-6">
    <form method="POST" action="{{ route('tunjangan.update') }}">
        @csrf
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Tunjangan</h4>
                    <div class="flex-shrink-0">
                        <button type="button" id="editButton" class="btn btn-success"><i class="ri-settings-4-line align-bottom me-1 fs-15"></i> Settings</button>
                        <button type="submit" id="saveButton" class="btn btn-primary d-none">Simpan</button>
                    </div>
                </div><!-- end card header -->
        
                <div class="card-body">
                    <div class="live-preview">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle table-nowrap mb-0" style="line-height: 0.5">
                                <thead>
                                    <tr style="background: #ffc801; color:rgb(0, 0, 0)">
                                        <th style="text-align:center">KD</th>
                                        <th style="text-align:center">Subjek</th>
                                        <th style="text-align:center">Nominal</th>
                                    </tr>
                                </thead>
                                @php $tunjangan = \App\Models\Tunjangan::get(); @endphp
                                <tbody>
                                    @foreach ($tunjangan as $tj)
                                    <tr>
                                        <td style="text-align: center">{{ $tj->Kode }}</td>
                                        <td>
                                            <input type="text" name="tunjangan[{{ $tj->id }}][Subjek]" value="{{ $tj->Subjek }}" class="form-control edit-input" readonly>
                                        </td>
                                        <td>
                                            <input type="text" name="tunjangan[{{ $tj->id }}][Nominal]" value="{{ $tj->Nominal }}" class="form-control edit-input text-end" readonly>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!-- end card-body -->
            </div><!-- end card -->
            
        </form>
    </div><!-- end col -->
        
        {{-- Script: aktifkan edit --}}
        <script>
            document.getElementById('editButton').addEventListener('click', function () {
                const inputs = document.querySelectorAll('.edit-input');
                inputs.forEach(input => input.removeAttribute('readonly'));
        
                document.getElementById('saveButton').classList.remove('d-none');
                this.classList.add('d-none'); // sembunyikan tombol setting
            });
        </script>
        

    <div class="col-xl-6">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Pendidikan & Masa Kerja</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="live-preview">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-nowrap mb-0" style="line-height: 0.5">
                            <thead>
                                <tr style="background: #222222; color:rgb(237, 201, 0)">
                                    <th colspan="2" href="#" style="text-align:center" role="button" id="naik1" scope="col">PENDIDIKAN</th>
                                </tr>
                                <tr style="background: #ffc801; color:rgb(0, 0, 0)">
                                    <th href="#" style="text-align:center" role="button" id="naik1" scope="col">Strata</th>
                                    <th href="#" style="text-align:center" role="button" id="naik1" scope="col">Gol PT</th>
                                </tr>
                            </thead>
                            @php
                                $pendidikan = Pendidikan::get();
                                $Masakerja = Masakerja::get();
                            @endphp
                            <tbody style="text-align: center">
                            @foreach ($pendidikan as $pd) 
                            <tr>
                                <td>{{$pd->Strata}}</td>
                                <td>{{$pd->Golongan}}</td>
                            </tr>
                            @endforeach
                            <tr style="background: #222222; color:rgb(237, 201, 0)">
                                <th colspan="2" href="#" style="text-align:center" role="button" id="naik1" scope="col">MASA KERJA</th>
                            </tr>
                            <tr style="background: #ffc801; color:rgb(0, 0, 0)">
                                <th href="#" style="text-align:center" role="button" id="naik1" scope="col">MK</th>
                                <th href="#" style="text-align:center" role="button" id="naik1" scope="col">LV</th>
                            </tr>
                            @foreach ($Masakerja as $mk) 
                            <tr>
                                <td>{{$mk->masa_kerja}}</td>
                                <td>{{$mk->level}}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-none code-view">
                    
                </div>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end col -->
</div>
<!--end row-->

<div class="modal fade" id="SkalaGajiGuruPersen" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit %Naik Skala Gaji Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" action="{{ route('skala-persentase-gaji-guru.update', $PersentaseGaji->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_1" class="form-label">Golongan 1</label>
                        <input type="text" value="{{ $PersentaseGaji->persentase_golongan_1 ?? 'N/A' }}" id="persentase_golongan_1" name="persentase_golongan_1" class="form-control" placeholder="Golongan 1" required />
                        <input type="hidden" value="{{ $PersentaseGaji->id ?? 'N/A' }}" id="idku" name="idku" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_2" class="form-label">Golongan 2</label>
                        <input type="text" value="{{ $PersentaseGaji->persentase_golongan_2 ?? 'N/A' }}" id="persentase_golongan_2" name="persentase_golongan_2" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_3" class="form-label">Golongan 3</label>
                        <input type="text" value="{{ $PersentaseGaji->persentase_golongan_3 ?? 'N/A' }}" id="persentase_golongan_3" name="persentase_golongan_3" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_4" class="form-label">Golongan 4</label>
                        <input type="text" value="{{ $PersentaseGaji->persentase_golongan_4 ?? 'N/A' }}" id="persentase_golongan_4" name="persentase_golongan_4" class="form-control" placeholder="Golongan 1" required />
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Save</button>
                        <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="SkalaGajiGuruPersen2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit %Naik Skala Gaji Guru MK > 5th</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" action="{{ route('skala-persentase-gaji-guru.update', $PersentaseGaji->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_1" class="form-label">Golongan 1</label>
                        <input type="text" value="{{ $PersentaseGajiG2->persentase_golongan_1 ?? 'N/A' }}" id="persentase_golongan_1" name="persentase_golongan_1" class="form-control" placeholder="Golongan 1" required />
                        <input type="hidden" value="{{ $PersentaseGajiG2->id ?? 'N/A' }}" id="idku" name="idku" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_2" class="form-label">Golongan 2</label>
                        <input type="text" value="{{ $PersentaseGajiG2->persentase_golongan_2 ?? 'N/A' }}" id="persentase_golongan_2" name="persentase_golongan_2" class="form-control" placeholder="Golongan 2" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_3" class="form-label">Golongan 3</label>
                        <input type="text" value="{{ $PersentaseGajiG2->persentase_golongan_3 ?? 'N/A' }}" id="persentase_golongan_3" name="persentase_golongan_3" class="form-control" placeholder="Golongan 3" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_4" class="form-label">Golongan 4</label>
                        <input type="text" value="{{ $PersentaseGajiG2->persentase_golongan_4 ?? 'N/A' }}" id="persentase_golongan_4" name="persentase_golongan_4" class="form-control" placeholder="Golongan 4" required />
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Save</button>
                        <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="SkalaGajiGuruPokok" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit Pokok Skala Gaji Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" action="{{ route('skala-pokok-gaji-guru.update', $GajiPokok->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3" id="modal-id" style="">
                        <label for="nominal_golongan_1" class="form-label">Golongan 1</label>
                        <input type="text" value="{{ $GajiPokok->nominal_golongan_1 ?? 'N/A' }}" id="nominal_golongan_1" name="nominal_golongan_1" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="nominal_golongan_2" class="form-label">Golongan 2</label>
                        <input type="text" value="{{ $GajiPokok->nominal_golongan_2 ?? 'N/A' }}" id="nominal_golongan_2" name="nominal_golongan_2" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="nominal_golongan_3" class="form-label">Golongan 3</label>
                        <input type="text" value="{{ $GajiPokok->nominal_golongan_3 ?? 'N/A' }}" id="nominal_golongan_3" name="nominal_golongan_3" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="nominal_golongan_4" class="form-label">Golongan 4</label>
                        <input type="text" value="{{ $GajiPokok->nominal_golongan_4 ?? 'N/A' }}" id="nominal_golongan_4" name="nominal_golongan_4" class="form-control" placeholder="Golongan 1" required />
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Save</button>
                        <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="SkalaGajiAdminPersen" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit %Naik Skala Gaji Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" action="{{ route('skala-persentase-gaji-Admin.update', $PersentaseGajiA->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_1" class="form-label">Golongan 1</label>
                        <input type="text" value="{{ $PersentaseGajiA->persentase_golongan_1 ?? 'N/A' }}" id="persentase_golongan_1" name="persentase_golongan_1" class="form-control" placeholder="Golongan 1" required />
                        <input type="hidden" value="{{ $PersentaseGajiA->id ?? 'N/A' }}" id="idku" name="idku" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_2" class="form-label">Golongan 2</label>
                        <input type="text" value="{{ $PersentaseGajiA->persentase_golongan_2 ?? 'N/A' }}" id="persentase_golongan_2" name="persentase_golongan_2" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_3" class="form-label">Golongan 3</label>
                        <input type="text" value="{{ $PersentaseGajiA->persentase_golongan_3 ?? 'N/A' }}" id="persentase_golongan_3" name="persentase_golongan_3" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_4" class="form-label">Golongan 4</label>
                        <input type="text" value="{{ $PersentaseGajiA->persentase_golongan_4 ?? 'N/A' }}" id="persentase_golongan_4" name="persentase_golongan_4" class="form-control" placeholder="Golongan 1" required />
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Save</button>
                        <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="SkalaGajiAdminPersen2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit %Naik Skala Gaji Admin MK > 5th</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" action="{{ route('skala-persentase-gaji-Admin.update', $PersentaseGajiA->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_1" class="form-label">Golongan 1</label>
                        <input type="text" value="{{ $PersentaseGajiA2->persentase_golongan_1 ?? 'N/A' }}" id="persentase_golongan_1" name="persentase_golongan_1" class="form-control" placeholder="Golongan 1" required />
                        <input type="hidden" value="{{ $PersentaseGajiA2->id ?? 'N/A' }}" id="idku" name="idku" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_2" class="form-label">Golongan 2</label>
                        <input type="text" value="{{ $PersentaseGajiA2->persentase_golongan_2 ?? 'N/A' }}" id="persentase_golongan_2" name="persentase_golongan_2" class="form-control" placeholder="Golongan 2" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_3" class="form-label">Golongan 3</label>
                        <input type="text" value="{{ $PersentaseGajiA2->persentase_golongan_3 ?? 'N/A' }}" id="persentase_golongan_3" name="persentase_golongan_3" class="form-control" placeholder="Golongan 3" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="persentase_golongan_4" class="form-label">Golongan 4</label>
                        <input type="text" value="{{ $PersentaseGajiA2->persentase_golongan_4 ?? 'N/A' }}" id="persentase_golongan_4" name="persentase_golongan_4" class="form-control" placeholder="Golongan 4" required />
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Save</button>
                        <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="SkalaGajiAdminPokok" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit Pokok Skala Gaji Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" action="{{ route('skala-pokok-gaji-Admin.update', $GajiPokokA->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3" id="modal-id" style="">
                        <label for="nominal_golongan_1" class="form-label">Golongan 1</label>
                        <input type="text" value="{{ $GajiPokokA->nominal_golongan_1 ?? 'N/A' }}" id="nominal_golongan_1" name="nominal_golongan_1" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="nominal_golongan_2" class="form-label">Golongan 2</label>
                        <input type="text" value="{{ $GajiPokokA->nominal_golongan_2 ?? 'N/A' }}" id="nominal_golongan_2" name="nominal_golongan_2" class="form-control" placeholder="Golongan 2" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="nominal_golongan_3" class="form-label">Golongan 3</label>
                        <input type="text" value="{{ $GajiPokokA->nominal_golongan_3 ?? 'N/A' }}" id="nominal_golongan_3" name="nominal_golongan_3" class="form-control" placeholder="Golongan 4" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="nominal_golongan_4" class="form-label">Golongan 4</label>
                        <input type="text" value="{{ $GajiPokokA->nominal_golongan_4 ?? 'N/A' }}" id="nominal_golongan_4" name="nominal_golongan_4" class="form-control" placeholder="Golongan 4" required />
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Save</button>
                        <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('script')
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>

<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>

<!-- listjs init -->
<script src="{{ URL::asset('build/js/pages/listjs.init.js') }}"></script>

<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>

<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
