@extends('layouts.master')
@section('title') @lang('translation.basic-tables') @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Absensi @endslot
@slot('title') Harian @endslot
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
    use App\Models\Attendance;
    use App\Models\User;
    use Carbon\Carbon;
@endphp

@if (session('success'))
<div class="alert alert-success alert-border-left alert-dismissible fade show material-shadow" role="alert">
    <i class="ri-check-double-line me-3 align-middle fs-16"></i>
    <strong>Success</strong> - {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-border-left alert-dismissible fade show material-shadow" role="alert">
    <i class="ri-error-warning-line me-3 align-middle fs-16"></i>
    <strong>Error</strong> - {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@php
$today = Carbon::today('Asia/Makassar');
$attendances = Attendance::with('user')
    ->whereDate('date', $today)
    ->get();
@endphp

<div class="row">
    <div class="col-lg-12">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Log Kehadiran</h4>
                <div class="d-flex gap-2">
                    <!-- TOMBOL TAMBAH ABSENSI MANUAL -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahAbsensiModal">
                        <i class="ri-add-line"></i> Tambah Absensi Manual
                    </button>
                    
                    <!-- INPUT TANGGAL YANG SUDAH ADA -->
                    <div class="mb-0">
                        <input type="text" class="form-control" data-provider="flatpickr"  value="{{ request('tanggal') }}" id="tanggal" name="tanggal" data-date-format="d M, Y" required placeholder="Pilih Tanggal" />
                    </div>
                </div>
            </div><!-- end card header -->
            
            <script>
                document.getElementById('tanggal').addEventListener('change', function () {
                    const selectedDate = this.value;
                    if (selectedDate) {
                        window.location.href = `?tanggal=${selectedDate}`;
                    }
                });
            </script>

            @php
            $tanggalDipilih = request('tanggal');
            $today = $tanggalDipilih ? Carbon::parse($tanggalDipilih) : Carbon::today('Asia/Makassar');

            $attendances = Attendance::with('user')
                ->whereDate('date', $today)
                ->get();
            @endphp
            
            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-borderless table-nowrap table-centered align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr style="text-align: center">
                                <th scope="col">No</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Check In</th>
                                <th scope="col">Check Out</th>
                                <th scope="col">Terlambat?</th>
                                <th scope="col">Selisih Menit</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead><!-- end thead -->
                        <tbody>
                            @foreach ($attendances as $index => $attendance)
                                <tr>
                                    <td style="text-align: center">{{ $index + 1 }}</td>
                                    <td>{{ $attendance->user->name ?? '-' }}</td>
                                    <td style="text-align: center">{{ $attendance->date }}</td>
                                    
                                    {{-- Check In --}}
                                    <td style="text-align: center">
                                        <span id="display-checkin-{{ $attendance->id }}">{{ $attendance->check_in_time ?? '-' }}</span>
                                        <input 
                                            type="time"
                                            id="input-checkin-{{ $attendance->id }}"
                                            value="{{ $attendance->check_in_time }}"
                                            class="form-control form-control-sm d-none w-auto"
                                            style="display: none; width: 120px; margin: 0 auto;"
                                            step="1"
                                        >
                                    </td>
                                    
                                    {{-- Check Out --}}
                                    <td style="text-align: center">
                                        <span id="display-checkout-{{ $attendance->id }}">{{ $attendance->check_out_time ?? '-' }}</span>
                                        <input 
                                            type="time"
                                            id="input-checkout-{{ $attendance->id }}"
                                            value="{{ $attendance->check_out_time }}"
                                            class="form-control form-control-sm d-none w-auto"
                                            style="display: none; width: 120px; margin: 0 auto;"
                                            step="1"
                                        >
                                    </td>

                                    {{-- Is Late --}}
                                    <td style="text-align: center">
                                        <span id="display-late-{{ $attendance->id }}">{{ $attendance->is_late ? 'Ya' : 'Tidak' }}</span>
                                        <select 
                                            id="input-late-{{ $attendance->id }}" 
                                            class="form-select form-select-sm d-none w-auto"
                                            style="display: none; width: 80px; margin: 0 auto;"
                                        >
                                            <option value="1" {{ $attendance->is_late ? 'selected' : '' }}>Ya</option>
                                            <option value="0" {{ !$attendance->is_late ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    </td>

                                    {{-- Selisih Menit --}}
                                    <td style="text-align: center">
                                        <span id="display-menit-{{ $attendance->id }}">{{ $attendance->selisih_menit ?? '0' }} menit</span>
                                        <input 
                                            type="number"
                                            id="input-menit-{{ $attendance->id }}"
                                            value="{{ $attendance->selisih_menit ?? 0 }}"
                                            class="form-control form-control-sm d-none w-auto"
                                            style="display: none; width: 80px; margin: 0 auto;"
                                            min="0"
                                        >
                                    </td>

                                    {{-- Action Buttons --}}
                                    <td style="text-align: center">
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editAttendance({{ $attendance->id }})" id="edit-btn-{{ $attendance->id }}">Edit</button>
                                        
                                        <form id="form-{{ $attendance->id }}" action="{{ route('attendance.updateManual', $attendance->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            <input type="hidden" name="check_in_time" id="hidden-checkin-{{ $attendance->id }}">
                                            <input type="hidden" name="check_out_time" id="hidden-checkout-{{ $attendance->id }}">
                                            <input type="hidden" name="is_late" id="hidden-late-{{ $attendance->id }}">
                                            <input type="hidden" name="selisih_menit" id="hidden-menit-{{ $attendance->id }}">
                                            <button type="submit" class="btn btn-sm btn-success" id="submit-{{ $attendance->id }}">Simpan</button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEdit({{ $attendance->id }})">Batal</button>
                                        </form>

                                        <!-- TOMBOL HAPUS (MEMBUKA MODAL) -->
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#hapusModal{{ $attendance->id }}">
                                            <i class="ri-delete-bin-line"></i> Hapus
                                        </button>

                                        <!-- MODAL KONFIRMASI HAPUS PER DATA -->
                                        <div class="modal fade" id="hapusModal{{ $attendance->id }}" tabindex="-1" aria-labelledby="hapusModalLabel{{ $attendance->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="hapusModalLabel{{ $attendance->id }}">Konfirmasi Hapus</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Apakah Anda yakin ingin menghapus data absensi berikut?</p>
                                                        <table class="table table-sm table-bordered">
                                                            <tr>
                                                                <th>Nama</th>
                                                                <td>{{ $attendance->user->name ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Tanggal</th>
                                                                <td>{{ $attendance->date }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Check In</th>
                                                                <td>{{ $attendance->check_in_time ?? '-' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Check Out</th>
                                                                <td>{{ $attendance->check_out_time ?? '-' }}</td>
                                                            </tr>
                                                        </table>
                                                        <p class="text-danger"><strong>Data yang dihapus tidak dapat dikembalikan!</strong></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <form action="{{ route('attendance.destroy', $attendance->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody><!-- end tbody -->
                    </table><!-- end table -->
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div>
</div>

<!-- MODAL TAMBAH ABSENSI MANUAL -->
<div class="modal fade" id="tambahAbsensiModal" tabindex="-1" aria-labelledby="tambahAbsensiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahAbsensiModalLabel">Tambah Absensi Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('attendance.storeManual') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Pilih Karyawan <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @php
                                $users = App\Models\User::where('role', '!=', 'admin')->orWhereNull('role')->get();
                            @endphp
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="check_in_time" class="form-label">Check In Time</label>
                            <input type="time" class="form-control" id="check_in_time" name="check_in_time" step="1">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="check_out_time" class="form-label">Check Out Time</label>
                            <input type="time" class="form-control" id="check_out_time" name="check_out_time" step="1">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="is_late" class="form-label">Terlambat?</label>
                            <select class="form-select" id="is_late" name="is_late">
                                <option value="0">Tidak</option>
                                <option value="1">Ya</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="selisih_menit" class="form-label">Selisih Menit</label>
                            <input type="number" class="form-control" id="selisih_menit" name="selisih_menit" value="0" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2" placeholder="Opsional"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="ri-information-line"></i> 
                        Jika check in dan check out tidak diisi, akan dianggap tidak hadir.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/listjs.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>

<script>
    function editAttendance(id) {
        // Sembunyikan semua tampilan teks
        document.getElementById('display-checkin-' + id).style.display = 'none';
        document.getElementById('display-checkout-' + id).style.display = 'none';
        document.getElementById('display-late-' + id).style.display = 'none';
        document.getElementById('display-menit-' + id).style.display = 'none';
        
        // Tampilkan semua input field (hapus class d-none dan set style display)
        document.getElementById('input-checkin-' + id).classList.remove('d-none');
        document.getElementById('input-checkin-' + id).style.display = 'inline-block';
        
        document.getElementById('input-checkout-' + id).classList.remove('d-none');
        document.getElementById('input-checkout-' + id).style.display = 'inline-block';
        
        document.getElementById('input-late-' + id).classList.remove('d-none');
        document.getElementById('input-late-' + id).style.display = 'inline-block';
        
        document.getElementById('input-menit-' + id).classList.remove('d-none');
        document.getElementById('input-menit-' + id).style.display = 'inline-block';
        
        // Sembunyikan tombol edit
        document.getElementById('edit-btn-' + id).style.display = 'none';
        
        // Tampilkan form
        document.getElementById('form-' + id).style.display = 'inline-block';
    }
    
    function cancelEdit(id) {
        // Tampilkan kembali semua tampilan teks
        document.getElementById('display-checkin-' + id).style.display = 'inline-block';
        document.getElementById('display-checkout-' + id).style.display = 'inline-block';
        document.getElementById('display-late-' + id).style.display = 'inline-block';
        document.getElementById('display-menit-' + id).style.display = 'inline-block';
        
        // Sembunyikan semua input field (tambah class d-none dan set style display none)
        document.getElementById('input-checkin-' + id).classList.add('d-none');
        document.getElementById('input-checkin-' + id).style.display = 'none';
        
        document.getElementById('input-checkout-' + id).classList.add('d-none');
        document.getElementById('input-checkout-' + id).style.display = 'none';
        
        document.getElementById('input-late-' + id).classList.add('d-none');
        document.getElementById('input-late-' + id).style.display = 'none';
        
        document.getElementById('input-menit-' + id).classList.add('d-none');
        document.getElementById('input-menit-' + id).style.display = 'none';
        
        // Tampilkan kembali tombol edit
        document.getElementById('edit-btn-' + id).style.display = 'inline-block';
        
        // Sembunyikan form
        document.getElementById('form-' + id).style.display = 'none';
    }
    
    // Event listener untuk form submit
    document.querySelectorAll('form[id^="form-"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const id = this.id.split('-')[1];
            
            // Pindahkan nilai dari input field ke hidden field
            document.getElementById('hidden-checkin-' + id).value = document.getElementById('input-checkin-' + id).value;
            document.getElementById('hidden-checkout-' + id).value = document.getElementById('input-checkout-' + id).value;
            document.getElementById('hidden-late-' + id).value = document.getElementById('input-late-' + id).value;
            document.getElementById('hidden-menit-' + id).value = document.getElementById('input-menit-' + id).value;
        });
    });

    // RESET FORM MODAL TAMBAH SETELAH DITUTUP
    $('#tambahAbsensiModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
</script>
@endsection