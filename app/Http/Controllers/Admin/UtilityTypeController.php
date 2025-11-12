<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UtilityType;
use Illuminate\Http\Request;

class UtilityTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $utilityTypes = UtilityType::latest()->paginate(10);
        return view('admin.utility-types.index', compact('utilityTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.utility-types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:utility_types',
            'unit_of_measurement' => 'required|string|max:255',
            'custom_unit' => 'required_if:unit_of_measurement,Other|nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle custom unit
        $unitOfMeasurement = $request->unit_of_measurement;
        if ($request->unit_of_measurement === 'Other' && $request->filled('custom_unit')) {
            $unitOfMeasurement = $request->custom_unit;
        }

        UtilityType::create([
            'name' => $request->name,
            'unit_of_measurement' => $unitOfMeasurement,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.utility-types.index')
                         ->with('success', 'Utility type created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  UtilityType  $utilityType
     * @return \Illuminate\Http\Response
     */
    public function show(UtilityType $utilityType)
    {
        return view('admin.utility-types.show', compact('utilityType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  UtilityType  $utilityType
     * @return \Illuminate\Http\Response
     */
    public function edit(UtilityType $utilityType)
    {
        return view('admin.utility-types.edit', compact('utilityType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  UtilityType  $utilityType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UtilityType $utilityType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:utility_types,name,' . $utilityType->id,
            'unit_of_measurement' => 'required|string|max:255',
            'custom_unit' => 'required_if:unit_of_measurement,Other|nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle custom unit
        $unitOfMeasurement = $request->unit_of_measurement;
        if ($request->unit_of_measurement === 'Other' && $request->filled('custom_unit')) {
            $unitOfMeasurement = $request->custom_unit;
        }

        $utilityType->update([
            'name' => $request->name,
            'unit_of_measurement' => $unitOfMeasurement,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.utility-types.index')
                         ->with('success', 'Utility type updated successfully.');
    }

    /**
     * Delete method disabled - data preservation policy
     * Utility types should be marked as inactive instead of deleted
     */
    public function destroy(UtilityType $utilityType)
    {
        return redirect()->route('admin.utility-types.index')
                         ->with('error', 'Deleting utility types is not allowed. Please mark as inactive instead.');
    }
}
