@extends('layouts.master')
@section('title') @lang('translation.details') @endsection
@section('content')
@php
    use App\Models\PersentaseGaji;
    use App\Models\Tunjangan;
    use Carbon\Carbon;
@endphp
<div class="row justify-content-center mt-n5" style="">
    <div class="col-xxl-9">
        <div class="col-xxl-9 mt-3">

        <div class="mt-5">
            <div class="card shadow-sm border">
                <div class="card-body p-3">
        
                    <form method="GET" action="{{ route('gajinya', request()->route('id')) }}">
                        <div class="row align-items-center g-2">
        
                            {{-- Label --}}
                            <div class="col-md-4">
                                <label class="fw-semibold text-muted mb-0">
                                    <i class="ri-calendar-2-line me-1"></i>
                                    Periode Gaji
                                </label>
                            </div>
        
                            {{-- Dropdown --}}
                            <div class="col-md-4">
                                <select name="bulan"
                                        class="form-select form-select-sm"
                                        onchange="this.form.submit()">
        
                                    @foreach ($listBulan as $bulan)
                                        <option value="{{ $bulan }}"
                                            {{ request('bulan', $bulanAktif) == $bulan ? 'selected' : '' }}>
                                            {{ $bulan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
        
                        </div>
                    </form>
        
                </div>
            </div>
        </div>
                
        <style>
        @media print {
            body * {
                visibility: hidden;
            }
        
            .print-area,
            .print-area * {
                visibility: visible;
            }
        
            .print-area {
                position: absolute;
                left: 0;
                top: 1;
                width: 100%;
            }
        }
        </style>

        <div class="card print-area mt-3" id="demo">
            <div class="row d-flex justify-content-center">
                <div class="col-lg-12 d-flex justify-content-center">
                    <div class="card-header border-bottom-dashed p-12">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <img src="{{ URL::asset('build/images/sditlogo1.png') }}" class="card-logo card-logo-dark" alt="logo dark" height="90">
                                <img src="{{ URL::asset('build/images/sditlogo1.png') }}" class="card-logo card-logo-light" alt="logo light" height="90">
                            </div>
                        </div>
                    </div>
                    <!--end card-header-->
                </div>
                <div class="col-lg-12" style="text-align:center; margin-top:-20px">
                    <h4>MA'HAD ALI BIN ABI THALIB</h4>
                    <!--end card-header-->
                </div>
                <!--end col-->
                <div class="col-lg-12 d-flex justify-content-between">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-lg-4 col-6">
                                <p class="text-muted mb-2 text-uppercase fw-semibold">Slip No</p>
                                <h5 class="fs-14 mb-0">#VL<span id="invoice-no">{{$kitirbulanan->id ?? ''}}</span></h5>
                            </div>
                            <!--end col-->
                            <div class="col-lg-4 col-6">
                                <p class="text-muted mb-2 text-uppercase fw-semibold">Tanggal</p>
                                <h5 class="fs-14 mb-0">
                                    <span id="invoice-date">
                                      {{ optional(optional($kitirbulanan)->created_at)->isoFormat('DD MMMM YYYY') }}
                                    </span>
                                  </h5>
                            </div>
                            <!--end col-->
                            <div class="col-lg-4 col-6">
                                <p class="text-muted mb-2 text-uppercase fw-semibold">Total Diterima</p>
                                <h5 class="fs-14 mb-0">Rp. <span id="total-amount">{{$kitirbulanan->total_diterima ?? ''}}</span></h5>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end card-body-->
                </div>
                <!--end col-->
                <div class="col-lg-12">
                    <div class="card-body p-4 border-top border-top-dashed">
                        <div class="row g-3">
                            <table>
                                <tbody>
                                    <tr>
                                        <td style="width: 200px;">Nama</td>
                                        <td>: <strong>{{ strtoupper($kitirbulanan->nama_ptk ?? '') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Golongan Pendidikan</td>
                                        <td>: {{$kitirbulanan->gol_pt ?? ''}}</td>
                                    </tr>
                                    @php
                                        $status = Tunjangan::where('Kode', $kitirbulanan->kj1 ?? '')->first();
                                        
                                        // --- LOGIKA SESUAI REKAP KEHADIRAN ---
                                        $bulanAngka = Carbon::parse($bulanAktif)->month;
                                        $tahunAngka = Carbon::parse($bulanAktif)->year;
                                        
                                        // Ambil data attendance untuk user ini di bulan tersebut
                                        $dataAbsensi = App\Models\Attendance::where('user_id', $kitirbulanan?->user_id) // Menggunakan user_id dari kitir
                                                        ->whereMonth('date', $bulanAngka)
                                                        ->whereYear('date', $tahunAngka)
                                                        ->get();
                                    
                                        // Map absensi berdasarkan hari agar mudah dicek dalam loop
                                        $attendanceByDate = $dataAbsensi->keyBy(function($item) {
                                            return Carbon::parse($item->date)->day;
                                        });
                                    
                                        // Hitung total menit telat dari data yang ada
                                        $totalMenitTelat = $dataAbsensi->sum('selisih_menit');
                                    
                                        // Hitung Hari Tidak Masuk dengan Looping (Skip Minggu)
                                        $jumlahTidakAbsen = 0;
                                        
                                        // Tentukan batas hari: Jika bulan ini, hitung sampai hari ini. Jika bulan lalu, hitung sampai akhir bulan.
                                        $hariTerakhir = (Carbon::parse($bulanAktif)->isCurrentMonth()) ? now()->day : Carbon::parse($bulanAktif)->daysInMonth;
                                    
                                        for ($day = 1; $day <= $hariTerakhir; $day++) {
                                            $tanggalCek = Carbon::create($tahunAngka, $bulanAngka, $day);
                                            
                                            // Skip hari Minggu
                                            if ($tanggalCek->dayOfWeek === Carbon::SUNDAY) {
                                                continue;
                                            }
                                    
                                            // Jika tidak ada data di attendanceByDate untuk tanggal ini, hitung sebagai tidak masuk
                                            if (empty($attendanceByDate[$day])) {
                                                $jumlahTidakAbsen++;
                                            }
                                        }
                                    
                                        // Hitung Lupa Pulang (Cek data yang check_in ada tapi check_out kosong)
                                        $jumlahLupaPulang = $dataAbsensi->whereNotNull('check_in_time')->whereNull('check_out_time')->count();
                                    @endphp
                                    <tr>
                                        <td>Status</td>
                                        <td>: {{$status->Subjek ?? ''}}</td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="align-top">Informasi Kehadiran</td>
                                        <td>: 
                                            <span class="badge bg-danger-subtle text-danger border border-danger-semibold">
                                                <i class="ri-time-line align-bottom"></i> {{$totalMenitTelat}} Menit Terlambat
                                            </span>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-semibold ms-1">
                                                <i class="ri-user-unfollow-line align-bottom"></i> {{$jumlahTidakAbsen}} Hari Alfa
                                            </span>
                                            @if($jumlahLupaPulang > 0)
                                            <span class="badge bg-info-subtle text-info border border-info-semibold ms-1">
                                                <i class="ri-logout-box-r-line align-bottom"></i> {{$jumlahLupaPulang}} Tidak Check Out
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end card-body-->
                </div>
                <!--end col-->
                <div class="col-lg-12">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-borderless text-center table-nowrap align-middle mb-0">
                                <thead>
                                    <tr class="table-active">
                                        <th scope="col" style="width: 50px;">#</th>
                                        <th scope="col">Keterangan</th>
                                        <th scope="col">Upah</th>
                                        <th scope="col">Potongan</th>
                                    </tr>
                                </thead>
                                <tbody id="products-list" style="line-height: 0.7;">
                                    <tr>
                                        <th scope="row">01</th>
                                        <td class="text-start">Gaji Pokok</td>
                                        <td>Rp. {{$kitirbulanan->gaji_pokok ?? ''}}</td>
                                        <td class="text-end"></td>
                                    </tr>
                                    @php
                                        $tunjngan1 = Tunjangan::where('Kode', $kitirbulanan->kj1 ?? '')->first();
                                        $tunjngan2 = Tunjangan::where('Kode', $kitirbulanan->kj2 ?? '')->first();
                                        $tunjngan3 = Tunjangan::where('Kode', $kitirbulanan->kj3 ?? '')->first();
                                        $tunjngan4 = Tunjangan::where('Kode', $kitirbulanan->kj4 ?? '')->first();
                                        $tunjngan5 = Tunjangan::where('Kode', $kitirbulanan->kj5 ?? '')->first();
                                        $tunjngan6 = $kitirbulanan->jam_mengajar ?? '';
                                        $tunjngan7 = $kitirbulanan->insentif_kehadiran ?? '';
                                        @endphp

                                        @if($tunjngan1 || $tunjngan2 || $tunjngan3 || $tunjngan4 || $tunjngan5)
                                        <tr class="border-top border-top-dashed">
                                                <th scope="row">02</th>
                                                <td class="text-start">Tunjangan</td>
                                                <td></td>
                                                <td class="text-end"></td>
                                            </tr>
                                        @endif

                                        @if($tunjngan1)
                                            <tr>
                                                <th scope="row"></th>
                                                <td class="text-start">{{$tunjngan1->Kode ?? ''}} - {{$tunjngan1->Subjek ?? ''}}</td>
                                                <td>Rp. {{$tunjngan1->Nominal ?? ''}}</td>
                                                <td class="text-end"></td>
                                            </tr>
                                        @endif

                                        @if($tunjngan2)
                                            <tr>
                                                <th scope="row"></th>
                                                <td class="text-start">{{$tunjngan2->Kode ?? ''}} - {{$tunjngan2->Subjek ?? ''}}</td>
                                                <td>Rp. {{$tunjngan2->Nominal ?? ''}}</td>
                                                <td class="text-end"></td>
                                            </tr>
                                        @endif

                                        @if($tunjngan3)
                                            <tr>
                                                <th scope="row"></th>
                                                <td class="text-start">{{$tunjngan3->Kode ?? ''}} - {{$tunjngan3->Subjek ?? ''}}</td>
                                                <td>Rp. {{$tunjngan3->Nominal ?? ''}}</td>
                                                <td class="text-end"></td>
                                            </tr>
                                        @endif

                                        @if($tunjngan4)
                                            <tr>
                                                <th scope="row"></th>
                                                <td class="text-start">{{$tunjngan4->Kode ?? ''}} - {{$tunjngan4->Subjek ?? ''}}</td>
                                                <td>Rp. {{$tunjngan4->Nominal ?? ''}}</td>
                                                <td class="text-end"></td>
                                            </tr>
                                        @endif

                                        @if($tunjngan5)
                                            <tr>
                                                <th scope="row"></th>
                                                <td class="text-start">{{$tunjngan5->Kode ?? ''}} - {{$tunjngan5->Subjek ?? ''}}</td>
                                                <td>Rp. {{$tunjngan5->Nominal ?? ''}}</td>
                                                <td class="text-end"></td>
                                            </tr>
                                        @endif

                                        @if($tunjngan6)
                                            <tr>
                                                <th scope="row"></th>
                                                <td class="text-start">{{$kitirbulanan->jam_mengajar ?? ''}} Jam Mengajar</td>
                                                <td>Rp. {{$kitirbulanan->total_rp_jam ?? ''}}</td>
                                                <td class="text-end"></td>
                                            </tr>
                                        @endif
                                    

                                    <tr class="border-top border-top-dashed">
                                        <th scope="row">03</th>
                                        <td class="text-start">Insentif Kehadiran</td>
                                        <td>Rp. {{$kitirbulanan->insentif_kehadiran ?? '0'}}</td>
                                        <td class="text-end"></td>
                                    </tr>
                                    
                                    <tr class="border-top border-top-dashed">
                                        <th scope="row">04</th>
                                        <td class="text-start">Tunjangan Kinerja</td>
                                        <td>Rp. {{$kitirbulanan->tunjangan_kinerja ?? '0'}}</td>
                                        <td class="text-end"></td>
                                    </tr>
                                    
                                    {{-- <tr class="border-top border-top-dashed">
                                        <th scope="row">04</th>
                                        <td class="text-start">Uang Lauk Pauk</td>
                                        <td>Rp. {{$kitirbulanan->uang_lauk_pauk ?? '0'}}</td>
                                        <td class="text-end"></td>
                                    </tr> --}}

                                    <tr class="border-top border-top-dashed">
                                        <th scope="row">05</th>
                                        <td class="text-start">Potongan Kasbon</td>
                                        <td></td>
                                        <td class="text-end">Rp. {{$kitirbulanan->pot_kasbon ?? '0'}}</td>
                                    </tr>
                                    
                                    <tr class="border-top border-top-dashed">
                                        <th scope="row">06</th>
                                        <td class="text-start">Honor BOS</td>
                                        <td></td>
                                        <td class="text-end">Rp. {{$kitirbulanan->pot_bos ?? '0'}}</td>
                                    </tr>

                                    <tr class="border-top border-top-dashed">
                                        <th scope="row">07</th>
                                        <td class="text-start">Potongan Insidentel</td>
                                        <td></td>
                                        <td class="text-end">Rp. {{$kitirbulanan->pot_insidentil ?? '0'}}</td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                            <!--end table-->
                        </div>
                        <div class="border-top border-top-dashed mt-2">
                            <table class="table table-borderless table-nowrap align-middle mb-0 ms-auto" style="width:250px">
                                <tbody>
                                    <tr>
                                        <td>Sub Total</td>
                                        <td class="text-end">Rp. {{$kitirbulanan->total_tanpa_potongan ?? ''}}</td>
                                    </tr>
                                    <tr>
                                        <td>Potongan</td>
                                        <td class="text-end">-Rp. {{$kitirbulanan->total_potongan ?? ''}}</td>
                                    </tr>
                                    <tr class="border-top border-top-dashed fs-15">
                                        <th scope="row">Total Diterima</th>
                                        <th class="text-end">Rp. {{$kitirbulanan->total_diterima ?? ''}}</th>
                                    </tr>
                                </tbody>
                            </table>
                            </table>
                            <!--end table-->
                        </div>
                        <div class="mt-4">
                            <div class="alert alert-info">
                                <p class="mb-0"><span class="fw-semibold">NOTES:</span>
                                    <span id="note">Jika terdapat kesalahan silahkan langsung menghubungi bagian Bendahara </span><br><span style="font-style:intalic"><i>Jazakumullah Khairan</i>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="hstack gap-2 justify-content-end d-print-none mt-4">
                            <a href="javascript:window.print()" class="btn btn-success"><i class="ri-printer-line align-bottom me-1"></i> Print</a>
                        </div>
                    </div>
                    <!--end card-body-->
                </div>
                <!--end col-->
            </div>
            <!--end row-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
</div>
<!--end row-->
@endsection
@section('script')
<script src="{{ URL::asset('build/js/pages/invoicedetails.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
    
<script>
    window.onpageshow = function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    };
</script>
@endsection
