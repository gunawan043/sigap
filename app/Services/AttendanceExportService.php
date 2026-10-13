<?php
// app/Services/AttendanceExportService.php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Storage;

class AttendanceExportService
{
    protected $exportPath = 'public/exports';
    protected $tempPath = 'public/exports/temp';
    
    /**
     * Export rekap absensi bulanan
     */
    public function exportBulanan($bulan, $tahun)
    {
        $tanggalAwal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $tanggalAkhir = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
        
        // Ambil data
        $users = User::where('role', 'pegawai')->get();
        $attendances = $this->getAttendanceData($tanggalAwal, $tanggalAkhir);
        
        // Buat spreadsheet
        $spreadsheet = $this->createSpreadsheet($users, $attendances, $tanggalAwal, $tanggalAkhir);
        
        // Simpan file
        $fileName = $this->generateFileName($bulan, $tahun);
        $filePath = $this->saveSpreadsheet($spreadsheet, $fileName);
        
        return [
            'success' => true,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'full_path' => Storage::url($filePath)
        ];
    }
    
    /**
     * Ambil data attendance
     */
    protected function getAttendanceData($tanggalAwal, $tanggalAkhir)
    {
        return Attendance::with('user')
            ->whereBetween('date', [$tanggalAwal->toDateString(), $tanggalAkhir->toDateString()])
            ->get()
            ->groupBy('user_id');
    }
    
    /**
     * Buat spreadsheet
     */
    protected function createSpreadsheet($users, $attendances, $tanggalAwal, $tanggalAkhir)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul
        $judulBulan = $tanggalAwal->locale('id')->translatedFormat('F Y');
        $sheet->setCellValue('A1', 'LAPORAN REKAP ABSENSI BULAN ' . strtoupper($judulBulan));
        $sheet->mergeCells('A1:' . $this->getColumnLetter(6 + $tanggalAkhir->day) . '1');
        $this->styleJudul($sheet);
        
        // Header kolom
        $rowHeader = 3;
        $this->setHeaders($sheet, $rowHeader, $tanggalAwal, $tanggalAkhir);
        
        // Isi data
        $row = 4;
        $no = 1;
        
        foreach ($users as $user) {
            $userAttendances = $attendances->get($user->id, collect());
            $this->isiDataUser($sheet, $row, $no++, $user, $userAttendances, $tanggalAwal, $tanggalAkhir);
            $row++;
        }
        
        // Set lebar kolom
        $this->setColumnWidths($sheet, $tanggalAkhir->day);
        
