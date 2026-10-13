@extends('layouts.master-mobile')
@section('title')
    @lang('translation.dashboards')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
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
    use App\Models\JamAbsensi;
    use App\Models\Absensi;
    use App\Models\Location;
    use App\Models\Attendance;
@endphp
@php
    if (!function_exists('GregorianToJD')) {
        function GregorianToJD($m, $d, $y) {
            return (int)((1461 * ($y + 4800 + ($m - 14) / 12)) / 4
                + (367 * ($m - 2 - 12 * (($m - 14) / 12))) / 12
                - (3 * (($y + 4900 + ($m - 14) / 12) / 100)) / 4
                + $d - 32075);
        }
    }

    if (!function_exists('JDToIslamic')) {
        function JDToIslamic($jd) {
            $jd = floor($jd) + 0.5;
            $days = $jd - 1948439.5;
            $cycle = floor($days / 10631);
            $days = $days - ($cycle * 10631);
            $year = floor(($days - 0.1335) / 354.36667);
            $days = $days - floor($year * 354.36667);
            $month = floor(($days + 28.5001) / 29.5);
            if ($month == 13) $month = 12;
            $day = $days - floor(29.5 * $month - 29) + 1;
            $year = $cycle * 30 + $year + 1;
            return array($year, $month, $day);
        }
    }

    if (!function_exists('hijriDate')) {
        function hijriDate($time = null) {
            if ($time === null) $time = time();
            $m = date('m', $time);
            $d = date('d', $time);
            $y = date('Y', $time);
            $jd = GregorianToJD($m, $d, $y);
            $hijri = JDToIslamic($jd);
            $monthNames = array(
                "Muharram", "Safar", "Rabi' al-awwal", "Rabi' ath-thani",
                "Jumada al-awwal", "Jumada ath-thani", "Rajab", "Sha'ban",
                "Ramadan", "Shawwal", "Dhu al-Qadah", "Dhu al-Hijjah"
            );

            $bulanIndex = $hijri[1] - 1; // convert to 0-based index
            $bulanHijriah = isset($monthNames[$bulanIndex]) ? $monthNames[$bulanIndex] : 'Bulan Tidak Dikenal';

            return $hijri[2] . ' ' . $bulanHijriah . ' ' . $hijri[0];
        }
    }
    $masehi = \Carbon\Carbon::now('Asia/Makassar')->locale('id')->translatedFormat('l, d F Y');
    $waktu = \Carbon\Carbon::now('Asia/Makassar')->locale('id')->format('H:i:s');
    $hijriah = hijriDate();
@endphp

