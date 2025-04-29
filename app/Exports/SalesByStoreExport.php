<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SalesByStoreExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $storesSales;
    protected $totalOmzet;
    protected $startDate;
    protected $endDate;

    /**
     * Constructor
     *
     * @param Collection $storesSales
     * @param float $totalOmzet
     * @param string $startDate
     * @param string $endDate
     */
    public function __construct($storesSales, $totalOmzet, $startDate, $endDate)
    {
        $this->storesSales = $storesSales;
        $this->totalOmzet = $totalOmzet;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect();
        $rank = 1;

        foreach ($this->storesSales as $store) {
            $avgTransaction = $store->total_transactions > 0 ? $store->total_omzet / $store->total_transactions : 0;
            $percentage = $this->totalOmzet > 0 ? ($store->total_omzet / $this->totalOmzet) * 100 : 0;

            $data->push([
                'Peringkat' => $rank,
                'Nama Toko' => $store->store_name,
                'Jumlah Transaksi' => $store->total_transactions,
                'Total Omzet' => $store->total_omzet,
                'Rata-rata per Transaksi' => $avgTransaction,
                'Persentase Kontribusi' => $percentage
            ]);

            $rank++;
        }

        // Tambahkan total di baris terakhir
        $data->push([
            'Peringkat' => '',
            'Nama Toko' => 'TOTAL',
            'Jumlah Transaksi' => $this->storesSales->sum('total_transactions'),
            'Total Omzet' => $this->totalOmzet,
            'Rata-rata per Transaksi' => '',
            'Persentase Kontribusi' => 100
        ]);

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            ['LAPORAN PENJUALAN PER TOKO (RANKING BERDASARKAN OMZET)'],
            ['Periode: ' . Carbon::parse($this->startDate)->format('d M Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d M Y')],
            [''],
            [
                'Peringkat',
                'Nama Toko',
                'Jumlah Transaksi',
                'Total Omzet',
                'Rata-rata per Transaksi',
                'Persentase Kontribusi (%)'
            ]
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');

        return [
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            4 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'A' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'C' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            'D' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
            'E' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
            'F' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            $lastRow => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E9ECEF']]]
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_PERCENTAGE_00
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:F1')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '4E73DF']
                    ],
                    'font' => [
                        'color' => ['rgb' => 'FFFFFF']
                    ]
                ]);

                $event->sheet->getStyle('A4:F4')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => 'E9ECEF']
                    ]
                ]);

                // Add border to all cells with data
                $highestRow = $event->sheet->getHighestRow();
                $highestColumn = $event->sheet->getHighestColumn();

                $event->sheet->getStyle('A4:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'DDDDDD']
                        ]
                    ]
                ]);

                // Set page to landscape and fit to width
                $event->sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->getPageSetup()->setFitToWidth(1);

                // Add footer with date and page number
                $event->sheet->getHeaderFooter()
                    ->setOddFooter('&L&D &T&C&RPagina &P / &N');
            }
        ];
    }
}
