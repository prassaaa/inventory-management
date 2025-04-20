<?php

namespace App\Helpers;

use App\Models\Unit;
use App\Models\ProductUnit;

class UnitConversion
{
    /**
     * Convert quantity from one unit to another for a specific product
     * 
     * @param float $quantity The quantity to convert
     * @param int $fromUnitId The source unit ID
     * @param int $toUnitId The target unit ID
     * @param int $productId The product ID
     * @return float|null The converted quantity or null if conversion is not possible
     */
    public static function convert($quantity, $fromUnitId, $toUnitId, $productId)
    {
        // If units are the same, no conversion needed
        if ($fromUnitId == $toUnitId) {
            return $quantity;
        }

        // Get product units
        $fromProductUnit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $fromUnitId)
            ->first();
            
        $toProductUnit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $toUnitId)
            ->first();

        if (!$fromProductUnit || !$toProductUnit) {
            return null;
        }

        // Convert to base unit quantity first, then to target unit
        $baseUnitQuantity = $quantity * $fromProductUnit->conversion_value;
        $targetUnitQuantity = $baseUnitQuantity / $toProductUnit->conversion_value;

        return $targetUnitQuantity;
    }

    /**
     * Get the base unit for a product
     * 
     * @param int $productId The product ID
     * @return Unit|null The base unit or null if not found
     */
    public static function getBaseUnit($productId)
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) {
            return null;
        }

        return $product->baseUnit;
    }

    /**
     * Convert price from one unit to another for a specific product
     * 
     * @param float $price The price to convert
     * @param int $fromUnitId The source unit ID
     * @param int $toUnitId The target unit ID
     * @param int $productId The product ID
     * @param string $priceType The price type (purchase_price or selling_price)
     * @return float|null The converted price or null if conversion is not possible
     */
    public static function convertPrice($price, $fromUnitId, $toUnitId, $productId, $priceType = 'selling_price')
    {
        // If units are the same, no conversion needed
        if ($fromUnitId == $toUnitId) {
            return $price;
        }

        // Get product units
        $fromProductUnit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $fromUnitId)
            ->first();
            
        $toProductUnit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $toUnitId)
            ->first();

        if (!$fromProductUnit || !$toProductUnit) {
            return null;
        }

        // Get unit conversion ratio
        $ratio = $toProductUnit->conversion_value / $fromProductUnit->conversion_value;

        // Convert price based on ratio and price type
        if ($priceType == 'purchase_price') {
            return $price * $ratio;
        } else {
            return $price * $ratio;
        }
    }
}