<div class="row  d-flex justify-content-center align-items-center layout-width" style="padding:20px; background: linear-gradient(90deg, #429a90, #2c6c66); margin-top:-100px; margin-left:-25px; margin-right:-24px; z-index:-1">
    <div class="row mt-n1" style="box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.8); border-radius: 20px; background-image: url('{{ URL::asset('build/images/bcjam.png') }}'); background-size: cover; color:#fbfbfc;">
        
        <div class="col-6 col-md-6 mt-2"> 
            <p>{{ $masehi }} M</p> 
        </div>
        <div class="col-6 col-md-6 mt-2" style="text-align: right"> 
            <p>{{ $hijriah }} H</p> 
        </div>
        <div class="col-12 col-md-12 mt-2" style="text-align: center"> 
            <p style="font-size: 45pt; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);" id="waktu"></p> 
        </div>
        <div class="col-12 col-md-12 mt-n3" style="text-align: center"> 
            @php
                use Carbon\Carbon;
                $hariIni = Carbon::now('Asia/Makassar')->locale('id')->dayName;
                $jadwalAbsensi = JamAbsensi::where('hari', $hariIni)->first();
                $jamSekarang = Carbon::now('Asia/Makassar');

                $targetTime = null;
                $label = null;

                if ($jadwalAbsensi) {
                    $checkIn = Carbon::createFromFormat('H:i:s', $jadwalAbsensi->check_in ?? '', 'Asia/Makassar');
                    $checkOut = Carbon::createFromFormat('H:i:s', $jadwalAbsensi->check_out ?? '', 'Asia/Makassar');

                    if ($jamSekarang->lt($checkIn)) {
                        $targetTime = $checkIn;
                        $label = "menuju batas waktu masuk";
                    } elseif ($jamSekarang->gt($checkIn->copy()->addHours(2)) && $jamSekarang->lt($checkOut)) {
                        $targetTime = $checkOut;
                        $label = "menuju waktu pulang";
                    }
                }
            @endphp

            @if ($targetTime)
                <p id="waktupulang"></p>
                <script>
                    const targetTime = new Date("{{ $targetTime->format('Y-m-d H:i:s') }}").getTime();

                    const timerInterval = setInterval(function () {
                        const now = new Date().getTime();
                        const distance = targetTime - now;

                        if (distance < 0) {
                            clearInterval(timerInterval);
                            document.getElementById("waktupulang").innerText = "Waktu telah tiba";
                        } else {
                            const hours = Math.floor((distance / (1000 * 60 * 60)));
                            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            const format = `± ${hours.toString().padStart(2, '0')} Jam, ` +
                                        `${minutes.toString().padStart(2, '0')} Menit, {{ $label }}`;

                            document.getElementById("waktupulang").innerText = format;
                        }
                    }, 1000);
                </script>
            @endif

        </div>
    </div> <!-- end col -->

    <div class="row d-flex mt-3 mb-5" style="color: #f3f3f9; text-align:center;">
        <div class="col-12 col-md-12 mt-2"> 
            <p><b>Jadwal Hari Ini</b></p> 
        </div>
        <div class="col-5 col-md-5 mt-n3"> 
            <p>Jam Masuk <i class="ri-login-box-line"></i></p>
            <p class="mt-n3" style="font-size: 15pt;">{{ Carbon::parse($jadwalAbsensi?->check_in)->format('H:i') ?? '-' }}</p>
        </div>
        <div class="col-2 col-md-2 mt-n3">
            <br> 
            <p>•••</p> 
        </div>
        <div class="col-5 col-md-5 mt-n3"> 
            <p><i class="ri-logout-box-line"></i> Jam Pulang</p>
            <p class="mt-n3" style="font-size: 15pt;">{{ Carbon::parse($jadwalAbsensi?->check_out)->format('H:i') ?? '-' }}</p>
        </div>
    </div>

