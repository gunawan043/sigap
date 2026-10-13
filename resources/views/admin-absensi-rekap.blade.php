@extends('layouts.master')
@section('title') @lang('Rekap Absensi') @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Absensi @endslot
@slot('title') Bulanan @endslot
@endcomponent

@php
    use App\Models\User;
    use App\Models\Absensi;
    use App\Models\ArsipKitir;
    use App\Support\Angka;
    use App\Support\HariLibur;
    use App\Support\InsentifKehadiran;
    use Carbon\Carbon;

    // Filter bulan (format YYYY-MM), default bulan ini
    $bulanParam = request('bulan');

    try {
        $periode = $bulanParam
            ? Carbon::createFromFormat('Y-m', $bulanParam)->startOfMonth()
            : Carbon::now()->startOfMonth();
    } catch (\Throwable $e) {
        $periode = Carbon::now()->startOfMonth();
    }

    $jumlahHari = $periode->daysInMonth;
    $batasHari = $periode->isCurrentMonth() ? min(Carbon::today()->day, $jumlahHari) : $jumlahHari;

    $hariLibur = HariLibur::daftar(
        $periode->copy()->startOfMonth()->toDateString(),
        $periode->copy()->endOfMonth()->toDateString()
    );

    $pengaturan = Absensi::first();
    $labelArsip = $periode->format('M, Y');

    $arsipPeriode = ArsipKitir::where('priode_gaji', $labelArsip)->get()->keyBy(function ($item) {
        return (string) $item->user_id;
    });

    $users = User::where('role', 'pegawai')
        ->with(['attendances' => function ($query) use ($periode) {
            $query->whereMonth('date', $periode->month)
                ->whereYear('date', $periode->year);
        }])
        ->orderBy('name')
        ->get();
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
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex flex-wrap gap-2">
                <h4 class="card-title mb-0 flex-grow-1">Log Kehadiran Bulan {{ $periode->locale('id')->translatedFormat('F Y') }}</h4>
                <form method="GET" action="{{ url('admin-absensi-rekap') }}" class="d-flex align-items-end gap-2">
                    <div>
                        <label class="form-label mb-1 fs-12">Pilih Bulan</label>
                        <input type="month" name="bulan" class="form-control form-control-sm" value="{{ $periode->format('Y-m') }}" max="{{ Carbon::now()->format('Y-m') }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="ri-filter-3-line align-bottom me-1"></i> Tampilkan</button>
                    <a href="{{ url('admin-absensi-rekap') }}" class="btn btn-sm btn-light">Bulan Ini</a>
                </form>
            </div><!-- end card header -->
            <div class="card-body">
                <p class="text-muted fs-13 mb-2">
                    <span class="badge bg-danger-subtle text-danger">L</span> tanggal libur (Minggu / libur nasional / libur sekolah).
                    Kolom <strong>Insentif Hitung</strong> memakai rumus penggajian saat ini, sedangkan
                    <strong>Insentif Arsip</strong> adalah nilai yang sudah dipublish.
                    Tanda <span class="text-danger fw-bold">*</span> berarti keduanya berbeda — publish ulang periode ini agar arsip ikut diperbarui.
                </p>
                <div class="table-responsive table-card">
                    <table class="table table-bordered table-nowrap table-centered align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr>
                                <th style="text-align: center; vertical-align: middle" rowspan="2">Nama</th>
                                <th style="text-align: center; background:rgb(255, 190, 25); color:black" scope="col" colspan="2">Total Terlambat</th>
                                <th style="text-align: center; vertical-align: middle" scope="col" rowspan="2">Lupa Pulang</th>
                                <th style="text-align: center; vertical-align: middle; background:rgb(255, 145, 0); color:rgb(0, 0, 0)" scope="col" rowspan="2">Tidak Absen</th>
                                <th style="text-align: center; vertical-align: middle; background:rgb(0, 89, 255); color:rgb(255, 255, 255)" scope="col" rowspan="2">Tepat Waktu</th>
                                <th style="text-align: center; vertical-align: middle; background:rgb(25, 135, 84); color:rgb(255, 255, 255)" scope="col" rowspan="2">Insentif Hitung</th>
                                <th style="text-align: center; vertical-align: middle; background:rgb(25, 135, 84); color:rgb(255, 255, 255)" scope="col" rowspan="2">Insentif Arsip</th>
                                <th style="text-align: center; background:rgb(39, 119, 13); color:rgb(255, 255, 255)" scope="col" colspan="{{ $jumlahHari }}">Tanggal</th>
                            </tr>
                            <tr>
                                <th scope="col" style="z-index:9; text-align: center; background:rgb(255, 247, 0); color:black">Menit</th>
                                <th scope="col" style="z-index:9; text-align: center; background:rgb(255, 247, 0); color:black">Hari</th>
                                @for ($day = 1; $day <= $jumlahHari; $day++)
                                    <th scope="col" style="z-index:9; text-align: center; width: 40px; background:rgb(0, 161, 115); color:rgb(255, 255, 255)">{{ $day }}</th>
                                @endfor
                            </tr>
                        </thead><!-- end thead -->
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $userAttendances = $user->attendances;
                                    $attendanceByDate = $userAttendances->keyBy(function ($item) {
                                        return (int) Carbon::parse($item->date)->day;
                                    });

                                    $totalTelat = $userAttendances->sum('selisih_menit');
                                    $totalHariTelat = $userAttendances->filter(function ($a) {
                                        return (int) ($a->selisih_menit ?? 0) > 0;
                                    })->count();
                                    $totalTepatWaktu = $userAttendances->filter(function ($a) {
                                        return (int) ($a->selisih_menit ?? 0) === 0 && !empty($a->check_in_time);
                                    })->count();
                                    $totalLupaPulang = $userAttendances->filter(function ($a) {
                                        return !empty($a->check_in_time) && empty($a->check_out_time);
                                    })->count();

                                    $awalMasuk = null;
                                    try {
                                        $awalMasuk = Carbon::createFromFormat('d M, Y', $user->awal_masuk ?? '');
                                    } catch (\Throwable $e) {
                                        $awalMasuk = null;
                                    }

                                    // Tidak absen (alfa) mengikuti aturan penggajian:
                                    // skip Minggu, skip periode libur, skip sebelum awal_masuk.
                                    $totalTidakMasuk = 0;

                                    for ($day = 1; $day <= $batasHari; $day++) {
                                        $tanggal = Carbon::create($periode->year, $periode->month, $day);

                                        if ($tanggal->dayOfWeek === Carbon::SUNDAY) {
                                            continue;
                                        }

                                        if ($awalMasuk && $tanggal->lt($awalMasuk->copy()->startOfDay())) {
                                            continue;
                                        }

                                        if (in_array($tanggal->toDateString(), $hariLibur, true)) {
                                            continue;
                                        }

                                        if (empty($attendanceByDate[$day])) {
                                            $totalTidakMasuk++;
                                        }
                                    }

                                    // Insentif kehadiran (Yayasan/TPQ tidak ikut absensi)
                                    $bukanYayasan = !in_array($user->induk, ['Yayasan', 'TPQ'], true);

                                    $hasilInsentif = $bukanYayasan
                                        ? InsentifKehadiran::hitung($user->id, $awalMasuk, $periode->month, $periode->year, $pengaturan, $hariLibur)
                                        : null;

                                    $insentifArsip = $arsipPeriode->get((string) $user->id)->insentif_kehadiran ?? null;
                                    $angkaArsip = $insentifArsip === null ? null : Angka::rupiah($insentifArsip);
                                    $bedaInsentif = $bukanYayasan && ($angkaArsip === null || (float) $angkaArsip !== (float) $hasilInsentif['insentif']);
                                @endphp
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td style="text-align: center">{{ $totalTelat }}</td>
                                    <td style="text-align: center">{{ $totalHariTelat }}</td>
                                    <td style="text-align: center">{{ $totalLupaPulang }}</td>
                                    <td style="text-align: center">{{ $totalTidakMasuk }}</td>
                                    <td style="text-align: center">{{ $totalTepatWaktu }}</td>
                                    <td style="text-align: center">
                                        @if ($bukanYayasan)
                                            Rp {{ Angka::format($hasilInsentif['insentif']) }}
                                        @else
                                            <span class="text-muted">&mdash;</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center">
                                        @if (!$bukanYayasan)
                                            <span class="text-muted">&mdash;</span>
                                        @elseif ($insentifArsip === null)
                                            <span class="text-muted">(belum ada)</span>
                                        @elseif ($bedaInsentif)
                                            <span class="text-danger fw-bold">Rp {{ Angka::format($angkaArsip) }} *</span>
                                        @else
                                            Rp {{ Angka::format($angkaArsip) }}
                                        @endif
                                    </td>
                                    @for ($day = 1; $day <= $jumlahHari; $day++)
                                        @php
                                            $tglLoop = Carbon::create($periode->year, $periode->month, $day);
                                            $isLibur = in_array($tglLoop->toDateString(), $hariLibur, true) || $tglLoop->dayOfWeek === Carbon::SUNDAY;
                                            $isMasaDepan = $tglLoop->gt(Carbon::today());
                                        @endphp
                                        <td style="text-align: center">
                                            @if ($isLibur)
                                                <span class="text-danger">L</span>
                                            @elseif ($isMasaDepan)
                                                <span class="text-muted">-</span>
                                            @else
                                                {{ $attendanceByDate[$day]->check_in_time ?? '-' }} <br>
                                                {{ $attendanceByDate[$day]->check_out_time ?? '-' }}
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $jumlahHari + 7 }}" class="text-center">
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

<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>

<!-- listjs init -->
<script src="{{ URL::asset('build/js/pages/listjs.init.js') }}"></script>

<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>

<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