        return $spreadsheet;
    }
    
    /**
     * Set header kolom
     */
    protected function setHeaders($sheet, $row, $tanggalAwal, $tanggalAkhir)
    {
        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Nama');
        $sheet->setCellValue('C' . $row, 'Total Terlambat (Menit)');
        $sheet->setCellValue('D' . $row, 'Total Terlambat (Hari)');
        $sheet->setCellValue('E' . $row, 'Total Tidak Absen');
        $sheet->setCellValue('F' . $row, 'Total Tepat Waktu');
        
        // Kolom tanggal
        $colDate = 7;
        for ($i = 1; $i <= $tanggalAkhir->day; $i++) {
            $colLetter = $this->getColumnLetter($colDate);
            $tanggal = Carbon::createFromDate($tanggalAwal->year, $tanggalAwal->month, $i);
            $hari = $tanggal->locale('id')->isoFormat('dddd');
            $sheet->setCellValue($colLetter . $row, $i . "\n" . $hari);
            $sheet->getStyle($colLetter . $row)->getAlignment()->setWrapText(true);
            $colDate++;
        }
        
        // Style header
        $headerRange = 'A' . $row . ':' . $this->getColumnLetter(6 + $tanggalAkhir->day) . $row;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E6E6E6']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
    }
    
    /**
     * Isi data per user
     */
    protected function isiDataUser($sheet, $row, $no, $user, $userAttendances, $tanggalAwal, $tanggalAkhir)
    {
        // Hitung statistik
        $statistik = $this->hitungStatistik($userAttendances, $tanggalAwal, $tanggalAkhir);
        
        // Tulis data user
        $sheet->setCellValue('A' . $row, $no);
        $sheet->setCellValue('B' . $row, $user->name);
        $sheet->setCellValue('C' . $row, $statistik['total_menit_terlambat']);
        $sheet->setCellValue('D' . $row, $statistik['total_hari_terlambat']);
        $sheet->setCellValue('E' . $row, $statistik['total_tidak_absen']);
        $sheet->setCellValue('F' . $row, $statistik['total_tepat_waktu']);
        
        // Tulis status per tanggal
        $this->isiDataTanggal($sheet, $row, $statistik['data_per_tanggal'], $tanggalAkhir->day);
        
        // Style baris
        $this->styleBarisData($sheet, $row, $tanggalAkhir->day);
    }
    
    /**
     * Hitung statistik per user
     */
    protected function hitungStatistik($userAttendances, $tanggalAwal, $tanggalAkhir)
    {
        $totalMenitTerlambat = 0;
        $totalHariTerlambat = 0;
        $totalTepatWaktu = 0;
        $dataPerTanggal = [];
        
        foreach ($userAttendances as $attendance) {
            $tanggal = Carbon::parse($attendance->date)->day;
            
            if ($attendance->is_late) {
                $totalMenitTerlambat += $attendance->selisih_menit;
            }
            
            if (!$attendance->check_in_time) {
                $dataPerTanggal[$tanggal] = 'tidak_absen';
            } elseif (!$attendance->check_out_time) {
                $dataPerTanggal[$tanggal] = 'tidak_absen_pulang';
            } else {
                if ($attendance->is_late) {
                    $totalHariTerlambat++;
                    $dataPerTanggal[$tanggal] = 'terlambat';
                } else {
                    $totalTepatWaktu++;
                    $dataPerTanggal[$tanggal] = 'hadir';
                }
            }
        }
        
        // Hitung total tidak absen
        $totalTidakAbsen = 0;
        for ($day = 1; $day <= $tanggalAkhir->day; $day++) {
            if (!isset($dataPerTanggal[$day])) {
                $totalTidakAbsen++;
                $dataPerTanggal[$day] = 'tidak_absen';
            }
        }
        
        return [
            'total_menit_terlambat' => $totalMenitTerlambat,
            'total_hari_terlambat' => $totalHariTerlambat,
            'total_tidak_absen' => $totalTidakAbsen,
            'total_tepat_waktu' => $totalTepatWaktu,
            'data_per_tanggal' => $dataPerTanggal
        ];
    }
    
    /**
     * Isi data per tanggal
     */
    protected function isiDataTanggal($sheet, $row, $dataPerTanggal, $totalHari)
    {
        $colDate = 7;
        for ($i = 1; $i <= $totalHari; $i++) {
            $colLetter = $this->getColumnLetter($colDate);
            $status = $dataPerTanggal[$i] ?? 'tidak_absen';
            
            switch ($status) {
                case 'hadir':
                    $sheet->setCellValue($colLetter . $row, '✓');
                    $sheet->getStyle($colLetter . $row)->getFont()->setColor(new Color('28a745'));
                    break;
                    
                case 'terlambat':
                    $sheet->setCellValue($colLetter . $row, '✓ T');
                    $sheet->getStyle($colLetter . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FFC107'); // Kuning
                    break;
                    
                case 'tidak_absen_pulang':
                    $sheet->setCellValue($colLetter . $row, '↷');
                    $sheet->getStyle($colLetter . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FD7E14'); // Orange
                    break;
                    
                case 'tidak_absen':
                    $sheet->setCellValue($colLetter . $row, '✗');
                    // Transparan / tanpa warna
                    break;
            }
            
            $sheet->getStyle($colLetter . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            
            $colDate++;
        }
    }
    
    /**
     * Style judul
     */
    protected function styleJudul($sheet)
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
    /**
     * Style baris data
     */
    protected function styleBarisData($sheet, $row, $totalHari)
    {
        $dataRange = 'A' . $row . ':' . $this->getColumnLetter(6 + $totalHari) . $row;
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
    
    /**
     * Set lebar kolom
     */
    protected function setColumnWidths($sheet, $totalHari)
    {
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        
        $colDate = 7;
        for ($i = 1; $i <= $totalHari; $i++) {
            $colLetter = $this->getColumnLetter($colDate);
            $sheet->getColumnDimension($colLetter)->setWidth(12);
            $colDate++;
        }
    }
    
    /**
     * Generate nama file
     */
    protected function generateFileName($bulan, $tahun)
    {
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM');
        $timestamp = Carbon::now()->format('Ymd_His');
        return "rekap_absensi_{$namaBulan}_{$tahun}_{$timestamp}.xlsx";
    }
    
    /**
     * Simpan spreadsheet ke storage
     */
    protected function saveSpreadsheet($spreadsheet, $fileName)
    {
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $writer->save($tempFile);
        
        $path = $this->exportPath . '/' . $fileName;
        Storage::put($path, file_get_contents($tempFile));
        unlink($tempFile);
        
        // Hapus file lama (lebih dari 7 hari)
        $this->cleanOldExports();
        
        return $path;
    }
    
    /**
     * Bersihkan file export lama
     */
    protected function cleanOldExports()
    {
        $files = Storage::files($this->exportPath);
        $now = Carbon::now();
        
        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(Storage::lastModified($file));
            if ($lastModified->diffInDays($now) > 7) {
                Storage::delete($file);
            }
        }
    }
    
    /**
     * Konversi angka ke huruf kolom Excel
     */
    protected function getColumnLetter($index)
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = floor($index / 26);
        }
        return $letter;
    }
    
    /**
     * Dapatkan daftar file export
     */
    public function getExportedFiles()
    {
        $files = Storage::files($this->exportPath);
        $fileList = [];
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'xlsx') {
                $fileList[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'url' => Storage::url($file),
                    'size' => $this->formatSize(Storage::size($file)),
                    'modified' => Carbon::createFromTimestamp(Storage::lastModified($file))->diffForHumans()
                ];
            }
        }
        
        // Urutkan berdasarkan waktu modified terbaru
        usort($fileList, function($a, $b) use ($files) {
            return Storage::lastModified($b['path']) - Storage::lastModified($a['path']);
        });
        
        return $fileList;
    }
    
    /**
     * Format ukuran file
     */
    protected function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}