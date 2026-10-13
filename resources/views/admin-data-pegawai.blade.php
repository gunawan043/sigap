@extends('layouts.master')
@section('title') @lang('Data Pegawai') @endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Data @endslot
@slot('title') Data Pegawai @endslot
@endcomponent
@php
    use App\Models\PersentaseGaji;
    use App\Models\GajiPokok;
    use App\Models\Pendidikan;
    use App\Models\Tunjangan;
    use App\Models\Masakerja;
    use App\Models\SkalaGajiGuru;
    use App\Models\SkalaGajiAdmin;
    use App\Models\User;
    use Carbon\Carbon;
@endphp
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Add, Edit & Remove</h4>
            </div><!-- end card header -->

            @if (session('success'))
                <div class="alert alert-success alert-border-left alert-dismissible fade show material-shadow" role="alert">
                    <i class="ri-check-double-line me-3 align-middle fs-16"></i>
                    <strong>Success</strong> - {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card-body">
                <div class="listjs-table" id="customerList">
                    <div class="row g-4 mb-3">
                        <div class="col-sm-auto">
                            <div>
                                <a class="btn btn-success add-btn" href="admin-tambah-pegawai"><i class="ri-add-line align-bottom me-1"></i> Add</a>
                                {{-- <button class="btn btn-soft-danger" onClick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button> --}}
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="d-flex justify-content-sm-end">
                                <div class="search-box ms-2">
                                    <input type="text" class="form-control search" placeholder="Search...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-card mt-3 mb-1">
                        <table class="table align-middle table-bordered table-nowrap" id="">
                            <thead class="table-light">
                                <tr style="text-align: center">
                                    <th class="sort" data-sort="name">ID</th>
                                    <th class="sort" data-sort="name">Nama</th>
                                    <th class="sort" data-sort="ttl">TTL</th>
                                    <th data-sort="jku">JK</th>
                                    <th data-sort="alamat">Alamat</th>
                                    <th data-sort="no_hp">No. HP</th>
                                    <th class="sort" data-sort="awal_masuk">TMT</th>
                                    <th data-sort="pendidikanu">PT</th>
                                    <th data-sort="golpt">Gol PT</th>
                                    <th class="sort" data-sort="gaji">Gaji Pokok</th>
                                    <th data-sort="action">Action</th>
                                </tr>
                            </thead>
                            @php
                            $pegawai = User::where('role', 'pegawai')->get();
                            @endphp
                            <tbody class="list form-check-all">
                                @foreach ($pegawai as $item)
                                @php
                                    $awalMasuk = Carbon::createFromFormat('d M, Y', $item->awal_masuk);
                                    $now = Carbon::now();
                                    $MasakerjaTahun = round($awalMasuk->floatDiffInYears($now));

                                    if ($MasakerjaTahun > 5) {
                                        $MasakerjaLevel = (object)['level' => 'F'];
                                    } else {
                                        $MasakerjaLevel = App\Models\Masakerja::where('masa_kerja', $MasakerjaTahun)->first();
                                    }

                                    $golpt = Pendidikan::where('Strata', $item->pendidikan)->first();
                                    $gaji = SkalaGajiGuru::where('masa_kerja', $MasakerjaTahun)->first();
                                    $golonganAngka = $golpt ? filter_var($golpt->Golongan, FILTER_SANITIZE_NUMBER_INT) : null;
                                    $txtgolingan = 'golongan_'. $golonganAngka;

                                    // Cek induk apakah Yayasan atau TPQ
                                    if (in_array($item->induk, ['Yayasan', 'TPQ'])) {
                                        $nilaiGaji = '0';
                                    } else {
                                        $nilaiGaji = $gaji->{$txtgolingan} ?? '-';
                                    }
                                @endphp


                                <tr>
                                    <td style="text-align: center; display:none;" class="subjek">{{$item->subjek}}</td>
                                    <td style="text-align: center; display:none;" class="induk">{{$item->induk}}</td>
                                    <td class="id"><a href="javascript:void(0);" class="fw-medium link-primary">{{$item->id}}</a></td>
                                    <td class="name"><img class="image avatar-xs rounded-circle" alt="" src="{{ URL::asset('build/images/users/'.$item->avatar) }}"> {{$item->name}}</td>
                                    <td style="text-align: center" class="ttl">{{$item->ttl}}</td>
                                    <td style="text-align: center" class="jku">{{$item->jk}}</td>
                                    <td style="text-align: center" class="alamat">{{$item->alamat}}</td>
                                    <td style="text-align: center" class="no_hp">{{$item->no_hp}}</td>
                                    <td style="text-align: center" class="awal_masuk">{{$item->awal_masuk}}</td>
                                    <td style="text-align: center" class="pendidikanu">{{$item->pendidikan}}</td>
                                    <td style="text-align: center" class="golpt">{{ $golpt ? $golpt->Golongan : '-' }}{{ $MasakerjaLevel ? $MasakerjaLevel->level : '-' }}</td> <!-- <- diperbaiki -->
                                    <td style="text-align: center" class="gaji">
                                        {{ $nilaiGaji }}
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <div class="edit">
                                                <button class="btn btn-sm btn-success edit-item-btn" data-bs-toggle="modal" 
                                                data-bs-target="#PublishGaji"
                                                data-id="{{ $item->id }}" 
                                                data-name="{{ $item->name }}" 
                                                data-alamat="{{ $item->alamat }}" 
                                                data-dana_bos="{{ $item->dana_bos }}" 
                                                data-sertifikasimodal="{{ $item->sertifikasi }}" 
                                                data-no_hp="{{ $item->no_hp }}" 
                                                data-awal_masuk="{{ $item->awal_masuk }}" 
                                                data-ttl="{{ $item->ttl }}" 
                                                data-jk="{{ $item->jk }}"
                                                data-pendidikanmodal="{{ $item->pendidikan }}"
                                                data-induk="{{ $item->induk }}"
                                                data-subjek="{{ $item->subjek }}">Edit</button>
                                            </div>
                                            <div class="remove">
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-danger remove-item-btn" 
                                                    data-id="{{ $item->id }}" 
                                                    data-name="{{ $item->name }}" 
                                                    data-jk="{{ $item->jk }}"
                                                    data-pendidikanmodal="{{ $item->pendidikan }}"
                                                    data-induk="{{ $item->induk }}"
                                                    data-subjek="{{ $item->subjek }}"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteRecordModal">
                                                    Remove
                                                </button>
                                            </div>                                                                                      
                                        </div>
                                    </td>
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

                    <!--<div class="d-flex justify-content-end">-->
                    <!--    <div class="pagination-wrap hstack gap-2">-->
                    <!--        <a class="page-item pagination-prev disabled" href="javascript:void(0);">-->
                    <!--            Previous-->
                    <!--        </a>-->
                    <!--        <ul class="pagination listjs-pagination mb-0"></ul>-->
                    <!--        <a class="page-item pagination-next" href="javascript:void(0);">-->
                    <!--            Next-->
                    <!--        </a>-->
                    <!--    </div>-->
                    <!--</div>-->
                </div>
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end col -->
</div>
<!-- end row -->
<div class="modal fade" id="PublishGaji" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="name" name="name" required placeholder="Nama">
                                        <input type="hidden" class="form-control" id="modal-id" name="id" required placeholder="Nama">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="induk" class="form-label">Jenis Kelamin</label>
                                        <select class="form-control" name="jk"
                                            id="modal-induk2">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="Laki-Laki">Laki-Laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required placeholder="Tempat, Tanggal Lahir">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                        <input type="text" class="form-control" data-provider="flatpickr" id="tanggal_lahir" name="tanggal_lahir" data-date-format="d M, Y" required placeholder="Pilih Tanggal" />
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <input type="text" class="form-control" id="alamat" name="alamat" required placeholder="Alamat">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="no_hp" class="form-label">No. HP</label>
                                        <input type="number" class="form-control" id="no_hp" name="no_hp" required placeholder="Nomor Handphone">
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="awal_masuk" class="form-label">Awal Masuk</label>
                                        <input type="text" class="form-control" data-provider="flatpickr" id="awal_masuk" name="awal_masuk" data-date-format="d M, Y" required placeholder="Pilih Tanggal" />
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    @php
                                    $tunjangan = Tunjangan::get();
                                    @endphp
                                    <div class="mb-3">
                                        <label for="subjek" class="form-label">Subjek</label>
                                        <select class="form-control" name="subjek" id="modal-kj5">
                                            <option value="">Pilih Subjek</option>
                                            @foreach ($tunjangan as $tj)
                                                <option value="{{ $tj->Kode }}">{{ $tj->Kode }} - {{ $tj->Subjek }}</option>
                                            @endforeach
                                        </select>                         
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="induk" class="form-label">Induk</label>
                                        <select class="form-control" name="induk"
                                            id="modal-induk1">
                                            <option value="">Pilih Induk</option>
                                            <option value="Yayasan">Yayasan</option>
                                            <option value="Sekolah">Sekolah</option>
                                            <option value="TPQ">TPQ</option>
                                            <option value="Admin">Admin</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="induk" class="form-label">Pendidikan</label>
                                        <select class="form-control" name="pendidikan"
                                            id="modal-induk">
                                            <option value="">Pilih Pendidikan</option>
                                            <option value="D0">D0</option>
                                            <option value="D1">D1</option>
                                            <option value="D2">D2</option>
                                            <option value="D3">D3</option>
                                            <option value="D4">D4</option>
                                            <option value="S1">S1</option>
                                            <option value="S2">S2</option>
                                            <option value="S3">S3</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="dana_bos" class="form-label">Dana BOS</label>
                                        <input type="number" class="form-control" id="dana_bos" name="dana_bos" required placeholder="Dana BOS" />
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="pendidikan" class="form-label">Memiliki Sertifikasi</label>
                                        <select class="form-control" name="sertifikasi" required
                                            id="modal-induk3">
                                            <tbody>
                                            <option value="">Memiliki Sertifikasi?</option>
                                            <option value="0">Tidak</option>
                                            <option value="1">Iya</option>
                                        </select>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
    
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-item-btn');
        const form = document.getElementById('editForm');

        const choicessubjek = new Choices('#modal-kj5', { shouldSort: false });
        const choicesinduk = new Choices('#modal-induk', { shouldSort: false });
        const choicesinduk1 = new Choices('#modal-induk1', { shouldSort: false });
        const choicesinduk2 = new Choices('#modal-induk2', { shouldSort: false });
        const choicesinduk3 = new Choices('#modal-induk3', { shouldSort: false });

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const Jk = this.dataset.jk;
                const Pendidikan = this.dataset.pendidikanmodal;
                const SertifikasiModal = this.dataset.sertifikasimodal;
                const Subjek = this.dataset.subjek;
                const Induk = this.dataset.induk;
                const ttl = this.dataset.ttl || '';  // ambil dari data-ttl, bukan dari <tr>
                const Name = this.dataset.name;
                const Alamat = this.dataset.alamat;
                const NoHP = this.dataset.no_hp;
                const AwalMasuk = this.dataset.awal_masuk;
                const DanaBos = this.dataset.dana_bos;

                const tanggal_lahir = ttl.slice(-12);
                const tempat_lahir = ttl.slice(0, -12).trim();

                document.getElementById('tempat_lahir').value = tempat_lahir;
                document.getElementById('tanggal_lahir').value = tanggal_lahir;

                document.getElementById('name').value = Name;
                document.getElementById('alamat').value = Alamat;
                document.getElementById('no_hp').value = NoHP;
                document.getElementById('awal_masuk').value = AwalMasuk;
                document.getElementById('dana_bos').value = DanaBos;

                choicessubjek.removeActiveItems();
                choicessubjek.setChoiceByValue(Subjek);

                choicesinduk.removeActiveItems();
                choicesinduk.setChoiceByValue(Pendidikan.trim());
                choicesinduk1.removeActiveItems();
                choicesinduk1.setChoiceByValue(Induk.trim());
                choicesinduk2.removeActiveItems();
                choicesinduk2.setChoiceByValue(Jk.trim());
                choicesinduk3.removeActiveItems();
                choicesinduk3.setChoiceByValue(SertifikasiModal.trim());
                
                form.action = `/admin-update/data-ptk/${id}`;
            });
        });
    });
</script>    

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteForm = document.getElementById('deleteForm');
        const namaTarget = document.getElementById('namaTarget');
        const removeButtons = document.querySelectorAll('.remove-item-btn');

        removeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                // Update form action
                deleteForm.action = `/admin/data-ptk/remove/${id}`;

                // Update nama di modal
                namaTarget.textContent = name;
            });
        });
    });
</script>

<!-- Modal -->
<div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="mt-2 text-center">
                        <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                        <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                            <h4>Hapus Data?</h4>
                            <p class="text-muted mx-4 mb-0">Yakin mau menghapus data <b><span id="namaTarget"></span></b> ini?</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn w-sm btn-danger" id="delete-record">Hapus</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end modal -->
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>

<!-- listjs init -->
<script src="{{ URL::asset('build/js/pages/listjs.init.js') }}"></script>

<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>


@endsection
