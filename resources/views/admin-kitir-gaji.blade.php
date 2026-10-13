@extends('layouts.master')
@section('title') @lang('translation.basic-tables') @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Kitir @endslot
@slot('title') Gaji Bulanan @endslot
@endcomponent

@php
    use App\Models\PersentaseGaji;
    use App\Models\GajiPokok;
    use App\Models\Pendidikan;
    use App\Models\User;
    use App\Models\Tunjangan;
    use App\Models\MasaKerja;
    use App\Models\SkalaGajiGuru;
    use App\Models\SkalaGajiAdmin;
    use App\Models\JamAbsensi;
    use App\Models\Attendance;
    use App\Models\KitirGaji;
    use App\Models\GajiPerjam;
    use App\Models\Kasbon;
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
    $now = Carbon::now();
    $users = User::all();

    $attendances = Attendance::with('user')
        ->whereMonth('date', $now->month)
        ->whereYear('date', $now->year)
        ->get()
        ->groupBy('user_id'); 
@endphp

@php
$PersentaseGaji = GajiPerjam::find(1);
@endphp
<style>
    .table-responsive table th:first-child,
    .table-responsive table td:first-child {
        position: sticky;
        left: 0;
        background: white;
        z-index: 2;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
</style>
    
<div class="row">
    <div class="col-lg-12">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Kitir Gaji Bulan {{$now->format('F')}}</h4>
                <a type="button" class="justify-content-end" data-bs-toggle="modal" id="create-btn" data-bs-target="#GajiRp" href="#">Rp. Per Jam: {{ $PersentaseGaji->rp_gaji ?? 'N/A' }} <i class="ri-settings-4-line align-bottom me-1 fs-15"></i></a>
            </div><!-- end card header -->
            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-bordered table-nowrap table-centered align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr style="text-align: center">
                                <th>Nama PTK</th>
                                <th>Slip Gaji</th>
                                <th>Induk</th>
                                <th>TMT</th>
                                <th>Jenis Kelamin</th>
                                <th>Golongan PT</th>
                                <th>Gaji Pokok</th>
                                <th>KJ1</th>
                                <th>KJ2</th>
                                <th>KJ3</th>
                                <th>KJ4</th>
                                <th>KJ5</th>
                                <th>T. Jabatan</th>
                                <th>Total Gaji</th>
                                <th>Jam<br>Mengajar</th>
                                <th>Total<br>Rp Jam</th>
                                <th>Insentif<br>Kehadiran</th>
                                <th>Insentif<br>Kedisiplinan</th>
                                {{-- <th>Insentif<br>Kehadiran</th>
                                <th>Uang<br>Lauk Pauk</th> --}}
                                <th>Potongan<br>Kasbon</th>
                                <th>Potongan<br>BOS</th>
                                <th>Potongan<br>Insidentil</th>
                                <th>Total<br>Potongan</th>
                                <th>Total<br>Tanpa Potongan</th>
                                <th>Total Diterima</th>
                            </tr>
                        </thead><!-- end thead -->
                        @php
                            $kitirbulanan = KitirGaji::get();
                        @endphp
                        <tbody>
                            @foreach($kitirbulanan as $ptk)
                            @php
                                $kasbon = Kasbon::where('user_id', $ptk->user_id)->where('lunas', '0')->orderBy('id', 'desc')->first();
                            @endphp
                            <tr>
                                <td>{{ $ptk->nama_ptk }}</td>
                                <td style="text-align: center">
                                    <a href="/detail-gaji/{{ $ptk->user_id }}">
                                        <i class="ri-wallet-line me-1"></i> Slip
                                    </a>
                                </td>
                                <td style="text-align: center">{{ $ptk->induk }}</td>
                                <td style="text-align: center">{{ $ptk->tmt }}</td>
                                <td style="text-align: center">{{ $ptk->jk }}</td>
                                <td style="text-align: center">{{ $ptk->gol_pt }}</td>
                                <td style="text-align: center">{{ $ptk->gaji_pokok }}</td>
                                <td style="text-align: center">
                                    <a href="#" type="button"
                                        class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $ptk->id }}" data-kj1="{{ $ptk->kj1 }}" data-kj2="{{ $ptk->kj2 }}" data-kj3="{{ $ptk->kj3 }}" data-kj4="{{ $ptk->kj4 }}" data-kj5="{{ $ptk->kj5 }}" data-jam_mengajar="{{ $ptk->jam_mengajar }}" data-pot_insidentil="{{ $ptk->pot_insidentil }}">
                                        {{ $ptk->kj1  ?? '-' }}
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <a href="#" type="button"
                                        class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $ptk->id }}" data-kj1="{{ $ptk->kj1 }}" data-kj2="{{ $ptk->kj2 }}" data-kj3="{{ $ptk->kj3 }}" data-kj4="{{ $ptk->kj4 }}" data-kj5="{{ $ptk->kj5 }}" data-jam_mengajar="{{ $ptk->jam_mengajar }}" data-pot_insidentil="{{ $ptk->pot_insidentil }}">
                                        {{ $ptk->kj2  ?? '-' }}
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <a href="#" type="button"
                                        class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $ptk->id }}" data-kj1="{{ $ptk->kj1 }}" data-kj2="{{ $ptk->kj2 }}" data-kj3="{{ $ptk->kj3 }}" data-kj4="{{ $ptk->kj4 }}" data-kj5="{{ $ptk->kj5 }}" data-jam_mengajar="{{ $ptk->jam_mengajar }}" data-pot_insidentil="{{ $ptk->pot_insidentil }}">
                                        {{ $ptk->kj3  ?? '-' }}
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <a href="#" type="button"
                                        class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $ptk->id }}" data-kj1="{{ $ptk->kj1 }}" data-kj2="{{ $ptk->kj2 }}" data-kj3="{{ $ptk->kj3 }}" data-kj4="{{ $ptk->kj4 }}" data-kj5="{{ $ptk->kj5 }}" data-jam_mengajar="{{ $ptk->jam_mengajar }}" data-pot_insidentil="{{ $ptk->pot_insidentil }}">
                                        {{ $ptk->kj4  ?? '-' }}
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <a href="#" type="button"
                                        class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $ptk->id }}" data-kj1="{{ $ptk->kj1 }}" data-kj2="{{ $ptk->kj2 }}" data-kj3="{{ $ptk->kj3 }}" data-kj4="{{ $ptk->kj4 }}" data-kj5="{{ $ptk->kj5 }}" data-jam_mengajar="{{ $ptk->jam_mengajar }}" data-pot_insidentil="{{ $ptk->pot_insidentil }}">
                                        {{ $ptk->kj5  ?? '-' }}
                                    </a>
                                </td>
                                <td style="text-align: center">{{ $ptk->t_jab }}</td>
                                <td style="text-align: center">{{ $ptk->total_gaji }}</td>
                                <td style="text-align: center">
                                    <a href="#" type="button"
                                        class="btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal" 
                                        data-id="{{ $ptk->id }}" 
                                        data-kj1="{{ $ptk->kj1 }}" 
                                        data-kj2="{{ $ptk->kj2 }}" 
                                        data-kj3="{{ $ptk->kj3 }}" 
                                        data-kj4="{{ $ptk->kj4 }}" 
                                        data-kj5="{{ $ptk->kj5 }}" 
                                        data-jam_mengajar="{{ $ptk->jam_mengajar }}" 
                                        data-pot_insidentil="{{ $ptk->pot_insidentil }}">
                                        {{ $ptk->jam_mengajar  ?? '-' }}
                                    </a>
                                </td>
                                <td style="text-align: center">{{ $ptk->total_rp_jam }}</td>
                                <td style="text-align: center">{{ $ptk->insentif_kehadiran }}</td>
                                <td style="text-align: center">{{ $ptk->tunjangan_kinerja }}</td>
                                <!--<td style="text-align: center">{{ $ptk->insentif_kehadiran }}</td>-->
                                <!--<td style="text-align: center">{{ $ptk->uang_lauk_pauk }}</td>-->
                                <td style="text-align: center">{{ $ptk->pot_kasbon }}</td>
                                <td style="text-align: center">{{ $ptk->pot_bos }}</td>
                                <td style="text-align: center">
                                    <a href="#" type="button"
                                        class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $ptk->id }}" data-kj1="{{ $ptk->kj1 }}" data-kj2="{{ $ptk->kj2 }}" data-kj3="{{ $ptk->kj3 }}" data-kj4="{{ $ptk->kj4 }}" data-kj5="{{ $ptk->kj5 }}" data-jam_mengajar="{{ $ptk->jam_mengajar }}" data-pot_insidentil="{{ $ptk->pot_insidentil }}">
                                        {{ $ptk->pot_insidentil  ?? '-' }}
                                    </a>
                                </td>
                                <td style="text-align: center">{{ $ptk->total_potongan }}</td>
                                <td style="text-align: center">
                                    {{ number_format(
                                        (float) str_replace('.', '', $ptk->total_gaji ?? 0) + 
                                        (float) str_replace('.', '', $ptk->total_rp_jam ?? 0) + 
                                        (float) str_replace('.', '', $ptk->tunjangan_kinerja ?? 0) + 
                                        (float) str_replace('.', '', $ptk->uang_lauk_pauk ?? 0), 
                                        0, ',', '.') 
                                    }}
                                </td>                               
                                <td style="text-align: center">
                                    {{ number_format(
                                        ((float) str_replace('.', '', $ptk->total_gaji ?? 0) + 
                                        (float) str_replace('.', '', $ptk->total_rp_jam ?? 0) + 
                                        (float) str_replace('.', '', $ptk->tunjangan_kinerja ?? 0) + 
                                        (float) str_replace('.', '', $ptk->insentif_kehadiran ?? 0) +
                                        (float) str_replace('.', '', $ptk->uang_lauk_pauk ?? 0))-
                                        ((float) str_replace('.', '', $ptk->pot_kasbon ?? 0) +
                                        (float) str_replace('.', '', $ptk->pot_bos ?? 0) +
                                        (float) str_replace('.', '', $ptk->pot_insidentil ?? 0)), 
                                        0, ',', '.') 
                                    }}
                                </td>                               
                            </tr>
                            @endforeach
                        </tbody>
                    </table><!-- end table -->
                </div>

            </div><!-- end card body -->
            <a type="button" class="btn btn-success justify-content-end mt-3 ms-3 me-3 mb-3" data-bs-toggle="modal" id="create-btn" data-bs-target="#PublishGaji" href="#">Publish Gaji</a>
        </div><!-- end card -->
    </div>
