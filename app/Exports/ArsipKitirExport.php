<?php

namespace App\Exports;

use App\Models\ArsipKitir;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithTitle,
    WithEvents,
    WithCustomStartCell
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{
    Border,
    Alignment,
    Fill,
    Color
};

class ArsipKitirExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithTitle,
    WithEvents,
    WithCustomStartCell
{
    protected $periode;
    protected $userId;
    protected $periodeLabel;
    protected $userName;
    protected $data;

    public function __construct($periode = null, $userId = null)
    {
        $this->periode = $periode;
        $this->userId = $userId;
        $this->periodeLabel = $periode ?: 'Semua Periode';
        $this->data = $this->getData();
    }

    private function getData(): Collection
    {
        $query = ArsipKitir::query();

        if ($this->periode) {
            $query->where('priode_gaji', $this->periode);
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
            $user = User::find($this->userId);
            $this->userName = $user?->name;
        }

        return $query->orderBy('id')->get();
    }

    /**
     * Start data di Baris 6 (Header)
     */
    public function startCell(): string
    {
        return 'A6'; 
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'NO','PERIODE','NAMA PTK','INDUK','TMT','JK','GOL',
            'GAJI POKOK','KJ1','KJ2','KJ3','KJ4','KJ5',
            'T. JABATAN','TOTAL GAJI','JAM',
            'RP JAM','T. KINERJA','INSENTIF','UANG MAKAN',
            'POT KASBON','POT INSIDENTIL','POT BOS',
            'TOTAL POT','TOTAL TANPA POT','TOTAL DITERIMA'
        ];
    }

    /**
     * Mapping data agar terbaca sebagai Numerik di Excel
     */
    public function map($row): array
    {
        return [
            '', // Kolom A untuk Nomor Urut (diisi via Event)
            $row->priode_gaji,
            $row->nama_ptk,
            $row->induk,
            $row->tmt,
            $row->jk,
            $row->gol_pt,
            
            // Mengubah string ribuan dari DB menjadi numeric murni
            $this->cleanNumeric($row->gaji_pokok),
            $this->cleanNumeric($row->kj1),
            $this->cleanNumeric($row->kj2),
            $this->cleanNumeric($row->kj3),
            $this->cleanNumeric($row->kj4),
            $this->cleanNumeric($row->kj5),
            
            $this->cleanNumeric($row->t_jab),
            $this->cleanNumeric($row->total_gaji),
            $row->jam_mengajar,
            
            $this->cleanNumeric($row->total_rp_jam),
            $this->cleanNumeric($row->tunjangan_kinerja),
            $this->cleanNumeric($row->insentif_kehadiran),
            $this->cleanNumeric($row->uang_lauk_pauk),
            
            $this->cleanNumeric($row->pot_kasbon),
            $this->cleanNumeric($row->pot_insidentil),
            $this->cleanNumeric($row->pot_bos),
            
            $this->cleanNumeric($row->total_potongan),
            $this->cleanNumeric($row->total_tanpa_potongan),
            $this->cleanNumeric($row->total_diterima),
        ];
    }
    
    /**
     * Helper untuk membersihkan format titik/koma agar menjadi angka murni
     */
    private function cleanNumeric($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        
        // Jika data dari DB adalah "332.750", hapus titiknya agar jadi 332750
        // Excel akan memformat ulang menggunakan NumberFormat #,##0 di bawah
        $clean = str_replace(['.', ','], '', $value);
        
        return (float) $clean;
    }

    public function title(): string
    {
        return substr('Arsip Gaji '.$this->periodeLabel, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A5568']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $count = $this->data->count();

                // Judul Laporan
                $sheet->mergeCells('A1:Z1');
                $sheet->setCellValue('A1', 'LAPORAN ARSIP KITIR GAJI');
                $sheet->mergeCells('A2:Z2');
                $sheet->setCellValue('A2', 'Periode: '.$this->periodeLabel);
                $sheet->mergeCells('A3:Z3');
                $sheet->setCellValue('A3', 'Tanggal Export: '.date('d/m/Y H:i'));

                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

                $startRow = 7;
                $endRow   = $startRow + $count - 1;

                // Isi Nomor Urut di Kolom A
                for ($i = 0; $i < $count; $i++) {
                    $sheet->setCellValue('A'.($startRow + $i), $i + 1);
                }

                // FORMAT RUPIAH (Kolom H sampai Z)
                // Ini akan mengubah 332750 tampil menjadi 332.750 secara visual
                foreach (range('H', 'Z') as $col) {
                    $sheet->getStyle("$col$startRow:$col$endRow")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                    
                    // Rata kanan untuk angka agar rapi
                    $sheet->getStyle("$col$startRow:$col$endRow")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $sheet->getStyle("A6:Z$endRow")->getBorders()
                    ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->freezePane('A7');
                $sheet->setAutoFilter("A6:Z$endRow");
            }
        ];
    }
}