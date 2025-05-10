<?php

namespace App\Helpers;

use App\Models\Unit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UnitConverter
{
    /**
     * Daftar konversi khusus yang umum digunakan
     * Format: [nama_satuan_dari => [nama_satuan_ke => faktor_konversi]]
     */
    private static $commonConversions = [
        // Massa/Berat
        'Gram' => ['Kg' => 0.001], // 1 Gram = 0.001 Kg (perbaikan: menggunakan nilai yang benar)
        'Kg' => ['Gram' => 1000.0], // 1 Kg = 1000 Gram

        // Volume
        'Ml' => ['Liter' => 0.001], // 1 Ml = 0.001 Liter
        'Liter' => ['Ml' => 1000.0], // 1 Liter = 1000 Ml

        // Panjang
        'Cm' => ['Meter' => 0.01], // 1 Cm = 0.01 Meter
        'Meter' => ['Cm' => 100.0], // 1 Meter = 100 Cm

        // Unit dagang
        'PCS' => ['BOX' => 0.2], // 1 PCS = 0.2 BOX
        'BOX' => ['PCS' => 5.0], // 1 BOX = 5 PCS
    ];

    /**
     * Konversi nilai dari satu satuan ke satuan lain
     * Metode ini bekerja untuk semua jenis satuan yang ada di database
     *
     * @param float $quantity Jumlah yang akan dikonversi
     * @param int|Unit $fromUnit Satuan asal (ID atau objek Unit)
     * @param int|Unit $toUnit Satuan tujuan (ID atau objek Unit)
     * @return float|null Nilai hasil konversi, atau null jika konversi tidak dapat dilakukan
     */
    public static function convert($quantity, $fromUnit, $toUnit)
    {
        // Handle parameter yang bisa ID atau objek Unit
        $fromUnitObj = is_object($fromUnit) ? $fromUnit : Unit::find(intval($fromUnit));
        $toUnitObj = is_object($toUnit) ? $toUnit : Unit::find(intval($toUnit));

        if (!$fromUnitObj || !$toUnitObj) {
            Log::error('Unit konversi tidak ditemukan', [
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit
            ]);
            return null;
        }

        // Jika satuan sama, tidak perlu konversi
        if ($fromUnitObj->id === $toUnitObj->id) {
            return $quantity;
        }

        // Log informasi untuk debugging
        Log::info('Mencoba konversi satuan', [
            'from' => $fromUnitObj->name,
            'to' => $toUnitObj->name,
            'quantity' => $quantity,
            'from_is_base' => $fromUnitObj->is_base_unit,
            'to_is_base' => $toUnitObj->is_base_unit
        ]);

        // Cek konversi umum terlebih dahulu
        if (isset(self::$commonConversions[$fromUnitObj->name][$toUnitObj->name])) {
            $conversionFactor = self::$commonConversions[$fromUnitObj->name][$toUnitObj->name];
            $result = $quantity * $conversionFactor;

            Log::info('Menggunakan konversi umum yang telah ditentukan', [
                'from' => $fromUnitObj->name,
                'to' => $toUnitObj->name,
                'factor' => $conversionFactor,
                'result' => $result
            ]);

            return $result;
        }

        // KASUS 1: Konversi dari Satuan Dasar ke Satuan Turunannya
        // Contoh: BOX (satuan dasar) ke PCS (satuan turunan, dimana 1 BOX = 5 PCS)
        // Rumus: quantity * conversion_factor
        if ($fromUnitObj->is_base_unit && !$toUnitObj->is_base_unit && $toUnitObj->base_unit_id === $fromUnitObj->id) {
            $result = $quantity * $toUnitObj->conversion_factor;
            Log::info('Konversi dari Satuan Dasar ke Turunan', [
                'from' => $fromUnitObj->name,
                'to' => $toUnitObj->name,
                'formula' => "{$quantity} * {$toUnitObj->conversion_factor} = {$result}",
                'meaning' => "1 {$fromUnitObj->name} = {$toUnitObj->conversion_factor} {$toUnitObj->name}"
            ]);
            return $result;
        }

        // KASUS 2: Konversi dari Satuan Turunan ke Satuan Dasarnya
        // Contoh: PCS (satuan turunan) ke BOX (satuan dasar, dimana 1 PCS = 0.2 BOX)
        // Rumus: quantity * (1 / conversion_factor)
        if (!$fromUnitObj->is_base_unit && $toUnitObj->is_base_unit && $fromUnitObj->base_unit_id === $toUnitObj->id) {
            // Penting: Untuk konversi dari Gram ke Kg, kita perlu 1/1000
            $result = $quantity / $fromUnitObj->conversion_factor;
            Log::info('Konversi dari Satuan Turunan ke Dasar', [
                'from' => $fromUnitObj->name,
                'to' => $toUnitObj->name,
                'formula' => "{$quantity} / {$fromUnitObj->conversion_factor} = {$result}",
                'meaning' => "1 {$fromUnitObj->name} = " . (1 / $fromUnitObj->conversion_factor) . " {$toUnitObj->name}"
            ]);
            return $result;
        }

        // KASUS 3: Konversi antar Satuan Turunan dengan Satuan Dasar yang Sama
        // Contoh: BOX ke CARTON, dimana 1 BOX = 10 PCS dan 1 CARTON = 30 PCS
        // Rumus: quantity * (to_conversion_factor / from_conversion_factor)
        if (!$fromUnitObj->is_base_unit && !$toUnitObj->is_base_unit &&
            $fromUnitObj->base_unit_id === $toUnitObj->base_unit_id) {

            $result = $quantity * ($toUnitObj->conversion_factor / $fromUnitObj->conversion_factor);
            Log::info('Konversi antar Satuan Turunan', [
                'from' => $fromUnitObj->name,
                'to' => $toUnitObj->name,
                'from_factor' => $fromUnitObj->conversion_factor,
                'to_factor' => $toUnitObj->conversion_factor,
                'formula' => "{$quantity} * ({$toUnitObj->conversion_factor} / {$fromUnitObj->conversion_factor}) = {$result}",
                'meaning' => "1 {$fromUnitObj->name} = " . ($toUnitObj->conversion_factor / $fromUnitObj->conversion_factor) . " {$toUnitObj->name}"
            ]);
            return $result;
        }

        // KASUS 4: Konversi melalui Satuan Dasar sebagai Perantara
        // Contoh: BOX ke CASE, dimana BOX dan CASE memiliki satuan dasar berbeda
        if (!$fromUnitObj->is_base_unit && !$toUnitObj->is_base_unit &&
            $fromUnitObj->base_unit_id !== $toUnitObj->base_unit_id) {

            // Dapatkan satuan dasar dari kedua satuan
            $fromBaseUnit = Unit::find(intval($fromUnitObj->base_unit_id));
            $toBaseUnit = Unit::find(intval($toUnitObj->base_unit_id));

            if (!$fromBaseUnit || !$toBaseUnit) {
                Log::error('Satuan dasar tidak ditemukan', [
                    'from_base_id' => $fromUnitObj->base_unit_id,
                    'to_base_id' => $toUnitObj->base_unit_id
                ]);
                return null;
            }

            // Cek apakah satuan dasar ini memiliki relasi konversi khusus
            $baseConversion = self::getBaseUnitConversion($fromBaseUnit->id, $toBaseUnit->id);

            if ($baseConversion) {
                // Step 1: Konversi dari satuan turunan asal ke satuan dasarnya
                $intermediateValue = $quantity / $fromUnitObj->conversion_factor;

                // Step 2: Konversi dari satuan dasar asal ke satuan dasar tujuan
                $intermediateValue = $intermediateValue * $baseConversion;

                // Step 3: Konversi dari satuan dasar tujuan ke satuan turunan tujuan
                $result = $intermediateValue * $toUnitObj->conversion_factor;

                Log::info('Konversi melalui satuan dasar perantara', [
                    'from' => $fromUnitObj->name,
                    'from_base' => $fromBaseUnit->name,
                    'to_base' => $toBaseUnit->name,
                    'to' => $toUnitObj->name,
                    'result' => $result
                ]);

                return $result;
            }
        }

        // Jika tidak ada jalur konversi yang ditemukan
        Log::warning('Tidak ada jalur konversi yang valid', [
            'from' => $fromUnitObj->name,
            'to' => $toUnitObj->name
        ]);

        return null;
    }

    /**
     * Mendapatkan konversi antar satuan dasar, jika ada
     *
     * @param int $fromBaseUnitId
     * @param int $toBaseUnitId
     * @return float|null
     */
    private static function getBaseUnitConversion($fromBaseUnitId, $toBaseUnitId)
    {
        $fromBaseUnit = Unit::find(intval($fromBaseUnitId));
        $toBaseUnit = Unit::find(intval($toBaseUnitId));

        if (!$fromBaseUnit || !$toBaseUnit) {
            return null;
        }

        // Cek di konversi umum
        if (isset(self::$commonConversions[$fromBaseUnit->name][$toBaseUnit->name])) {
            return self::$commonConversions[$fromBaseUnit->name][$toBaseUnit->name];
        }

        if (isset(self::$commonConversions[$toBaseUnit->name][$fromBaseUnit->name])) {
            return 1 / self::$commonConversions[$toBaseUnit->name][$fromBaseUnit->name];
        }

        // Implementasi konversi khusus antar satuan dasar bisa ditambahkan di sini
        return null;
    }

    /**
     * Mendapatkan format string yang menggambarkan konversi satuan
     * Metode ini menangani semua jenis satuan dengan format 1Gram**Satuan Turunan**Kg0.0010
     *
     * @param int|Unit $fromUnit
     * @param int|Unit $toUnit
     * @param bool $useSpecialFormat Gunakan format khusus untuk POS
     * @return string
     */
    public static function getConversionFormat($fromUnit, $toUnit, $useSpecialFormat = false)
    {
        $fromUnitObj = is_object($fromUnit) ? $fromUnit : Unit::find(intval($fromUnit));
        $toUnitObj = is_object($toUnit) ? $toUnit : Unit::find(intval($toUnit));

        if (!$fromUnitObj || !$toUnitObj) {
            return 'Konversi tidak tersedia';
        }

        // Coba dapatkan faktor konversi
        $conversionFactor = null;

        // Dari konversi umum
        if (isset(self::$commonConversions[$fromUnitObj->name][$toUnitObj->name])) {
            $conversionFactor = self::$commonConversions[$fromUnitObj->name][$toUnitObj->name];
        }
        // Atau dari konversi dinamis
        else {
            $conversionFactor = self::convert(1, $fromUnitObj, $toUnitObj);
        }

        if ($conversionFactor === null) {
            return $useSpecialFormat ?
                "NoConversion**Satuan Turunan**{$fromUnitObj->name}{$toUnitObj->name}" :
                "Tidak ada konversi langsung dari {$fromUnitObj->name} ke {$toUnitObj->name}";
        }

        // Format khusus untuk POS (seperti "1Gram**Satuan Turunan**Kg0.0010")
        if ($useSpecialFormat) {
            return "1{$fromUnitObj->name}**Satuan Turunan**{$toUnitObj->name}" . number_format($conversionFactor, 4, '.', '');
        }

        // Format standar untuk UI
        return "1 {$fromUnitObj->name} = " . number_format($conversionFactor, 4, '.', '') . " {$toUnitObj->name}";
    }

    /**
     * Menghasilkan daftar semua konversi yang tersedia dalam sistem
     * Berguna untuk melihat semua pasangan konversi yang mungkin
     *
     * @return array
     */
    public static function getAllConversions()
    {
        $units = Unit::all();
        $conversions = [];

        // Tambahkan konversi umum terlebih dahulu
        foreach (self::$commonConversions as $fromUnitName => $toUnits) {
            $fromUnit = Unit::where('name', $fromUnitName)->first();
            if (!$fromUnit) continue;

            foreach ($toUnits as $toUnitName => $factor) {
                $toUnit = Unit::where('name', $toUnitName)->first();
                if (!$toUnit) continue;

                $conversions[] = [
                    'from_unit_id' => $fromUnit->id,
                    'from_unit_name' => $fromUnit->name,
                    'to_unit_id' => $toUnit->id,
                    'to_unit_name' => $toUnit->name,
                    'conversion_factor' => $factor,
                    'formatted' => "1 {$fromUnit->name} = " . number_format($factor, 4, '.', '') . " {$toUnit->name}",
                    'special_format' => "1{$fromUnit->name}**Satuan Turunan**{$toUnitObj->name}" . number_format($factor, 4, '.', '')
                ];
            }
        }

        // Tambahkan konversi berdasarkan relasi unit di database
        foreach ($units as $fromUnit) {
            foreach ($units as $toUnit) {
                if ($fromUnit->id === $toUnit->id) {
                    continue; // Skip konversi ke satuan yang sama
                }

                // Skip jika sudah ada di konversi umum
                $alreadyExists = false;
                foreach ($conversions as $conv) {
                    if ($conv['from_unit_id'] == $fromUnit->id && $conv['to_unit_id'] == $toUnit->id) {
                        $alreadyExists = true;
                        break;
                    }
                }

                if ($alreadyExists) {
                    continue;
                }

                $conversionFactor = self::convert(1, $fromUnit, $toUnit);
                if ($conversionFactor !== null) {
                    $conversions[] = [
                        'from_unit_id' => $fromUnit->id,
                        'from_unit_name' => $fromUnit->name,
                        'to_unit_id' => $toUnit->id,
                        'to_unit_name' => $toUnit->name,
                        'conversion_factor' => $conversionFactor,
                        'formatted' => self::getConversionFormat($fromUnit, $toUnit),
                        'special_format' => self::getConversionFormat($fromUnit, $toUnit, true)
                    ];
                }
            }
        }

        return $conversions;
    }

    /**
     * Mendapatkan konversi untuk satuan tertentu dari cache atau DB
     * Menggunakan cache untuk performa yang lebih baik
     *
     * @param int $unitId
     * @return array
     */
    public static function getConversionsForUnit($unitId)
    {
        $cacheKey = "unit_conversions_" . intval($unitId);

        return Cache::remember($cacheKey, 3600, function() use ($unitId) {
            $unit = Unit::find(intval($unitId));
            if (!$unit) {
                return [];
            }

            $conversions = [];

            // Tambahkan konversi umum terlebih dahulu
            if (isset(self::$commonConversions[$unit->name])) {
                foreach (self::$commonConversions[$unit->name] as $toUnitName => $factor) {
                    $toUnit = Unit::where('name', $toUnitName)->first();
                    if (!$toUnit) continue;

                    $conversions[] = [
                        'unit_id' => $toUnit->id,
                        'unit_name' => $toUnit->name,
                        'conversion_factor' => $factor,
                        'formatted' => "1 {$unit->name} = " . number_format($factor, 4, '.', '') . " {$toUnit->name}",
                        'special_format' => "1{$unit->name}**Satuan Turunan**{$toUnit->name}" . number_format($factor, 4, '.', '')
                    ];
                }
            }

            // Tambahkan konversi dari database
            $allUnits = Unit::where('id', '!=', intval($unitId))->get();

            foreach ($allUnits as $toUnit) {
                // Skip jika sudah ada di konversi umum
                $alreadyExists = false;
                foreach ($conversions as $conv) {
                    if ($conv['unit_id'] == $toUnit->id) {
                        $alreadyExists = true;
                        break;
                    }
                }

                if ($alreadyExists) {
                    continue;
                }

                $conversionFactor = self::convert(1, $unit, $toUnit);
                if ($conversionFactor !== null) {
                    $conversions[] = [
                        'unit_id' => $toUnit->id,
                        'unit_name' => $toUnit->name,
                        'conversion_factor' => $conversionFactor,
                        'formatted' => self::getConversionFormat($unit, $toUnit),
                        'special_format' => self::getConversionFormat($unit, $toUnit, true)
                    ];
                }
            }

            return $conversions;
        });
    }

    /**
     * Parse format khusus "1Gram**Satuan Turunan**Kg0.0010" untuk mendapatkan data konversi
     *
     * @param string $formatString
     * @return array|null
     */
    public static function parseSpecialFormat($formatString)
    {
        if (empty($formatString)) {
            return null;
        }

        // Coba parse format khusus
        if (preg_match('/^1([A-Za-z0-9]+)\*\*Satuan Turunan\*\*([A-Za-z0-9]+)([0-9.]+)$/', $formatString, $matches)) {
            return [
                'from_unit' => $matches[1],
                'to_unit' => $matches[2],
                'factor' => floatval($matches[3])
            ];
        }

        return null;
    }

    /**
     * Temukan satuan berdasarkan nama
     * Berguna saat memproses format khusus
     *
     * @param string $unitName
     * @return Unit|null
     */
    public static function findUnitByName($unitName)
    {
        return Unit::where('name', $unitName)->first();
    }

    /**
     * Hapus cache konversi satuan ketika ada perubahan pada satuan
     *
     * @param int $unitId
     * @return void
     */
    public static function clearConversionCache($unitId = null)
    {
        if ($unitId) {
            Cache::forget("unit_conversions_" . intval($unitId));
        } else {
            // Hapus semua cache konversi
            $units = Unit::all();
            foreach ($units as $unit) {
                Cache::forget("unit_conversions_{$unit->id}");
            }
        }
    }

    /**
     * Tampilkan semua konversi umum dalam format yang ditentukan
     *
     * @param bool $useSpecialFormat Gunakan format khusus untuk POS
     * @return array
     */
    public static function getAllCommonConversions($useSpecialFormat = false)
    {
        $result = [];

        foreach (self::$commonConversions as $fromUnitName => $toUnits) {
            foreach ($toUnits as $toUnitName => $factor) {
                if ($useSpecialFormat) {
                    $result[] = "1{$fromUnitName}**Satuan Turunan**{$toUnitName}" . number_format($factor, 4, '.', '');
                } else {
                    $result[] = "1 {$fromUnitName} = " . number_format($factor, 4, '.', '') . " {$toUnitName}";
                }
            }
        }

        return $result;
    }

    /**
     * Tambahkan konversi umum baru atau perbarui yang sudah ada
     *
     * @param string $fromUnitName
     * @param string $toUnitName
     * @param float $factor
     * @return bool
     */
    public static function addCommonConversion($fromUnitName, $toUnitName, $factor)
    {
        if (!isset(self::$commonConversions[$fromUnitName])) {
            self::$commonConversions[$fromUnitName] = [];
        }

        self::$commonConversions[$fromUnitName][$toUnitName] = $factor;

        // Cache clear
        $fromUnit = Unit::where('name', $fromUnitName)->first();
        $toUnit = Unit::where('name', $toUnitName)->first();

        if ($fromUnit) {
            self::clearConversionCache($fromUnit->id);
        }

        if ($toUnit) {
            self::clearConversionCache($toUnit->id);
        }

        return true;
    }
}
