@extends('layouts.master')
@section('title') @lang('translation.basic-tables') @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Setting @endslot
@slot('title') Absensi @endslot
@endcomponent

@php
    use App\Models\PersentaseGaji;
    use App\Models\GajiPokok;
    use App\Models\Pendidikan;
    use App\Models\Tunjangan;
    use App\Models\Masakerja;
    use App\Models\SkalaGajiGuru;
    use App\Models\SkalaGajiAdmin;
    use App\Models\JamAbsensi;
    use App\Models\Absensi;
    use App\Models\Location;
@endphp

@if (session('success'))
<div class="alert alert-success alert-border-left alert-dismissible fade show material-shadow" role="alert">
    <i class="ri-check-double-line me-3 align-middle fs-16"></i>
    <strong>Success</strong> - {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if (session('warning'))
<div class="alert alert-warning alert-border-left alert-dismissible fade show material-shadow" role="alert">
    <i class="ri-alert-line me-3 align-middle fs-16"></i>
    <strong>Perlu Publish Ulang</strong> - {{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-border-left alert-dismissible fade show material-shadow" role="alert">
    <i class="ri-error-warning-line me-3 align-middle fs-16"></i>
    <strong>Gagal</strong> - {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
<div class="row">
    <div class="col-lg-6">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Locations</h4>
                <div class="flex-shrink-0">
                    <div class="dropdown card-header-dropdown">
                        @if(auth()->user()->role === 'admin')
                        <a type="button" class="text-reset btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#SettingLokasi" href="#"><i class="ri-settings-4-line align-bottom me-1 fs-15"></i> Settings</a>
                        @endif
                    </div>
                </div>
            </div><!-- end card header -->
            @php
                $lokasi = Location::find(1);
                $absensi = Absensi::find(1);
            @endphp

            <div class="card-body p-0">

                <div style="width: 100%">
                    <iframe 
                        width="100%" 
                        height="200" 
                        frameborder="0" 
                        scrolling="no" 
                        marginheight="0" 
                        marginwidth="0" 
                        src="https://maps.google.com/maps?width=100%25&amp;height=400&amp;hl=en&amp;q={{ $lokasi->latitude ?? '-8.569077114369874' }},{{ $lokasi->longitude ?? '116.09690609003769' }}+(Lokasi)&amp;t=&amp;z=15&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
                    </iframe>
                </div>
                
                <div data-simplebar="init" style="max-height: 350px;" class="simplebar-scrollable-y"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content" style="height: auto; overflow: hidden scroll;"><div class="simplebar-content" style="padding: 0px;">
                    <ul class="list-group list-group-flush border-dashed px-3">
                        <li class="list-group-item ps-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <label class="form-check-label mb-0 ps-2" for="task_one">Radius</label>
                                </div>
                                <div class="flex-shrink-0 ms-2">
                                    <p class="text-muted fs-12 mb-0">{{ $lokasi->radius_meter ?? 'N/A' }} Meter</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item ps-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <label class="form-check-label mb-0 ps-2" for="task_two">Latitude</label>
                                </div>
                                <div class="flex-shrink-0 ms-2">
                                    <p class="text-muted fs-12 mb-0">{{ $lokasi->latitude ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item ps-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <label class="form-check-label mb-0 ps-2" for="task_two">Longitude</label>
                                </div>
                                <div class="flex-shrink-0 ms-2">
                                    <p class="text-muted fs-12 mb-0">{{ $lokasi->longitude ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item ps-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <label class="form-check-label mb-0 ps-2" for="task_two">Insentif Kehadiran</label>
                                </div>
                                <div class="flex-shrink-0 ms-2">
                                    <p class="text-muted fs-12 mb-0">Rp. {{ $absensi->tunjangan_kehadiran ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item ps-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <label class="form-check-label mb-0 ps-2" for="task_two">Toleransi Keterlambatan</label>
                                </div>
                                <div class="flex-shrink-0 ms-2">
                                    <p class="text-muted fs-12 mb-0">{{ $absensi->toleransi_keterlambatan ?? 'N/A' }} Menit/Bulan</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item ps-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <label class="form-check-label mb-0 ps-2" for="task_two">Potongan Terlambat/{{ $absensi->toleransi_keterlambatan ? ($absensi->toleransi_keterlambatan / 4) : 'N/A' }} Menit</label>
                                </div>
                                <div class="flex-shrink-0 ms-2">
                                    <p class="text-muted fs-12 mb-0">Rp. {{ $absensi->pot_terlambat ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item ps-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <label class="form-check-label mb-0 ps-2" for="task_two">Potongan Tidak Absen Pulang</label>
                                </div>
                                <div class="flex-shrink-0 ms-2">
                                    <p class="text-muted fs-12 mb-0">{{ $absensi->pot_absen ?? 'N/A' }} Menit</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item ps-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <label class="form-check-label mb-0 ps-2" for="task_two">Insentif Kedisiplinan</label>
                                </div>
                                <div class="flex-shrink-0 ms-2">
                                    <p class="text-muted fs-12 mb-0">Rp. {{ $absensi->tunjangan_harian ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </li>
                        <!--<li class="list-group-item ps-0">-->
                        <!--    <div class="d-flex align-items-start">-->
                        <!--        <div class="flex-grow-1">-->
                        <!--            <label class="form-check-label mb-0 ps-2" for="task_two">Maksimal Keterlambatan/Bulan</label>-->
                        <!--        </div>-->
                        <!--        <div class="flex-shrink-0 ms-2">-->
                        <!--            <p class="text-muted fs-12 mb-0">{{ $absensi->toleransi_keterlambatan ?? 'N/A' }} Menit</p>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</li>-->
                    </ul><!-- end ul -->
                </div></div></div></div><div class="simplebar-placeholder" style="width: 382px; height: 356px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none; transform: translate3d(0px, 0px, 0px);"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: visible;"><div class="simplebar-scrollbar" style="height: 134px; transform: translate3d(0px, 0px, 0px); display: block;"></div></div></div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div>
    <!-- end col -->

    <div class="col-lg-6">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Jam Kerja</h4>
                <div class="flex-shrink-0">
                    <div class="dropdown card-header-dropdown">
                        <a type="button" class="text-reset btn btn-success add-btn dropdown-item" data-bs-toggle="modal" id="create-btn" data-bs-target="#JamKehadiran" href="#">
                            <i class="ri-settings-4-line align-bottom me-1 fs-15"></i> Settings Jam Kerja
                        </a>
                        <a type="button" class="text-reset btn btn-primary add-btn dropdown-item mt-1" data-bs-toggle="modal" data-bs-target="#PengaturanTanggal">
                            <i class="ri-calendar-line align-bottom me-1 fs-15"></i> Settings Tanggal
                        </a>
                        <a type="button" class="text-reset btn btn-danger add-btn dropdown-item mt-1" data-bs-toggle="modal" data-bs-target="#PeriodeLibur">
                            <i class="ri-calendar-close-line align-bottom me-1 fs-15"></i> Periode Libur
                        </a>
                    </div>
                </div>
            </div><!-- end card header -->
            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-borderless table-nowrap table-centered align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr style="text-align: center">
                                <th scope="col">Hari</th>
                                <th scope="col">Check In</th>
                                <th scope="col">Check Out</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead><!-- end thead -->
                        @php
                            $JadwalAbsensi = JamAbsensi::get();
                        @endphp
                        <tbody>
                            @foreach ($JadwalAbsensi as $JA)  
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <label class="form-check-label ms-1" for="checkTask1">
                                            {{$JA->hari}}
                                        </label>
                                    </div>
                                </td>
                                <td style="text-align: center" class="text-muted">{{$JA->check_in}}</td>
                                <td style="text-align: center" class="text-muted">{{$JA->check_out}}</td>
                                <td class="text-center">
                                    @if ($JA->status === 'Active')
                                        <span class="badge bg-success-subtle text-success">
                                            {{ $JA->status }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">
                                            {{ $JA->status }}
                                        </span>
                                    @endif
                                </td>

                            </tr><!-- end -->
                            @endforeach
                        </tbody><!-- end tbody -->
                    </table><!-- end table -->
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div>
</div>

<!-- Modal untuk Pengaturan Tanggal -->
<div class="modal fade" id="PengaturanTanggal" tabindex="-1" aria-labelledby="pengaturanTanggalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="pengaturanTanggalLabel">Pengaturan Tanggal Aktif/Non-Aktif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pengaturan-tanggal.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="non_aktif">Non-Aktif</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Check In (optional untuk non-aktif)</label>
                        <input type="time" name="check_in" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Check Out (optional untuk non-aktif)</label>
                        <input type="time" name="check_out" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal Periode Libur (Nasional / Sekolah) -->
<div class="modal fade" id="PeriodeLibur" tabindex="-1" aria-labelledby="periodeLiburLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="periodeLiburLabel">Periode Libur (Nasional / Sekolah)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.periode-libur.store') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Jenis Libur</label>
                            <select name="jenis" class="form-select" required>
                                <option value="nasional">Libur Nasional</option>
                                <option value="sekolah">Libur Sekolah</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan_libur" class="form-control" placeholder="mis. Maulid Nabi / Libur Semester" required>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-2 fs-13">
                        Tanggal yang sudah lewat tetap boleh diisi. Sistem membuat penanda libur bagi seluruh pegawai
                        sehingga tanggal tersebut tidak lagi dihitung alfa. Jika periode gajinya sudah pernah dipublish,
                        Anda akan diminta Publish Gaji ulang agar insentif kehadiran ikut diperbaiki.
                    </div>
                    <button type="submit" class="btn btn-danger"><i class="ri-add-line align-bottom me-1"></i> Tambah Periode Libur</button>
                </form>

                <hr>

                <form action="{{ route('admin.periode-libur.nasional') }}" method="POST" class="mb-4">
                    @csrf
                    <label class="form-label">Generate Libur Nasional</label>
                    <div class="input-group" style="max-width: 320px;">
                        <input type="number" name="tahun" class="form-control" value="{{ now()->year }}" min="2020" max="2035" required>
                        <button type="submit" class="btn btn-outline-primary">Generate</button>
                    </div>
                    <div class="form-text">Mengambil daftar dari <code>config/hari_libur.php</code>. Mohon verifikasi dengan SKB 3 Menteri terbaru.</div>
                </form>

                <hr>

                <h6 class="mb-2">Periode Libur Tersimpan</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Keterangan</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th style="width:80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($periodeLibur as $libur)
                            <tr>
                                <td>{{ $libur->keterangan_libur ?? '-' }}</td>
                                <td>{{ $libur->tanggal_mulai ? \Carbon\Carbon::parse($libur->tanggal_mulai)->format('d M Y') : '-' }}</td>
                                <td>{{ $libur->tanggal_selesai ? \Carbon\Carbon::parse($libur->tanggal_selesai)->format('d M Y') : '-' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('admin.periode-libur.delete', $libur->id) }}" method="POST" onsubmit="return confirm('Hapus periode libur ini? Penanda kehadiran pada rentang tersebut juga akan dihapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada periode libur.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="JamKehadiran" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit Jam Kerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" action="{{ route('admin-data-jadwal.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @foreach ($JadwalAbsensi as $item)
                        <div class="mb-3" id="modal-id">
                            <label class="form-label fw-semibold">{{ $item->hari }}</label>
                    
                            <div class="row g-2 align-items-end">
                    
                                {{-- Check In --}}
                                <div class="col-md-4">
                                    <label class="form-label">Check In</label>
                                    <input type="time"
                                           value="{{ $item->check_in ?? '' }}"
                                           name="check_in[]"
                                           class="form-control"
                                           required>
                                </div>
                    
                                {{-- Check Out --}}
                                <div class="col-md-4">
                                    <label class="form-label">Check Out</label>
                                    <input type="time"
                                           value="{{ $item->check_out ?? '' }}"
                                           name="check_out[]"
                                           class="form-control"
                                           required>
                                </div>
                    
                                {{-- Status Active / Tidak --}}
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status[]" class="form-select" required>
                                        <option value="Active" {{ $item->status == 'Active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="Nonactive" {{ $item->status == 'Nonactive' ? 'selected' : '' }}>
                                            Nonactive
                                        </option>
                                    </select>
                                </div>
                    
                            </div>
                        </div>
                        <hr>
                    @endforeach

                </div>
            
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="add-btn">Save</button>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
</div>

@if(auth()->user()->role === 'admin')
<div class="modal fade" id="SettingLokasi" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit Data Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" action="{{ route('admin-data-lokasi.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3" id="modal-id" style="">
                        <label for="radius_meter" class="form-label">Radius</label>
                        <input type="text" value="{{ $lokasi->radius_meter ?? 'N/A' }}" id="radius_meter" name="radius_meter" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" value="{{ $lokasi->latitude ?? 'N/A' }}" id="latitude" name="latitude" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" value="{{ $lokasi->longitude ?? 'N/A' }}" id="longitude" name="longitude" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="tunjangan_kehadiran" class="form-label">Insentif Kehadiran</label>
                        <input type="text" value="{{ $absensi->tunjangan_kehadiran ?? 'N/A' }}" id="tunjangan_kehadiran" name="tunjangan_kehadiran" class="form-control" placeholder="Golongan 1" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="toleransi_keterlambatan" class="form-label">Toleransi Keterlambatan</label>
                        <input type="number" value="{{ $absensi->toleransi_keterlambatan ?? 'N/A' }}" id="toleransi_keterlambatan" name="toleransi_keterlambatan" class="form-control" placeholder="Toleransi Keterlambatan Menit/Bulan" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="pot_terlambat" class="form-label">Potongan Terlambat/{{ $absensi->toleransi_keterlambatan ? ($absensi->toleransi_keterlambatan / 4) : 'N/A' }} Menit (Rp.)</label>
                        <input type="number" value="{{ $absensi->pot_terlambat ?? 'N/A' }}" id="pot_terlambat" name="pot_terlambat" class="form-control" placeholder="Potongan Terlambat (Rp.)" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="pot_absen" class="form-label">Potongan Menit Tidak Absen Pulang</label>
                        <input type="number" value="{{ $absensi->pot_absen ?? 'N/A' }}" id="pot_absen" name="pot_absen" class="form-control" placeholder="Potongan Tidak Absen Pulang" required />
                    </div>

                    <div class="mb-3" id="modal-id" style="">
                        <label for="pot_disiplin" class="form-label">Tunjangan Kedisiplinan (Rp.)</label>
                        <input type="text" value="{{ $absensi->tunjangan_harian ?? 'N/A' }}" id="tunjangan_harian" name="tunjangan_harian" class="form-control" placeholder="Tunjangan Kedisiplinan (Rp.)" required />
                    </div>

                    <!--<div class="mb-3" id="modal-id" style="">-->
                    <!--    <label for="toleransi_keterlambatan" class="form-label">Toleransi Keterlambatan</label>-->
                    <!--    <input type="text" value="{{ $absensi->toleransi_keterlambatan ?? 'N/A' }}" id="toleransi_keterlambatan" name="toleransi_keterlambatan" class="form-control" placeholder="Toleransi Keterlambatan" required />-->
                    <!--</div>-->

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
@endif

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
