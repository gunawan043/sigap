<?php

namespace App\Http\Controllers;

use App\Models\ArsipKitir;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\ArsipKitirExport;
use Maatwebsite\Excel\Facades\Excel;


class ArsipKitirController extends Controller
{
    public function index(Request $request)
    {
        $query = ArsipKitir::query();

        if ($request->filled('periode')) {
            $query->where('priode_gaji', $request->periode);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $arsipKitir = $query
            ->orderBy('priode_gaji', 'desc')
            ->paginate(50);

        $periodes = ArsipKitir::select('priode_gaji')
            ->distinct()
            ->orderBy('priode_gaji', 'desc')
            ->get();

        $users = User::orderBy('name')->get();

        return view('arsip-kitir-gaji', compact(
            'arsipKitir',
            'periodes',
            'users'
        ));
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->periode;
        $userId  = $request->user_id;

        $filename = 'Arsip-Kitir-Gaji';

        if ($periode) {
            $filename .= '-' . str_replace([' ', ','], '-', $periode);
        }

        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $filename .= '-' . str_replace(' ', '-', $user->name);
            }
        }

        $filename .= '-' . Carbon::now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new ArsipKitirExport($periode, $userId),
            $filename
        );
    }

    /**
     * Export to CSV (Excel bisa buka CSV)
     */
    public function exportCSV(Request $request)
    {
        $periode = $request->input('periode', '');
        $userId = $request->input('user_id', '');

        $query = ArsipKitir::query();

        if (!empty($periode)) {
            $query->where('priode_gaji', $periode);
        }

        if (!empty($userId) && $userId != 'null') {
            $query->where('user_id', $userId);
        }

        $data = $query->orderBy('nama_ptk')->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diunduh.');
        }

        $filename = 'Arsip-Kitir-Gaji';
        if (!empty($periode)) {
            $filename .= '-' . str_replace([',', ' '], '-', $periode);
        }
        $filename .= '-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');

            // BOM supaya Excel membaca UTF-8 dengan benar
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['NO', 'PERIODE', 'NAMA', 'GAJI POKOK'], ';');

            $no = 1;
            foreach ($data as $item) {
                fputcsv($output, [
                    $no++,
                    $item->priode_gaji,
                    $item->nama_ptk,
                    $item->gaji_pokok,
                ], ';');
            }

            fclose($output);
        }, $filename, $headers);
    }

    
    /**
     * Export individual slip to CSV
     */
    public function exportSlipCSV($id)
    {
        $arsip = ArsipKitir::findOrFail($id);
        
        $filename = 'Slip-Gaji-' . str_replace(' ', '-', $arsip->nama_ptk) . 
                    '-' . str_replace([',', ' '], '-', $arsip->priode_gaji) . 
                    '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($arsip) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            
            // Header slip
            fputcsv($file, ['SLIP GAJI KARYAWAN'], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['PERIODE GAJI', $arsip->priode_gaji], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['INFORMASI KARYAWAN'], ';');
            fputcsv($file, ['Nama PTK', $arsip->nama_ptk], ';');
            fputcsv($file, ['Induk', $arsip->induk], ';');
            fputcsv($file, ['TMT', $arsip->tmt], ';');
            fputcsv($file, ['Jenis Kelamin', $arsip->jk], ';');
            fputcsv($file, ['Golongan', $arsip->gol_pt], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['RINCIAN PENDAPATAN'], ';');
            fputcsv($file, ['DESKRIPSI', 'JUMLAH (Rp)'], ';');
            fputcsv($file, ['Gaji Pokok', number_format($arsip->gaji_pokok, 0, ',', '.')], ';');
            fputcsv($file, ['Tunjangan Jabatan', number_format($arsip->t_jab, 0, ',', '.')], ';');
            fputcsv($file, ['Total Gaji', number_format($arsip->total_gaji, 0, ',', '.')], ';');
            fputcsv($file, ['Jam Mengajar (' . $arsip->jam_mengajar . ' jam)', number_format($arsip->total_rp_jam, 0, ',', '.')], ';');
            fputcsv($file, ['Tunjangan Kinerja', number_format($arsip->tunjangan_kinerja, 0, ',', '.')], ';');
            fputcsv($file, ['Insentif Kehadiran', number_format($arsip->insentif_kehadiran, 0, ',', '.')], ';');
            fputcsv($file, ['Uang Lauk Pauk', number_format($arsip->uang_lauk_pauk, 0, ',', '.')], ';');
            fputcsv($file, ['Total Pendapatan', number_format($arsip->total_tanpa_potongan, 0, ',', '.')], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['RINCIAN POTONGAN'], ';');
            fputcsv($file, ['DESKRIPSI', 'JUMLAH (Rp)'], ';');
            fputcsv($file, ['Potongan Kasbon', number_format($arsip->pot_kasbon, 0, ',', '.')], ';');
            fputcsv($file, ['Potongan BOS', number_format($arsip->pot_bos, 0, ',', '.')], ';');
            fputcsv($file, ['Potongan Insidentil', number_format($arsip->pot_insidentil, 0, ',', '.')], ';');
            fputcsv($file, ['Total Potongan', number_format($arsip->total_potongan, 0, ',', '.')], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['REKAPITULASI'], ';');
            fputcsv($file, ['Total Pendapatan', number_format($arsip->total_tanpa_potongan, 0, ',', '.')], ';');
            fputcsv($file, ['Total Potongan', number_format($arsip->total_potongan, 0, ',', '.')], ';');
            fputcsv($file, ['TOTAL DITERIMA', number_format($arsip->total_diterima, 0, ',', '.')], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['Tanggal Cetak', Carbon::now()->format('d F Y H:i:s')], ';');
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $arsip = ArsipKitir::findOrFail($id);
        $arsip->delete();
        
        return redirect()->back()->with('success', 'Data arsip berhasil dihapus.');
    }
}