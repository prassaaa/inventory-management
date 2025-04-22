<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Product::with(['category', 'baseUnit', 'warehouseStock'])->get();
    }
    
    /**
     * @var Product $product
     */
    public function map($product): array
    {
        return [
            $product->code,
            $product->name,
            $product->category->name,
            $product->baseUnit->name,
            $product->purchase_price,
            $product->selling_price,
            $product->min_stock,
            $product->warehouseStock ? $product->warehouseStock->quantity : 0,
            $product->description,
            $product->is_active ? 'Active' : 'Inactive'
        ];
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Category',
            'Base Unit',
            'Purchase Price',
            'Selling Price',
            'Min Stock',
            'Current Stock',
            'Description',
            'Status'
        ];
    }
    
    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}