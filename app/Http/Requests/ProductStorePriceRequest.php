<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProductStorePriceRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'store_id' => 'required|exists:stores,id',
            'base_price' => 'required|numeric|min:0',
            'unit_prices' => 'nullable|array',
            'unit_prices.*.unit_id' => 'required|exists:units,id',
            'unit_prices.*.selling_price' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'store_id.required' => 'Store harus dipilih',
            'base_price.required' => 'Harga dasar harus diisi',
            'base_price.numeric' => 'Harga dasar harus berupa angka',
            'base_price.min' => 'Harga dasar tidak boleh negatif',
        ];
    }
}
