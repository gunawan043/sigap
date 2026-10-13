@extends('layouts.master-mobile')
@section('title')
    @lang('translation.dashboards')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col mt-n3">

        <div class="h-100r">
            <div class="row mb-3 pb-1 mt-n5">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">Riwayat Absensi</h4>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form id="dateRangeForm" method="GET">
                                <div class="input-group">
                                    <input type="text" id="rangePicker" name="date_range" class="form-control" 
                                           placeholder="Pilih rentang tanggal" value="{{ request('date_range') }}">
                                    <div class="input-group-text border-primary text-white" style="background: linear-gradient(45deg, #429a90, #2c6c66);">
                                        <i class="ri-calendar-2-line"></i>
                                    </div>
                                </div>
                            </form>
                            
                            <script>
                                // Ambil tanggal awal dan akhir bulan ini
                                const today = new Date();
                                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                                const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                            
                                // Flatpickr init
                                flatpickr("#rangePicker", {
                                    mode: "range",
                                    dateFormat: "Y-m-d", // format yang cocok untuk backend
                                    defaultDate: "{{ request('date_range') }}" ? undefined : [firstDay, lastDay],
                                    onClose: function(selectedDates) {
                                        if (selectedDates.length === 2) {
                                            document.getElementById('dateRangeForm').submit();
                                        }
                                    }
                                });
                            </script>
                            
                        </div>
                    </div><!-- end card header -->
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <div class="row">
                @foreach ($absensi as $item)
                <div class="col-md-12">
                    <!-- card -->
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0"> {{ \Carbon\Carbon::parse($item->date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-2">
                                <div style="text-align: center">
                                    <h5 class="text-success fs-14 mb-0">
                                        <i class="ri-arrow-left-down-line fs-13 align-middle"></i>Check In
                                    </h5>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-2 mt-2">{{$item->check_in_time}}</h4>
                                </div>
                                <div style="text-align: center">
                                    <h5 class="text-danger fs-14 mb-0">
                                        Check Out<i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                                    </h5>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-2 mt-2">{{$item->check_out_time}}</h4>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
                @endforeach
            </div> <!-- end row-->

        </div> <!-- end .h-100-->

    </div> <!-- end col -->

</div>
@endsection
@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/dashboard-ecommerce.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
