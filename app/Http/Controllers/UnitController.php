<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Product;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::with('baseUnit')->orderBy('name')->get();
        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $baseUnits = Unit::where('is_base_unit', true)->orderBy('name')->get();
        return view('units.create', compact('baseUnits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units',
            'is_base_unit' => 'sometimes|boolean',
            'base_unit_id' => 'required_if:is_base_unit,0|nullable|exists:units,id',
            'conversion_factor' => 'required_if:is_base_unit,0|nullable|numeric|min:0.0001',
        ]);

        // Set is_base_unit to boolean value
        $validated['is_base_unit'] = isset($validated['is_base_unit']) && $validated['is_base_unit'] == 1;

        // If this is a base unit, clear base_unit_id and set conversion_factor to 1
        if ($validated['is_base_unit']) {
            $validated['base_unit_id'] = null;
            $validated['conversion_factor'] = 1;
        }

        Unit::create($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        $baseUnits = Unit::where('is_base_unit', true)
            ->where('id', '!=', $unit->id)
            ->orderBy('name')
            ->get();
            
        return view('units.edit', compact('unit', 'baseUnits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'is_base_unit' => 'sometimes|boolean',
            'base_unit_id' => 'required_if:is_base_unit,0|nullable|exists:units,id',
            'conversion_factor' => 'required_if:is_base_unit,0|nullable|numeric|min:0.0001',
        ]);

        // Set is_base_unit to boolean value
        $validated['is_base_unit'] = isset($validated['is_base_unit']) && $validated['is_base_unit'] == 1;

        // If this is a base unit, clear base_unit_id and set conversion_factor to 1
        if ($validated['is_base_unit']) {
            $validated['base_unit_id'] = null;
            $validated['conversion_factor'] = 1;
        }

        // Check if we're changing from a base unit to derived unit
        if (!$validated['is_base_unit'] && $unit->is_base_unit) {
            // Check if other units are using this as a base unit
            if (Unit::where('base_unit_id', $unit->id)->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Cannot change this unit from base unit to derived because other units are using it as a base unit.');
            }
        }

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        // Check if unit is used as a base unit for other units
        if (Unit::where('base_unit_id', $unit->id)->exists()) {
            return redirect()->route('units.index')
                ->with('error', 'Cannot delete unit because it is used as a base unit for other units.');
        }

        // Check if unit is used by products
        if (Product::where('base_unit_id', $unit->id)->exists() || 
            $unit->productUnits()->exists()) {
            return redirect()->route('units.index')
                ->with('error', 'Cannot delete unit because it is used by products.');
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Unit deleted successfully.');
    }
}