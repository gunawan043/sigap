@extends('layouts.master')
@section('title')
    @lang('')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@php
    use App\Models\PersentaseGaji;
    use App\Models\GajiPokok;
    use App\Models\Pendidikan;
    use App\Models\Tunjangan;
    use App\Models\MasaKerja;
    use App\Models\SkalaGajiGuru;
    use App\Models\SkalaGajiAdmin;
    use App\Models\JamAbsensi;
    use App\Models\User;
    use App\Models\Attendance;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;
@endphp
@php
    $today = Carbon::today();
    $pegawai = User::where('role', 'pegawai')->count();
    $attendances = Attendance::where('is_late', '1')->whereDate('date', $today)->count();
    $tidakhadir = User::where('role', 'pegawai')
    ->whereNotExists(function ($query) use ($today) {
        $query->select('user_id')
            ->from('attendances')
            ->whereDate('date', $today)
            ->whereColumn('attendances.user_id', 'users.id');
    })
    ->count();
    $hariIndonesia = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    $hariIni = $hariIndonesia[Carbon::now('Asia/Makassar')->format('l')];
    $jamAbsensi = JamAbsensi::where('hari', $hariIni)->first();

    $startDate = Carbon::now()->startOfMonth();
    $endDate = Carbon::now()->endOfMonth();


    $dates = collect();
    
    $lateData = collect();
    $absentData = collect();

    $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

    foreach ($period as $date) {
        $tanggal = $date->format('Y-m-d');
        $dates->push($tanggal);

        // Hitung keterlambatan
        $lateCount = Attendance::where('is_late', 1)
            ->whereDate('date', $tanggal)
            ->count();
        $lateData->push($lateCount);

        // Hitung ketidakhadiran
        $absentCount = User::whereNotExists(function ($query) use ($tanggal) {
            $query->select(DB::raw(1))
                ->from('attendances')
                ->whereDate('date', $tanggal)
                ->whereColumn('attendances.user_id', 'users.id');
        })->count();
        $absentData->push($absentCount);
    }
@endphp
    <div class="row">
        <div class="col">

            <div class="h-100">

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0"> Total Pegawai
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                data-target="{{$pegawai}}">0</span> </h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle rounded fs-3">
                                            <i class="bx bx-dollar-circle text-success"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Jumlah Terlambat</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                data-target="{{$attendances}}">0</span></h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle rounded fs-3">
                                            <i class="bx bx-shopping-bag text-info"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Tidak Hadir/Belum Absen</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                data-target="{{$tidakhadir}}">0</span></h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                                            <i class="bx bx-user-circle text-warning"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Jam Kerja</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                            {{ $jamAbsensi ? Carbon::parse($jamAbsensi->check_in ?? '--' )->format('H:i') . '-' . Carbon::parse($jamAbsensi->check_out ?? '--' )->format('H:i') : '--:--' }}
                                        </h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                                            <i class="bx bx-wallet text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                </div> <!-- end row-->

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            @php
                                $bulanIndonesia = [
                                    'January' => 'Januari',
                                    'February' => 'Februari',
                                    'March' => 'Maret',
                                    'April' => 'April',
                                    'May' => 'Mei',
                                    'June' => 'Juni',
                                    'July' => 'Juli',
                                    'August' => 'Agustus',
                                    'September' => 'September',
                                    'October' => 'Oktober',
                                    'November' => 'November',
                                    'December' => 'Desember',
                                ];

                                $namaBulan = $bulanIndonesia[Carbon::now()->format('F')];

                            @endphp 

                            <div class="card-header border-0 align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Grafik Keterlambatan dan Ketidakhadiran Bulan {{$namaBulan}}</h4>
                            </div><!-- end card header -->

                            <div class="card-body p-0 pb-2">
                                <div class="w-100">
                                    <div id="customer_impression_charts" class="apex-charts" dir="ltr"></div>

                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                </div>
            </div> <!-- end .h-100-->

        </div> <!-- end col -->

    </div>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const dates = @json($dates);
        const lateData = @json($lateData);
        const absentData = @json($absentData);

        var options = {
            chart: {
                type: 'line',
                height: 350
            },
            series: [{
                name: 'Terlambat',
                data: lateData
            }, {
                name: 'Tidak Hadir',
                data: absentData
            }],
            xaxis: {
                categories: dates
            },
            colors: ['#f39c12', '#e74c3c'],
            stroke: {
                width: 2
            },
            title: {
                text: '',
                align: 'center'
            }
        };

        var chart = new ApexCharts(document.querySelector("#customer_impression_charts"), options);
        chart.render();
    });
</script>
<script>
    console.log(@json($dates));
    console.log(@json($lateData));
    console.log(@json($absentData));
</script>


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