</div>

<div class="modal fade" id="GajiRp" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit Rp. Perjam Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" action="{{ route('rp-gaji-guru.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3" id="modal-id" style="">
                        <label for="rp_gaji" class="form-label">Rp/Jam</label>
                        <input type="text" value="{{ $PersentaseGaji->rp_gaji ?? 'N/A' }}" id="rp_gaji" name="rp_gaji" class="form-control" placeholder="Nilai Rp/Jam" required />
                    </div>
                    <div class="mb-3" id="modal-id" style="">
                        <label for="lauk_pauk" class="form-label">Tunjangan Lauk Pauk</label>
                        <input type="text" value="{{ $PersentaseGaji->lauk_pauk ?? 'N/A' }}" id="lauk_pauk" name="lauk_pauk" class="form-control" placeholder="Tunjangan Lauk Pauk" required />
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

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Edit Tunjangan dan Potongan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            @php
            $tunjangan = Tunjangan::get();
            @endphp
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="modal-id">
                    
                    <div class="mb-3">
                        <label for="modal-kj1" class="form-label">Tunjangan 1</label>
                        <select class="form-control" name="kj1" id="modal-kj1">
                            <option value="">Pilih Subjek</option>
                            @foreach ($tunjangan as $tj)
                                <option value="{{ $tj->Kode }}">{{ $tj->Kode }} - {{ $tj->Subjek }}</option>
                            @endforeach
                        </select>                        
                    </div>
                    <div class="mb-3">
                        <label for="modal-kj2" class="form-label">Tunjangan 2</label>
                        <select class="form-control" name="kj2" id="modal-kj2">
                            <option value="">Pilih Subjek</option>
                            @foreach ($tunjangan as $tj)
                                <option value="{{ $tj->Kode }}">{{ $tj->Kode }} - {{ $tj->Subjek }}</option>
                            @endforeach
                        </select>                        
                    </div>
                    <div class="mb-3">
                        <label for="modal-kj3" class="form-label">Tunjangan 3</label>
                        <select class="form-control" name="kj3" id="modal-kj3">
                            <option value="">Pilih Subjek</option>
                            @foreach ($tunjangan as $tj)
                                <option value="{{ $tj->Kode }}">{{ $tj->Kode }} - {{ $tj->Subjek }}</option>
                            @endforeach
                        </select>                        
                    </div>
                    <div class="mb-3">
                        <label for="modal-kj4" class="form-label">Tunjangan 4</label>
                        <select class="form-control" name="kj4" id="modal-kj4">
                            <option value="">Pilih Subjek</option>
                            @foreach ($tunjangan as $tj)
                                <option value="{{ $tj->Kode }}">{{ $tj->Kode }} - {{ $tj->Subjek }}</option>
                            @endforeach
                        </select>                        
                    </div>
                    <div class="mb-3">
                        <label for="modal-kj5" class="form-label">Tunjangan 5</label>
                        <select class="form-control" name="kj5" id="modal-kj5">
                            <option value="">Pilih Subjek</option>
                            @foreach ($tunjangan as $tj)
                                <option value="{{ $tj->Kode }}">{{ $tj->Kode }} - {{ $tj->Subjek }}</option>
                            @endforeach
                        </select>                        
                    </div>
                    <div class="mb-3">
                        <label for="jam_mengajar" class="form-label">Jam Mengajar</label>
                        <input type="text" id="modal-jam_mengajar" name="jam_mengajar" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label for="pot_insidentil" class="form-label">Potongan Insidentil</label>
                        <input type="text" id="modal-pot_insidentil" name="pot_insidentil" class="form-control" />
                    </div>
                    <script>
                        const input = document.getElementById('modal-pot_insidentil');

                        input.addEventListener('input', function(e) {
                            let value = e.target.value.replace(/\D/g, '');
                            value = parseInt(value);
                            if (!isNaN(value)) {
                                e.target.value = new Intl.NumberFormat('id-ID').format(value);
                            } else {
                                e.target.value = '';
                            }
                        });
                    </script>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="PublishGaji" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Publish Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form id="editForm" method="POST" action="{{ route('publish.gaji') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="modal-id">

                    <div class="mb-3">
                        <label for="priode" class="form-label">Priode Gaji</label> 
                        <input type="text" class="form-control" data-provider="flatpickr" id="priode" name="priode" data-date-format="M, Y" required placeholder="Pilih Bulan Priode Gaji" /> 
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    flatpickr("#priode", {
    plugins: [
        new monthSelectPlugin({
            shorthand: true, // "May" instead of "May"
            dateFormat: "M, Y", // output format
            altFormat: "F, Y"
        })
    ]
});

</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.btn-edit');
        const form = document.getElementById('editForm');
    
        // Inisialisasi Choices.js
        const choicesKj1 = new Choices('#modal-kj1', { shouldSort: false });
        const choicesKj2 = new Choices('#modal-kj2', { shouldSort: false });
        const choicesKj3 = new Choices('#modal-kj3', { shouldSort: false });
        const choicesKj4 = new Choices('#modal-kj4', { shouldSort: false });
        const choicesKj5 = new Choices('#modal-kj5', { shouldSort: false });
    
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                const kj1 = this.dataset.kj1;  
                const kj2 = this.dataset.kj2;  
                const kj3 = this.dataset.kj3;  
                const kj4 = this.dataset.kj4;  
                const kj5 = this.dataset.kj5;  
                const jamMengajar = this.dataset.jam_mengajar;
                const potInsidentil = this.dataset.pot_insidentil;
                document.getElementById('modal-jam_mengajar').value = jamMengajar;
                document.getElementById('modal-pot_insidentil').value = potInsidentil;
                choicesKj1.removeActiveItems();
                choicesKj2.removeActiveItems();
                choicesKj3.removeActiveItems();
                choicesKj4.removeActiveItems();
                choicesKj5.removeActiveItems();
                choicesKj1.setChoiceByValue(kj1);
                choicesKj2.setChoiceByValue(kj2);
                choicesKj3.setChoiceByValue(kj3);
                choicesKj4.setChoiceByValue(kj4);
                choicesKj5.setChoiceByValue(kj5);
                const inputId = document.getElementById('modal-id');
                if (inputId) inputId.value = id;
                form.action = `/kitir-gaji-update-guru/${id}`;
            });
        });
    });
</script>
    
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
