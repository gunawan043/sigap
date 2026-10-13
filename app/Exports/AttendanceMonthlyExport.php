<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AttendanceMonthlyExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $startRow = 5; // Data dimulai dari baris ke-5

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    /**
     * Generate data array untuk export excel
     */
    public function array(): array
    {
        $tanggal = Carbon::create($this->tahun, $this->bulan);
        $daysInMonth = $tanggal->daysInMonth;
        $namaBulan = $tanggal->format('F');
        
        $users = $this->getUsersWithAttendance();
        
        $data = [];
        
        // BARIS 1: JUDUL
        $data[] = ["REKAP ABSENSI BULAN {$namaBulan} {$this->tahun}"];
        
        // BARIS 2: KOSONG (SPASI)
        $data[] = [''];
        
        // BARIS 3: HEADER UTAMA (NAMA DAN GROUP HEADER)
        $headerRow1 = ['Nama', 'Total Terlambat', '', 'Total Tidak', 'Total Tepat'];
        // Tambahkan kolom kosong untuk tanggal (akan diisi di baris 4)
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $headerRow1[] = '';
        }
        $data[] = $headerRow1;
        
        // BARIS 4: SUB HEADER (MENIT, HARI, ABSEN, WAKTU, DAN TANGGAL)
        $headerRow2 = ['', 'Menit', 'Hari', 'Absen', 'Waktu'];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $headerRow2[] = $day;
        }
        $data[] = $headerRow2;

        // DATA PEGAWAI (MULAI BARIS 5)
        foreach ($users as $user) {
            $attendanceByDate = $this->groupAttendanceByDay($user->attendances);
            $statistics = $this->calculateStatistics($user->attendances);
            
            $row = $this->buildUserRow($user, $statistics, $daysInMonth, $attendanceByDate);
            $data[] = $row;
        }
        
        // HITUNG POSISI UNTUK KETERANGAN (3 BARIS SETELAH DATA TERAKHIR)
        $lastDataRow = count($data); // Posisi baris terakhir data
        
        // KOSONGKAN 3 BARIS UNTUK JARAK DENGAN KETERANGAN
        $data[] = ['']; // Baris kosong 1
        $data[] = ['']; // Baris kosong 2
        $data[] = ['']; // Baris kosong 3
        
        // BARIS KETERANGAN
        $data[] = ['KETERANGAN:'];
        $data[] = ['■', 'Hijau : Tepat waktu dan check out lengkap'];
        $data[] = ['■', 'Kuning : Terlambat check in'];
        $data[] = ['■', 'Orange : Check in saja (belum check out)'];
        $data[] = ['■', 'Strip (-) : Tidak melakukan absensi'];

        return $data;
    }

    /**
     * Style untuk worksheet excel
     */
    public function styles(Worksheet $sheet)
    {
        // Style default untuk semua cell
        $sheet->getStyle('A:' . $sheet->getHighestColumn())
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);
    }
    
    /**
     * Event untuk merge cell dan styling lanjutan
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $lastColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                
                // MERGE CELL UNTUK HEADER
                
                // Merge judul utama (baris 1) dari A sampai kolom terakhir
                $sheet->mergeCells("A1:{$highestColumn}1");
                
                // Merge header "Total Terlambat" (baris 3 kolom B-C)
                $sheet->mergeCells("B3:C3");
                
                // STYLING JUDUL (BARIS 1)
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension('1')->setRowHeight(30);
                
                // BARIS 2 (KOSONG) - SET TINGGI
                $sheet->getRowDimension('2')->setRowHeight(10);
                
                // STYLING HEADER (BARIS 3-4)
                $sheet->getStyle("A3:{$highestColumn}4")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE0E0E0'],
                    ],
                ]);
                
                // SET TINGGI BARIS UNTUK HEADER
                $sheet->getRowDimension('3')->setRowHeight(25);
                $sheet->getRowDimension('4')->setRowHeight(25);
                
                // HITUNG BARIS TERAKHIR DATA PEGAWAI
                $lastDataRow = $this->getLastDataRow($sheet);
                
                // STYLING DATA PEGAWAI (BARIS 5 SAMPAI BARIS TERAKHIR DATA)
                for ($row = $this->startRow; $row <= $lastDataRow; $row++) {
                    
                    // Style untuk kolom nama (A) - rata kiri
                    $sheet->getStyle("A{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setIndent(1);
                    
                    // Center alignment untuk kolom B sampai terakhir
                    $sheet->getStyle("B{$row}:{$highestColumn}{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Border untuk semua data
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                    
                    // Set tinggi baris
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }
                
                // WARNA UNTUK CELL ATTENDANCE (MULAI KOLOM KE-6 = F)
                for ($row = $this->startRow; $row <= $lastDataRow; $row++) {
                    $userName = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                    $user = User::where('name', $userName)->first();
                    
                    if (!$user) continue;
                    
                    // Mulai dari kolom ke-6 (kolom F) untuk tanggal 1
                    for ($col = 6; $col <= $lastColumnIndex; $col++) {
                        $cellValue = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                        
                        if ($cellValue == '-') {
                            continue;
                        }
                        
                        $day = $col - 5; // Kolom F = index 6 -> day 1
                        $attendance = $this->getAttendanceForDate($user->id, $day);
                        
                        if (!$attendance) continue;
                        
                        $this->applyCellColor($sheet, $col, $row, $attendance);
                    }
                }
                
                // HITUNG POSISI KETERANGAN
                $startKeterangan = $lastDataRow + 4; // +4 karena 3 baris kosong + 1 baris judul keterangan
                
                // STYLING KETERANGAN
                $this->styleKeterangan($sheet, $startKeterangan, $highestColumn);
                
                // WRAP TEXT untuk kolom nama
                $sheet->getStyle('A:A')->getAlignment()->setWrapText(true);
                
                // FREEZE PANE (membekukan header)
                $sheet->freezePane('A5');
            },
        ];
    }

    /**
     * Setting lebar kolom
     */
    public function columnWidths(): array
    {
        $widths = [
            'A' => 35,  // Nama
            'B' => 15,  // Menit (Total Terlambat)
            'C' => 15,  // Hari (Total Terlambat)
            'D' => 15,  // Total Tidak Absen
            'E' => 15,  // Total Tepat Waktu
        ];
        
        return $widths;
    }

    /**
     * Mendapatkan baris terakhir data pegawai
     */
    private function getLastDataRow($sheet): int
    {
        $highestRow = $sheet->getHighestRow();
        
        // Cari baris terakhir yang memiliki data pegawai
        for ($row = $highestRow; $row >= $this->startRow; $row--) {
            $nama = $sheet->getCellByColumnAndRow(1, $row)->getValue();
            // Jika kolom A tidak kosong dan bukan "KETERANGAN:" dan tidak dimulai dengan "■"
            if (!empty($nama) && $nama != 'KETERANGAN:' && !is_numeric(strpos($nama, '■'))) {
                return $row;
            }
        }
        
        return $this->startRow;
    }

    /**
     * Style untuk bagian keterangan
     */
    private function styleKeterangan($sheet, $startRow, $highestColumn): void
    {
        // Judul KETERANGAN
        $sheet->mergeCells("A{$startRow}:B{$startRow}");
        $sheet->getStyle("A{$startRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(20);
        
        // Style untuk setiap baris keterangan (4 baris)
        for ($i = 1; $i <= 4; $i++) {
            $currentRow = $startRow + $i;
            
            // Merge kolom B sampai E untuk teks keterangan
            $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
            
            // Beri warna pada kolom A sesuai keterangan
            $colorARGB = 'FFFFFFFF'; // Default putih
            
            if ($i == 1) { // Hijau
                $colorARGB = 'FF28A745';
            } elseif ($i == 2) { // Kuning
                $colorARGB = 'FFFFC107';
            } elseif ($i == 3) { // Orange
                $colorARGB = 'FFFD7E14';
            } elseif ($i == 4) { // Putih untuk strip
                $colorARGB = 'FFFFFFFF';
            }
            
            $sheet->getStyle("A{$currentRow}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($colorARGB);
            
            // Border untuk baris keterangan
            $sheet->getStyle("A{$currentRow}:E{$currentRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            
            // Alignment untuk teks keterangan
            $sheet->getStyle("B{$currentRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER);
            
            // Alignment untuk simbol di kolom A
            $sheet->getStyle("A{$currentRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            
            $sheet->getRowDimension($currentRow)->setRowHeight(18);
        }
    }

    /**
     * Mendapatkan data user beserta attendance
     */
    private function getUsersWithAttendance()
    {
        return User::where('role', 'pegawai')
            ->with(['attendances' => function ($query) {
                $query->whereMonth('date', $this->bulan)
                      ->whereYear('date', $this->tahun);
            }])
            ->orderBy('name')
            ->get();
    }

    /**
     * Mengelompokkan attendance berdasarkan hari
     */
    private function groupAttendanceByDay($attendances)
    {
        return $attendances->keyBy(function ($item) {
            return Carbon::parse($item->date)->day;
        });
    }

    /**
     * Menghitung statistik attendance
     */
    private function calculateStatistics($attendances): array
    {
        return [
            'totalTelatMenit' => $attendances->sum('selisih_menit'),
            'totalTelatHari' => $attendances->where('selisih_menit', '>', 0)->count(),
            'totalTepatWaktu' => $attendances->where('selisih_menit', 0)->count(),
        ];
    }

    /**
     * Membuat row data untuk setiap user
     */
    private function buildUserRow($user, $statistics, $daysInMonth, $attendanceByDate): array
    {
        $totalTidakAbsen = 0;
        
        $row = [
            $user->name,
            $statistics['totalTelatMenit'],
            $statistics['totalTelatHari'],
            0,  // Total Tidak Absen (akan diupdate)
            $statistics['totalTepatWaktu']
        ];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            if (!isset($attendanceByDate[$day])) {
                $row[] = '-';
                $totalTidakAbsen++;
            } else {
                $attendance = $attendanceByDate[$day];
                // Tampilkan check_in_time saja, jika ada
                $row[] = $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i') : '-';
            }
        }

        $row[3] = $totalTidakAbsen; // Update total tidak absen di kolom D (index ke-3)

        return $row;
    }

    /**
     * Mendapatkan data attendance berdasarkan user dan tanggal
     */
    private function getAttendanceForDate($userId, $day)
    {
        return Attendance::whereDay('date', $day)
            ->whereMonth('date', $this->bulan)
            ->whereYear('date', $this->tahun)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Menerapkan warna pada cell berdasarkan kondisi attendance
     */
    private function applyCellColor(Worksheet $sheet, $col, $row, $attendance): void
    {
        $cellStyle = $sheet->getStyleByColumnAndRow($col, $row)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID);

        if ($attendance->selisih_menit > 0) {
            // KUNING untuk terlambat
            $cellStyle->getStartColor()->setARGB('FFFFC107');
        } elseif ($attendance->check_in_time && !$attendance->check_out_time) {
            // ORANGE untuk check-in saja
            $cellStyle->getStartColor()->setARGB('FFFD7E14');
        } else {
            // HIJAU untuk tepat waktu dan lengkap
            $cellStyle->getStartColor()->setARGB('FF28A745');
        }
    }
}