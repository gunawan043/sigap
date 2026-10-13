@extends('layouts.master')
@section('title') @lang('Kasbon') @endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Data @endslot
@slot('title') Data Kasbon @endslot
@endcomponent

@php
    use App\Models\PersentaseGaji;
    use App\Models\GajiPokok;
    use App\Models\Kasbon;
    use App\Models\Tunjangan;
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
    $now = Carbon::now();
    $users = User::where('role', 'pegawai')->get();
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

    .table-responsive table th:last-child,
    .table-responsive table td:last-child {
        position: sticky;
        right: 0;
        background: white;
        z-index: 2;
        box-shadow: -2px 0 5px rgba(0,0,0,0.1);
    }
</style>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Add, Edit & Remove</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="listjs-table" id="customerList">
                    <div class="row g-4 mb-3">
                        <div class="col-sm-auto">
                            <div>
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal" id="create-btn" data-bs-target="#showModal"><i class="ri-add-line align-bottom me-1"></i> Add</button>
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
                        <table class="table align-middle table-nowrap" id="customerTable">
                            <thead class="table-light">
                                <tr style="text-align: center">
                                    <th data-sort="Nama">Nama</th>
                                    <th data-sort="Nominal">Nominal</th>
                                    <th data-sort="TanggalPinjam">Tanggal<br>Pinjam</th>
                                    <th data-sort="JumlahAngsuran">Jumlah<br>Angsuran</th>
                                    <th data-sort="LamaAngsuran">Lama<br>Angsuran</th>
                                    <th data-sort="TerakhirAngsuran">Terakhir<br>Angsuran</th>
                                    <th data-sort="Angsuran1">1</th>
                                    <th data-sort="Angsuran2">2</th>
                                    <th data-sort="Angsuran3">3</th>
                                    <th data-sort="Angsuran4">4</th>
                                    <th data-sort="Angsuran5">5</th>
                                    <th data-sort="Angsuran6">6</th>
                                    <th data-sort="Angsuran7">7</th>
                                    <th data-sort="Angsuran8">8</th>
                                    <th data-sort="Angsuran9">9</th>
                                    <th data-sort="Angsuran10">10</th>
                                    <th data-sort="Angsuran11">11</th>
                                    <th data-sort="Angsuran12">12</th>
                                    <th data-sort="Angsuran13">13</th>
                                    <th data-sort="Angsuran14">14</th>
                                    <th data-sort="Angsuran15">15</th>
                                    <th data-sort="Angsuran16">16</th>
                                    <th data-sort="Angsuran17">17</th>
                                    <th data-sort="Angsuran18">18</th>
                                    <th data-sort="Angsuran19">19</th>
                                    <th data-sort="Angsuran20">20</th>
                                    <th data-sort="Angsuran21">21</th>
                                    <th data-sort="Angsuran22">22</th>
                                    <th data-sort="Angsuran23">23</th>
                                    <th data-sort="Angsuran24">24</th>
                                    <th data-sort="Angsuran25">25</th>
                                    <th data-sort="Angsuran26">26</th>
                                    <th data-sort="Angsuran27">27</th>
                                    <th data-sort="Angsuran28">28</th>
                                    <th data-sort="Angsuran29">29</th>
                                    <th data-sort="Angsuran30">30</th>
                                    <th data-sort="action">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @foreach ($users as $usr)
                                @php
                                    $kasbon = Kasbon::where('user_id', $usr->id)->orderBy('id', 'desc')->first();
                                @endphp
                                <tr>
                                    <td class="Nama"><img class="image avatar-xs rounded-circle" alt="" src="{{ URL::asset('build/images/users/' . $usr->avatar) }}"> {{$usr->name}}</td>
                                    <td class="id" style="display:none;"><a href="javascript:void(0);" class="fw-medium link-primary"></a></td>
                                    <td class="Nominal">{{ $kasbon->nominal ?? '' }}</td>
                                    <td class="TanggalPinjam">{{ $kasbon->tanggal_pinjaman ?? '' }}</td>
                                    <td class="AngsuranPerbulan">{{ $kasbon->angsuran_perbulan ?? '' }}</td>
                                    <td class="LamaAngsuran">{{ $kasbon->lama_angsuran ?? '' }}</td>
                                    <td class="TerakhirAngsuran">{{ $kasbon->terakhir_angsuran ?? '' }}</td>
                                    <td class="Angsuran1">{{ $kasbon->angsuran_ke_1 ?? '' }}</td>
                                    <td class="Angsuran2">{{ $kasbon->angsuran_ke_2 ?? '' }}</td>
                                    <td class="Angsuran3">{{ $kasbon->angsuran_ke_3 ?? '' }}</td>
                                    <td class="Angsuran4">{{ $kasbon->angsuran_ke_4 ?? '' }}</td>
                                    <td class="Angsuran5">{{ $kasbon->angsuran_ke_5 ?? '' }}</td>
                                    <td class="Angsuran6">{{ $kasbon->angsuran_ke_6 ?? '' }}</td>
                                    <td class="Angsuran7">{{ $kasbon->angsuran_ke_7 ?? '' }}</td>
                                    <td class="Angsuran8">{{ $kasbon->angsuran_ke_8 ?? '' }}</td>
                                    <td class="Angsuran9">{{ $kasbon->angsuran_ke_9 ?? '' }}</td>
                                    <td class="Angsuran10">{{ $kasbon->angsuran_ke_10 ?? '' }}</td>
                                    <td class="Angsuran11">{{ $kasbon->angsuran_ke_11 ?? '' }}</td>
                                    <td class="Angsuran12">{{ $kasbon->angsuran_ke_12 ?? '' }}</td>
                                    <td class="Angsuran13">{{ $kasbon->angsuran_ke_13 ?? '' }}</td>
                                    <td class="Angsuran14">{{ $kasbon->angsuran_ke_14 ?? '' }}</td>
                                    <td class="Angsuran15">{{ $kasbon->angsuran_ke_15 ?? '' }}</td>
                                    <td class="Angsuran16">{{ $kasbon->angsuran_ke_16 ?? '' }}</td>
                                    <td class="Angsuran17">{{ $kasbon->angsuran_ke_17 ?? '' }}</td>
                                    <td class="Angsuran18">{{ $kasbon->angsuran_ke_18 ?? '' }}</td>
                                    <td class="Angsuran19">{{ $kasbon->angsuran_ke_19 ?? '' }}</td>
                                    <td class="Angsuran20">{{ $kasbon->angsuran_ke_20 ?? '' }}</td>
                                    <td class="Angsuran21">{{ $kasbon->angsuran_ke_21 ?? '' }}</td>
                                    <td class="Angsuran22">{{ $kasbon->angsuran_ke_22 ?? '' }}</td>
                                    <td class="Angsuran23">{{ $kasbon->angsuran_ke_23 ?? '' }}</td>
                                    <td class="Angsuran24">{{ $kasbon->angsuran_ke_24 ?? '' }}</td>
                                    <td class="Angsuran25">{{ $kasbon->angsuran_ke_25 ?? '' }}</td>
                                    <td class="Angsuran26">{{ $kasbon->angsuran_ke_26 ?? '' }}</td>
                                    <td class="Angsuran27">{{ $kasbon->angsuran_ke_27 ?? '' }}</td>
                                    <td class="Angsuran28">{{ $kasbon->angsuran_ke_28 ?? '' }}</td>
                                    <td class="Angsuran29">{{ $kasbon->angsuran_ke_29 ?? '' }}</td>
                                    <td class="Angsuran30">{{ $kasbon->angsuran_ke_30 ?? '' }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <div class="edit">
                                                <button class="btn btn-sm btn-success edit-item-btn" data-bs-toggle="modal" data-bs-target="#showModal">Tambah</button>
                                            </div>
                                            <div class="remove">
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-danger remove-item-btn" 
                                                    data-id="{{ $kasbon->id ?? '' }}" 
                                                    data-name="{{$usr->name}}" 
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

                    <div class="d-flex justify-content-end">
                        <div class="pagination-wrap hstack gap-2">
                            <a class="page-item pagination-prev disabled" href="javascript:void(0);">
                                Previous
                            </a>
                            <ul class="pagination listjs-pagination mb-0"></ul>
                            <a class="page-item pagination-next" href="javascript:void(0);">
                                Next
                            </a>
                        </div>
                    </div>
                </div>
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end col -->
</div>
<!-- end row -->

<div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5>Tambah Kasbon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form method="POST" action="/admin-post/kasbon" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3" id="modal-id" style="display: none;">
                        <label for="id-field" class="form-label">ID</label>
                        <input type="text" id="id-field" class="form-control" placeholder="ID" readonly />
                    </div>

                    <div>
                        <div class="mb-3">
                            <label for="subjek" class="form-label">Nama</label>
                            <select class="form-control" data-choices1 name="user_id"
                                id="user_id" required>
                                <option value="">Pilih Nama</option>
                                @foreach ($users as $usr)
                                <option value="{{$usr->id}}">{{$usr->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nominal" class="form-label">Nominal</label>
                        <input type="text" id="nominal" name="nominal" class="form-control" placeholder="Masukan Nominal" required />
                        <div class="invalid-feedback">Harap masukan nominal</div>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam</label>
                        <input type="text" class="form-control" data-provider="flatpickr" id="tanggal_pinjam" name="tanggal_pinjam" data-date-format="d M, Y" required placeholder="Pilih Tanggal" />
                        <div class="invalid-feedback">Harap masukan Tanggal Pinjam</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="jumlah_angsuran" class="form-label">Jumlah Angsuran</label>
                        <input type="text" id="jumlah_angsuran" name="jumlah_angsuran" class="form-control" placeholder="Masukan Jumlah Angsuran" required />
                        <div class="invalid-feedback">Harap masukan Jumlah Angsuran</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="lama_angsuran" class="form-label">Lama Angsuran</label>
                        <input type="text" id="lama_angsuran" name="lama_angsuran" class="form-control" placeholder="Masukan Lama Angsuran" required  readonly/>
                        <div class="invalid-feedback">Harap masukan Lama Angsuran</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="terakhir_angsuran" class="form-label">Terakhir Angsuran</label>
                        <input type="number" class="form-control" data-provider="flatpickr" id="terakhir_angsuran" name="terakhir_angsuran" data-date-format="d M, Y" required placeholder="Pilih Tanggal"  readonly/>
                        <div class="invalid-feedback">Harap masukan Tanggal Pinjam</div>
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
                deleteForm.action = `/kasbon/${id}`;

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
                            <p class="text-muted mx-4 mb-0">Yakin mau menghapus data Kasbon <b><span id="namaTarget"></span></b> ini?</p>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nominalInput = document.getElementById('nominal');
        const jumlahAngsuranInput = document.getElementById('jumlah_angsuran');
        const lamaAngsuranInput = document.getElementById('lama_angsuran');
        const tanggalPinjamInput = document.getElementById('tanggal_pinjam');
        const terakhirAngsuranInput = document.getElementById('terakhir_angsuran');
    
        const formatter = new Intl.NumberFormat('id-ID');
    
        function formatRupiah(angka) {
            return formatter.format(angka);
        }
    
        function hitungLamaAngsuran() {
            const nominal = parseFloat(nominalInput.value.replace(/\./g, ''));
            const jumlahAngsuran = parseFloat(jumlahAngsuranInput.value.replace(/\./g, ''));
    
            if (!isNaN(nominal) && !isNaN(jumlahAngsuran) && jumlahAngsuran > 0) {
                const lamaAngsuran = Math.floor(nominal / jumlahAngsuran);
                lamaAngsuranInput.value = formatRupiah(lamaAngsuran);
            } else {
                lamaAngsuranInput.value = '';
            }
        }
    
        function hitungTerakhirAngsuran() {
          const tanggalPinjam = tanggalPinjamInput.value;
          const lamaAngsuran = parseInt(lamaAngsuranInput.value.replace(/\D/g, ''));
          
          if (tanggalPinjam && !isNaN(lamaAngsuran) && lamaAngsuran > 0) {
            const tanggal = new Date(tanggalPinjam);
            tanggal.setMonth(tanggal.getMonth() + lamaAngsuran - 1);
            
            const lastDayOfMonth = new Date(tanggal.getFullYear(), tanggal.getMonth() + 1, 0);
            tanggal.setDate(lastDayOfMonth.getDate());
            
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const formatted = tanggal.toLocaleDateString('id-ID', options);
            terakhirAngsuranInput.value = formatted;
          } else {
            terakhirAngsuranInput.value = '';
          }
        }
    
        function formatInputNumber(element) {
            element.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value) {
                    e.target.value = formatRupiah(value);
                } else {
                    e.target.value = '';
                }
            });
        }
    
        // Format input nominal & jumlah angsuran saat diketik
        formatInputNumber(nominalInput);
        formatInputNumber(jumlahAngsuranInput);
    
        nominalInput.addEventListener('input', function () {
            hitungLamaAngsuran();
        });
    
        jumlahAngsuranInput.addEventListener('input', function () {
            hitungLamaAngsuran();
            hitungTerakhirAngsuran();
        });
    
        tanggalPinjamInput.addEventListener('input', hitungTerakhirAngsuran);
    });
</script>
    
    
    
    
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
