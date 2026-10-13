@extends('layouts.master')
@section('title')
    Arsip Kitir Gaji
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Kitir
        @endslot
        @slot('title')
            Arsip Gaji Bulanan
        @endslot
    @endcomponent

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-border-left alert-dismissible fade show material-shadow" role="alert">
            <i class="ri-check-double-line me-3 align-middle fs-16"></i>
            <strong>Success</strong> - {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <style>
        /* Container Custom untuk Scroll */
        .table-container {
            position: relative;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #e9ebec;
            background: white;
        }

        .table-responsive-custom {
            overflow-x: auto;
            width: 100%;
        }

        /* Sticky Column Nama PTK */
        .sticky-name {
            position: sticky;
            left: 0;
            z-index: 5;
            background-color: white !important;
            min-width: 200px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }

        /* Header Sticky */
        thead th.sticky-name {
            z-index: 6;
            background-color: #f3f6f9 !important;
        }

        .text-number {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        /* Perbaikan Pagination Bootstrap agar tidak raksasa */
        .pagination {
            margin-bottom: 0;
            gap: 2px;
        }
        .page-link {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header align-items-center d-flex py-3">
                    <h4 class="card-title mb-0 flex-grow-1">
                        <i class="ri-archive-fill align-bottom me-1 text-primary"></i> Arsip Kitir Gaji
                    </h4>
                    <div class="d-flex flex-wrap gap-2">
                        {{-- Filter Form --}}
                        <form action="{{ route('arsip-kitir.index') }}" method="GET" class="d-flex gap-2">
                            <select name="periode" class="form-select form-select-sm shadow-sm" style="width: 160px;" onchange="this.form.submit()">
                                <option value="">Pilih Periode</option>
                                @foreach ($periodes as $periode)
                                    <option value="{{ $periode->priode_gaji }}" {{ request('periode') == $periode->priode_gaji ? 'selected' : '' }}>
                                        {{ $periode->priode_gaji }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="user_id" class="form-select form-select-sm shadow-sm" style="width: 180px;" onchange="this.form.submit()">
                                <option value="">Semua Karyawan</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        <form action="{{ route('arsip-kitir.export-excel') }}" method="GET">
                            <input type="hidden" name="periode" value="{{ request('periode') }}">
                            <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                            <button type="submit" class="btn btn-success btn-sm shadow-sm">
                                <i class="ri-file-excel-2-line align-bottom me-1"></i> Export Excel
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if ($arsipKitir->count() > 0)
                        <div class="alert alert-soft-info border-0 mb-3 d-flex justify-content-between">
                            <span>Menampilkan data periode: <strong>{{ request('periode') ?: 'Semua' }}</strong></span>
                            <span>Total: <strong>{{ $arsipKitir->total() }} Data</strong></span>
                        </div>

                        <div class="table-container">
                            <div class="table-responsive-custom">
                                <table class="table table-hover table-bordered table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="text-center fs-11 text-uppercase text-muted">
                                            <th style="width: 40px;">No</th>
                                            <th>Periode</th>
                                            <th class="sticky-name text-dark">Nama PTK</th>
                                            <th>Induk</th>
                                            <th>TMT</th>
                                            <th>Gol</th>
                                            <th class="text-end">Gaji Pokok</th>
                                            <th class="text-end">T. Jabatan</th>
                                            <th class="text-end">Total Gaji</th>
                                            <th>Jam</th>
                                            <th class="text-end">Total Rp Jam</th>
                                            <th class="text-end text-success">Bruto</th>
                                            <th class="text-end text-danger">Potongan</th>
                                            <th class="bg-primary text-white text-end">Netto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $clean = fn($v) => (float) str_replace(['.', ','], '', $v ?: 0);
                                        @endphp
                                        @foreach ($arsipKitir as $key => $arsip)
                                            <tr>
                                                <td class="text-center text-muted">{{ $arsipKitir->firstItem() + $key }}</td>
                                                <td class="text-center"><span class="badge bg-light text-primary border border-primary">{{ $arsip->priode_gaji }}</span></td>
                                                <td class="sticky-name fw-semibold text-dark">{{ $arsip->nama_ptk }}</td>
                                                <td class="text-center">{{ $arsip->induk }}</td>
                                                <td class="text-center">{{ $arsip->tmt }}</td>
                                                <td class="text-center">{{ $arsip->gol_pt }}</td>
                                                
                                                <td class="text-end text-number">{{ number_format($clean($arsip->gaji_pokok), 0, ',', '.') }}</td>
                                                <td class="text-end text-number">{{ number_format($clean($arsip->t_jab), 0, ',', '.') }}</td>
                                                <td class="text-end text-number">{{ number_format($clean($arsip->total_gaji), 0, ',', '.') }}</td>
                                                <td class="text-center fw-bold">{{ $arsip->jam_mengajar }}</td>
                                                <td class="text-end text-number">{{ number_format($clean($arsip->total_rp_jam), 0, ',', '.') }}</td>
                                                <td class="text-end text-number fw-bold text-success">{{ number_format($clean($arsip->total_tanpa_potongan), 0, ',', '.') }}</td>
                                                <td class="text-end text-number text-danger">{{ number_format($clean($arsip->total_potongan), 0, ',', '.') }}</td>
                                                <td class="text-end text-number fw-bold bg-soft-primary text-primary">{{ number_format($clean($arsip->total_diterima), 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @if (request('periode') && !request('user_id'))
                                        <tfoot class="table-light fw-bold text-end">
                                            <tr>
                                                <td colspan="6">GRAND TOTAL (Hal ini):</td>
                                                <td class="text-number">{{ number_format($arsipKitir->sum(fn($i) => $clean($i->gaji_pokok)), 0, ',', '.') }}</td>
                                                <td class="text-number">{{ number_format($arsipKitir->sum(fn($i) => $clean($i->t_jab)), 0, ',', '.') }}</td>
                                                <td class="text-number text-primary">{{ number_format($arsipKitir->sum(fn($i) => $clean($i->total_gaji)), 0, ',', '.') }}</td>
                                                <td class="text-center text-dark">{{ $arsipKitir->sum('jam_mengajar') }}</td>
                                                <td class="text-number">{{ number_format($arsipKitir->sum(fn($i) => $clean($i->total_rp_jam)), 0, ',', '.') }}</td>
                                                <td class="text-number text-success">{{ number_format($arsipKitir->sum(fn($i) => $clean($i->total_tanpa_potongan)), 0, ',', '.') }}</td>
                                                <td class="text-number text-danger">{{ number_format($arsipKitir->sum(fn($i) => $clean($i->total_potongan)), 0, ',', '.') }}</td>
                                                <td class="bg-primary text-white text-number">{{ number_format($arsipKitir->sum(fn($i) => $clean($i->total_diterima)), 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>

                        {{-- Pagination Fix --}}
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted fs-13">
                                Showing {{ $arsipKitir->firstItem() }} to {{ $arsipKitir->lastItem() }} of {{ $arsipKitir->total() }} entries
                            </div>
                            <div>
                                {{ $arsipKitir->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:75px;height:75px"></lord-icon>
                            <h5 class="mt-2">Opps! Data Kosong</h5>
                            <p class="text-muted">Tidak ada arsip gaji ditemukan untuk kriteria ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection