<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IngredientUsageReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $storeId;
    protected $categoryId;

    public function __construct($startDate, $endDate, $storeId = null, $categoryId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->storeId = $storeId;
        $this->categoryId = $categoryId;
    }

    public function collection()
    {
        $query = DB::table('sale_details as sd')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->join('products as p', 'sd.product_id', '=', 'p.id')
            ->join('product_ingredients as pi', 'p.id', '=', 'pi.product_id')
            ->join('products as p_ingredient', 'pi.ingredient_id', '=', 'p_ingredient.id')
            ->join('units as u', 'pi.unit_id', '=', 'u.id')
            ->join('categories as c', 'p_ingredient.category_id', '=', 'c.id')
            ->join('stores as st', 's.store_id', '=', 'st.id')
            ->select([
                'pi.ingredient_id',
                'p_ingredient.name as ingredient_name',
                'p_ingredient.code as ingredient_code',
                'c.name as category_name',
                'u.name as unit_name',
                'st.name as store_name',
                's.store_id',
                'pi.quantity as recipe_quantity',
                DB::raw('SUM(sd.quantity) as total_sold_quantity'),
                DB::raw('COUNT(DISTINCT s.id) as total_transactions')
            ])
            ->where('p.is_processed', true)
            ->whereBetween('s.date', [$this->startDate, $this->endDate])
            ->groupBy([
                'pi.ingredient_id', 
                'p_ingredient.name', 
                'p_ingredient.code',
                'c.name',
                'u.name', 
                'st.name',
                's.store_id',
                'pi.quantity'
            ]);

        if ($this->storeId) {
            $query->where('s.store_id', $this->storeId);
        }

        if ($this->categoryId) {
            $query->where('p_ingredient.category_id', $this->categoryId);
        }

        $results = $query->orderBy('total_sold_quantity', 'desc')->get();

        // Process results same as controller
        return $results->map(function ($usage) {
            $totalQuantityUsed = $usage->total_sold_quantity * $usage->recipe_quantity;
            
            return (object) [
                'ingredient_code' => $usage->ingredient_code,
                'ingredient_name' => $usage->ingredient_name,
                'category_name' => $usage->category_name,
                'total_quantity_used' => $totalQuantityUsed,
                'unit_name' => $usage->unit_name,
                'total_transactions' => $usage->total_transactions,
                'store_name' => $usage->store_name
            ];
        })->sortByDesc('total_quantity_used');
    }

    public function headings(): array
    {
        return [
            'Kode Bahan',
            'Nama Bahan Baku',
            'Kategori',
            'Total Digunakan',
            'Satuan',
            'Total Transaksi',
            'Nama Outlet'
        ];
    }

    public function map($row): array
    {
        return [
            $row->ingredient_code,
            $row->ingredient_name,
            $row->category_name,
            number_format($row->total_quantity_used, 2),
            $row->unit_name,
            $row->total_transactions,
            $row->store_name
        ];
    }

    public function title(): string
    {
        return 'Laporan Penggunaan Bahan Baku';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