</div>
<div class="row justify-content-center align-items-center layout-width" style="box-shadow: 0px -8px 8px -4px rgba(0, 0, 0, 0.3); padding:20px; border-top-left-radius: 20px; border-top-right-radius: 20px; background: linear-gradient(to bottom, #ffffff, #f3f3f9); margin-top:-50px; margin-left:-25px; margin-right:-24px; z-index:-1">
    @php
    
        $tanggalHariIni = Carbon::now('Asia/Makassar')->toDateString();
        $statusHariIni = \App\Support\HariLibur::status($tanggalHariIni);
        $lokasi = Location::first();
        $jamSekarang = Carbon::now('Asia/Makassar');
    
        $absenMasukStart = null;
        $absenMasukEnd = null;
        $absenPulangStart = null;
        $absenPulangEnd = null;
        $canAbsenMasuk = false;
        $canAbsenPulang = false;
    
        if (!$statusHariIni['libur'] && !empty($statusHariIni['check_in']) && !empty($statusHariIni['check_out'])) {
            $absenMasukStart = Carbon::createFromFormat('H:i:s', $statusHariIni['check_in'], 'Asia/Makassar')->subHours(3);
            $absenMasukEnd = Carbon::createFromFormat('H:i:s', $statusHariIni['check_in'], 'Asia/Makassar')->addHours(2);
    
            $absenPulangStart = Carbon::createFromFormat('H:i:s', $statusHariIni['check_out'], 'Asia/Makassar');
            $absenPulangEnd = Carbon::createFromFormat('H:i:s', $statusHariIni['check_out'], 'Asia/Makassar')->addHours(4);
    
            $canAbsenMasuk = $jamSekarang->between($absenMasukStart, $absenMasukEnd);
            $canAbsenPulang = $jamSekarang->between($absenPulangStart, $absenPulangEnd);
        }
    
        $absenHariIni = Attendance::where('user_id', Auth::id())
                        ->whereDate('date', Carbon::now('Asia/Makassar')->format('Y-m-d'))
                        ->first();
    
        $sudahAbsenMasuk = $absenHariIni?->check_in_time ?? '';
        $sudahAbsenPulang = $absenHariIni?->check_out_time ?? '';
    @endphp



    <div class="col-md-12 mt-5">

        <!-- STATUS -->
        <div id="status-content" style="text-align: center">
            <p class="text-muted fw-semibold">Mendeteksi lokasi Anda...</p>
        </div>

        <!-- TOMBOL ABSEN -->
        <div id="absen-buttons" class="text-center mb-4" style="display: none;">
            
            @if ($canAbsenMasuk && !$sudahAbsenMasuk)
                {{-- Tombol Absensi Masuk --}}
                <button onclick="submitAbsen('masuk')" class="btn btn-success btn-lg rounded-pill px-4">
                    Absensi Masuk 
                </button>
        
            @elseif ($canAbsenPulang && !$sudahAbsenPulang)
                {{-- Tombol Absensi Pulang --}}
                <button onclick="submitAbsen('pulang')" class="btn btn-warning btn-lg rounded-pill px-4">
                    Absensi Pulang 
                </button>
                
            @elseif ($sudahAbsenMasuk && !$sudahAbsenPulang)
                {{-- Modal jika sudah absen masuk, belum absen pulang --}}
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center p-5">
                            <lord-icon 
                                src="https://cdn.lordicon.com/hrqwmuhr.json" 
                                trigger="loop" 
                                colors="primary:#121331,secondary:#08a88a" 
                                style="transform: rotate(180deg); width:120px; height:120px;">
                            </lord-icon>
                            <div class="mt-4">
                                <h6 class="mb-3">Terima kasih sudah melakukan Absensi Masuk</h6>
                            </div>
                        </div>
                    </div>
                </div>
        
            @elseif ($sudahAbsenPulang)
                {{-- Modal jika sudah absen pulang --}}
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center p-5">
                            <lord-icon 
                                src="https://cdn.lordicon.com/hrqwmuhr.json" 
                                trigger="loop" 
                                colors="primary:#121331,secondary:#08a88a" 
                                style="transform: rotate(180deg); width:120px; height:120px;">
                            </lord-icon>
                            <div class="mt-4">
                                <h6 class="mb-3">Terima kasih sudah melakukan Absensi Pulang</h6>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        

        <!-- MODAL HASIL ABSENSI -->
        <div class="modal fade bs-example-modal-center" tabindex="-1" role="dialog" aria-labelledby="absenSuccessLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-5">
                        <lord-icon
                            src="https://cdn.lordicon.com/egmlnyku.json"
                            trigger="loop"
                            colors="primary:#0ab39c,secondary:#405189"
                            style="width:120px;height:120px">
                        </lord-icon>
                        <div class="mt-4">
                            <h4 class="mb-3 text-success">Absensi Berhasil!</h4>
                            <p id="absenSuccessMessage" class="text-muted mb-4">Data absensi Anda sudah kami catat.</p>
                            <div class="hstack gap-2 justify-content-center">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div class="modal fade bs-example-modal-center1" tabindex="-1" role="dialog" aria-labelledby="absenSuccessLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-5">
                        <lord-icon 
                                src="https://cdn.lordicon.com/hrqwmuhr.json" 
                                trigger="loop" 
                                colors="primary:#121331,secondary:#08a88a" 
                                style="width:120px; height:120px;">
                            </lord-icon>
                        <div class="mt-4">
                            <h4 class="mb-3 text-warning">Gagal Melakukan Absensi</h4>
                            <p id="absenSuccessMessage" class="text-muted mb-4">Perangkat ini sudah melakukan absensi dengan akun lain!</p>
                            <div class="hstack gap-2 justify-content-center">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div>

    <!-- SCRIPT -->
    <script>
        const locationRef = {
            lat: {{ $lokasi->latitude ?? 0 }},
            lng: {{ $lokasi->longitude ?? 0 }},
            radius: {{ $lokasi->radius_meter ?? 0 }}
        };

        // Posisi terakhir user (dipakai saat mengirim absensi ke server)
        let posisiAbsen = null;

        // Status hari ini dari server (libur nasional/sekolah/tanggal non-aktif)
        const hariLiburInfo = {
            libur: {{ $statusHariIni['libur'] ? 'true' : 'false' }},
            keterangan: @json($statusHariIni['keterangan'])
        };

        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3;
            const toRad = deg => deg * (Math.PI / 180);
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a = Math.sin(dLat / 2) ** 2 +
                    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                    Math.sin(dLon / 2) ** 2;
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function showStatusHTML(html) {
            document.getElementById('status-content').innerHTML = html;
        }

        navigator.geolocation.getCurrentPosition(function(position) {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            const distance = getDistance(userLat, userLng, locationRef.lat, locationRef.lng);

            posisiAbsen = { latitude: userLat, longitude: userLng };

            if (hariLiburInfo.libur) {
                showStatusHTML(`<p class="text-info fw-bold"><i class="mdi mdi-calendar-remove"></i> Hari ini libur${hariLiburInfo.keterangan ? ': ' + hariLiburInfo.keterangan : ''}. Absensi tidak diperlukan.</p>`);
                document.getElementById('absen-buttons').style.display = 'none';
                return;
            }

            if (distance <= locationRef.radius) {
                showStatusHTML('<p class="text-success"><i class="mdi mdi-map-marker-radius"></i> Anda berada dalam radius absensi.</p>');
                document.getElementById('absen-buttons').style.display = 'block';
            } else {
                showStatusHTML(`
                    <img src="{{ URL::asset('build/images/notlocation.gif') }}" alt="Di luar radius" style="max-height:190px;" class="mt-2">
                    <p class="text-danger"><i class="mdi mdi-map-marker-remove"></i> Anda berada di luar radius absensi!</p>
                `);
            }
        }, function(error) {
            let pesan = '';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    pesan = '❌ Akses lokasi ditolak. Mohon izinkan lokasi.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    pesan = '❌ Informasi lokasi tidak tersedia.';
                    break;
                case error.TIMEOUT:
                    pesan = '❌ Permintaan lokasi melebihi waktu.';
                    break;
                default:
                    pesan = '❌ Terjadi kesalahan dalam mengakses lokasi.';
                    break;
            }

            showStatusHTML(`
                <img src="{{ URL::asset('build/images/notlocation.gif') }}" alt="Gagal akses lokasi" style="max-height:100px;" class="mt-2">
                <p class="text-danger fw-bold">${pesan}</p>
            `);
        });

        function submitAbsen(type) {
            if (!posisiAbsen) {
                document.getElementById('absenSuccessMessage').innerHTML = `
                    <span class="text-danger">❌ Lokasi Anda belum terdeteksi. Aktifkan GPS lalu muat ulang halaman.</span>
                `;
                const modalGagal = new bootstrap.Modal(document.querySelector('.bs-example-modal-center'));
                modalGagal.show();
                return;
            }

            fetch("{{ route('absen.submit') }}", {
                method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    type: type,
                    latitude: posisiAbsen.latitude,
                    longitude: posisiAbsen.longitude
                })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                // Jika status bukan 2xx, tampilkan pesan error dari server
                document.getElementById('absenSuccessMessage').innerHTML = `
                    <span class="text-danger">❌ ${data.message}</span>
                `;
                const modal = new bootstrap.Modal(document.querySelector('.bs-example-modal-center1'));
                modal.show();
                return;
                }
                // Jika sukses
                const status = data.status === 1 ? 
                `<span class="text-danger">Terlambat: <strong>${data.terlambat}</strong></span>` : 
                `<span class="text-success">Tepat waktu</span>`;
                                
                const pesan = `
                Jam: <strong>${data.jam}</strong><br>
                <strong>${data.tanggal}</strong><br>
                ${status}
                `;
                document.getElementById('absenSuccessMessage').innerHTML = pesan;
                const modal = new bootstrap.Modal(document.querySelector('.bs-example-modal-center'));
                modal.show();
            })
            .catch(error => {
                console.error(error);
                document.getElementById('absenSuccessMessage').innerHTML = `
                <span class="text-danger">❌ Gagal terhubung ke server.</span>
                `;
                const modal = new bootstrap.Modal(document.querySelector('.bs-example-modal-center'));
                modal.show();
            });
            }


    </script>


</div>
@endsection
@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/profile.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/dashboard-ecommerce.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>

    <!-- BOOTSTRAP & LORDICON -->
    <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>-->
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <script>
        function updateWaktu() {
            const waktu = new Date(new Date().toLocaleString("en-US", { timeZone: "Asia/Makassar" }));
            const jam = String(waktu.getHours()).padStart(2, '0');
            const menit = String(waktu.getMinutes()).padStart(2, '0');
            const detik = String(waktu.getSeconds()).padStart(2, '0');
            document.getElementById('waktu').innerText = `${jam}:${menit}:${detik}`;
        }
    
        setInterval(updateWaktu, 1000);
        updateWaktu();
    </script>
    
    <script>
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    </script>
@endsection
