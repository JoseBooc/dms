<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UtilityReading;
use Illuminate\Http\Request;

class UtilityReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $utilityReadings = UtilityReading::with(['utilityType', 'room', 'tenant', 'recordedBy'])
            ->latest()
            ->paginate(10);
        return view('admin.utility-readings.index', compact('utilityReadings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $utilityTypes = \App\Models\UtilityType::where('status', 'active')->get();
        $rooms = \App\Models\Room::where('status', 'available')->orWhere('status', 'occupied')->get();
        return view('admin.utility-readings.create', compact('utilityTypes', 'rooms'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $utilityReading = UtilityReading::with(['room', 'utilityType', 'recordedBy'])->findOrFail($id);
        return view('admin.utility-readings.show', compact('utilityReading'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $utilityReading = UtilityReading::with(['room', 'utilityType'])->findOrFail($id);
        $rooms = Room::all();
        $utilityTypes = UtilityType::all();
        return view('admin.utility-readings.edit', compact('utilityReading', 'rooms', 'utilityTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
