<?php
// app/Http/Controllers/AttendanceExportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceExportService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Exports\AttendanceMonthlyExport;
use Maatwebsite\Excel\Facades\Excel;
class AttendanceExportController extends Controller
{
    protected $exportService;
    
    public function indexArsip()
    {
        $now = Carbon::now();
        
        $users = User::where('role', 'pegawai')->with(['attendances' => function ($query) use ($now) {
            $query->whereMonth('date', $now->month)
                ->whereYear('date', $now->year);
        }])->get();
        
        return view('admin-arsip-absensi', compact('now', 'users'));
    }

    public function __construct(AttendanceExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Tampilkan form pilih bulan.
     * Halaman ini memakai rekap absensi yang sudah ada.
     */
    public function showForm()
    {
        return redirect()->route('rekap.absensi');
    }

    /**
     * Proses export Excel
     */
    public function export($bulan, $tahun)
    {
        if (!$bulan || !$tahun) {
            abort(404);
        }

        $fileName = 'Rekap_Absensi_'.$bulan.'_'.$tahun.'.xlsx';

        return Excel::download(
            new AttendanceMonthlyExport($bulan, $tahun),
            $fileName
        );
    }


    /**
     * Download file export
     */
    public function download($fileName)
    {
        $path = 'public/exports/' . $fileName;
        
        if (!Storage::exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::download($path, $fileName);
    }

    /**
     * Hapus file export
     */
    public function delete($fileName)
    {
        $path = 'public/exports/' . $fileName;
        
        if (Storage::exists($path)) {
            Storage::delete($path);
            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'File tidak ditemukan'
        ], 404);
    }

    /**
     * Dapatkan daftar file export
     */
    public function getFiles()
    {
        $files = $this->exportService->getExportedFiles();
        return response()->json($files);
    }
}