@extends('layouts.master')
@section('title') @lang('Rekap Absensi') @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Absensi @endslot
@slot('title') Bulanan @endslot
@endcomponent

@php
    use App\Models\PersentaseGaji;
    use App\Models\GajiPokok;
    use App\Models\Pendidikan;
    use App\Models\User;
    use App\Models\Tunjangan;
    use App\Models\Masakerja;
    use App\Models\SkalaGajiGuru;
    use App\Models\SkalaGajiAdmin;
    use App\Models\JamAbsensi;
    use App\Models\Attendance;
    use Carbon\Carbon;
    $now = Carbon::now();
@endphp

<style>
    .table-responsive table th:first-child,
    .table-responsive table td:first-child {
        position: sticky;
        left: 0;
        background: white;
        z-index: 99;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    .export-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
    }
    .legend-color {
        width: 20px;
        height: 20px;
        display: inline-block;
        border-radius: 4px;
        margin-right: 5px;
        vertical-align: middle;
    }
</style>

@if (session('success'))
<div class="alert alert-success alert-border-left alert-dismissible fade show material-shadow" role="alert">
    <i class="ri-check-double-line me-3 align-middle fs-16"></i>
    <strong>Success</strong> - {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-12">
        <!-- Card Export -->
        <div class="card mb-3">
            <div class="card-body export-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-4">
                            <div>
                                <h6 class="mb-2"><i class="ri-information-line text-primary"></i> Informasi Warna Export:</h6>
                                <div class="d-flex flex-wrap gap-3">
                                    <span>
                                        <span class="legend-color" style="background: #FFC107;"></span>
                                        Kuning = Terlambat
                                    </span>
                                    <span>
                                        <span class="legend-color" style="background: #FD7E14;"></span>
                                        Orange = Tidak Absen Pulang
                                    </span>
                                    <span>
                                        <span class="legend-color" style="background: #28A745;"></span>
                                        Hijau = Hadir Tepat Waktu
                                    </span>
                                    <span>
                                        <span class="legend-color" style="background: transparent; border: 1px solid #6c757d;"></span>
                                        Transparan = Tidak Absen
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-end gap-2">
                            <select class="form-select form-select-sm" id="exportBulan" style="width: 120px;">
                                <option value="1" {{ $now->month == 1 ? 'selected' : '' }}>Januari</option>
                                <option value="2" {{ $now->month == 2 ? 'selected' : '' }}>Februari</option>
                                <option value="3" {{ $now->month == 3 ? 'selected' : '' }}>Maret</option>
                                <option value="4" {{ $now->month == 4 ? 'selected' : '' }}>April</option>
                                <option value="5" {{ $now->month == 5 ? 'selected' : '' }}>Mei</option>
                                <option value="6" {{ $now->month == 6 ? 'selected' : '' }}>Juni</option>
                                <option value="7" {{ $now->month == 7 ? 'selected' : '' }}>Juli</option>
                                <option value="8" {{ $now->month == 8 ? 'selected' : '' }}>Agustus</option>
                                <option value="9" {{ $now->month == 9 ? 'selected' : '' }}>September</option>
                                <option value="10" {{ $now->month == 10 ? 'selected' : '' }}>Oktober</option>
                                <option value="11" {{ $now->month == 11 ? 'selected' : '' }}>November</option>
                                <option value="12" {{ $now->month == 12 ? 'selected' : '' }}>Desember</option>
                            </select>
                            <select class="form-select form-select-sm" id="exportTahun" style="width: 100px;">
                                @for($year = 2020; $year <= $now->year; $year++)
                                    <option value="{{ $year }}" {{ $now->year == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                            <a href="javascript:void(0)" class="btn btn-success btn-sm" id="exportBtn">
                                <i class="ri-download-2-line"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Utama -->
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Log Kehadiran Bulan {{$now->format('F')}}</h4>
            </div><!-- end card header -->
            
            <div class="card-body">
                <div class="table-responsive table-card">
                    @php
                        $now = Carbon::now();
                        $users = User::where('role', 'pegawai')->with(['attendances' => function ($query) use ($now) {
                            $query->whereMonth('date', $now->month)
                                ->whereYear('date', $now->year);
                        }])->get();
                    @endphp

                    <table class="table table-bordered table-nowrap table-centered align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr>
                                <th style="text-align: center; vertical-align: middle" rowspan="2">Nama</th>
                                <th style="text-align: center; background:rgb(255, 190, 25); color:black" scope="col" colspan="2">Total Terlambat</th>
                                <th style="text-align: center; vertical-align: middle; background:rgb(255, 145, 0); color:rgb(0, 0, 0)" scope="col" rowspan="2">Tidak Absen</th>
                                <th style="text-align: center; vertical-align: middle; background:rgb(0, 89, 255); color:rgb(255, 255, 255)" scope="col" rowspan="2">Tepat Waktu</th>
                                <th style="text-align: center; background:rgb(39, 119, 13); color:rgb(255, 255, 255)" scope="col" colspan="{{ $now->daysInMonth }}">Tanggal</th>
                            </tr>
                            <tr>
                                <th scope="col" style="z-index:9; text-align: center; background:rgb(255, 247, 0); color:black">Menit</th>
                                <th scope="col" style="text-align: center; background:rgb(255, 247, 0); color:black">Hari</th>
                                @for ($day = 1; $day <= $now->daysInMonth; $day++)
                                    <th scope="col" style="z-index:9; text-align: center; width: 40px; background:rgb(0, 161, 115); color:rgb(255, 255, 255)">{{ $day }}</th>
                                @endfor
                            </tr>
                        </thead><!-- end thead -->
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $userAttendances = $user->attendances;
                                    $attendanceByDate = $userAttendances->keyBy(function($item) {
                                        return Carbon::parse($item->date)->day;
                                    });
                                    $totalTelat = $userAttendances->sum('selisih_menit');
                                    $totalHariTelat = 0;
                                    $totalTepatWaktu = 0;

                                    foreach ($userAttendances as $attendance) {
                                        if ($attendance->selisih_menit > 0) {
                                            $totalHariTelat++;
                                        } elseif ($attendance->selisih_menit == 0) {
                                            $totalTepatWaktu++;
                                        }
                                    }

                                    $totalTidakMasuk = 0;
                                    $hariIni = $now->day;

                                    for ($day = 1; $day <= $hariIni; $day++) {
                                        $tanggal = Carbon::create($now->year, $now->month, $day);
                                        if ($tanggal->dayOfWeek === Carbon::SUNDAY) {
                                            continue; // skip hari Minggu
                                        }

                                        if (empty($attendanceByDate[$day])) {
                                            $totalTidakMasuk++;
                                        }
                                    }

                                @endphp
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td style="text-align: center">{{ $totalTelat }}</td>
                                    <td style="text-align: center">{{ $totalHariTelat }}</td>
                                    <td style="text-align: center">{{ $totalTidakMasuk }}</td>
                                    <td style="text-align: center">{{ $totalTepatWaktu }}</td>
                                    @for ($day = 1; $day <= $now->daysInMonth; $day++)
                                        <td style="text-align: center">
                                            {{ $attendanceByDate[$day]->check_in_time ?? '-' }} <br>
                                            {{ $attendanceByDate[$day]->check_out_time ?? '-' }}
                                        </td>
                                    @endfor
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $now->daysInMonth + 2 }}" class="text-center">
                                        <div class="noresult" style="">
                                            <div class="text-center">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                                </lord-icon>
                                                <h5 class="mt-2">Data Belum Tersedia!</h5>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        
                    </table><!-- end table -->
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/listjs.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>

<script>
document.getElementById('exportBtn').addEventListener('click', function(e) {
    e.preventDefault();
    
    var bulan = document.getElementById('exportBulan').value;
    var tahun = document.getElementById('exportTahun').value;
    var bulanText = document.querySelector('#exportBulan option:checked').text;

    if (!bulan || !tahun) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Silakan pilih bulan dan tahun terlebih dahulu!',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    // Langsung redirect ke URL export tanpa konfirmasi
    window.location.href = '/export-attendance/' + bulan + '/' + tahun;
});
</script>

<!-- Fallback untuk jika javascript tidak berjalan -->
<noscript>
    <div class="alert alert-warning">
        JavaScript tidak aktif. Gunakan tombol di bawah untuk download:
        <a href="/export-attendance/{{ $now->month }}/{{ $now->year }}" class="btn btn-success btn-sm">Download Excel</a>
    </div>
</noscript>

@endsection