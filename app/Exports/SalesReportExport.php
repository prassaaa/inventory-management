<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $storeId;
    protected $paymentType;
    
    public function __construct($startDate, $endDate, $storeId = null, $paymentType = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->storeId = $storeId;
        $this->paymentType = $paymentType;
    }
    
    public function collection()
    {
        $query = Sale::with(['store', 'creator'])
            ->whereBetween('date', [$this->startDate, $this->endDate]);
            
        if ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }
        
        if ($this->paymentType) {
            $query->where('payment_type', $this->paymentType);
        }
        
        return $query->orderBy('date', 'desc')->get();
    }
    
    public function headings(): array
    {
        return [
            'Date',
            'Invoice',
            'Store',
            'Customer',
            'Payment Type',
            'Total Amount',
            'Discount',
            'Tax',
            'Status',
            'Created By'
        ];
    }
    
    public function map($sale): array
    {
        return [
            $sale->date->format('d/m/Y'),
            $sale->invoice_number,
            $sale->store->name,
            $sale->customer_name ?? 'Walk-in Customer',
            ucfirst($sale->payment_type),
            $sale->total_amount,
            $sale->discount,
            $sale->tax,
            ucfirst($sale->status),
            $sale->creator->name
        ];
    }
    
    public function title(): string
    {
        return 'Sales Report';
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}