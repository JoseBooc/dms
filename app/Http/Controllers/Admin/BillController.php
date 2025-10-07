<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bills = Bill::with(['tenant', 'room'])->latest()->paginate(10);
        return view('admin.bills.index', compact('bills'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $rooms = \App\Models\Room::with(['activeAssignment.tenant'])->get();
        $tenants = \App\Models\Tenant::all();
        return view('admin.bills.create', compact('rooms', 'tenants'));
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
     * @param  Bill  $bill
     * @return \Illuminate\Http\Response
     */
    public function show(Bill $bill)
    {
        $bill->load(['tenant', 'room', 'createdBy']);
        return view('admin.bills.show', compact('bill'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Bill  $bill
     * @return \Illuminate\Http\Response
     */
    public function edit(Bill $bill)
    {
        return view('admin.bills.edit', compact('bill'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Bill  $bill
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'room_id' => 'required|exists:rooms,id',
            'bill_type' => 'required|in:rent,utility,maintenance,other',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'description' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        // Map 'amount' from form to 'total_amount' for database
        $validated['total_amount'] = $validated['amount'];
        unset($validated['amount']);

        // Add created_by if not already set (for new records this would be needed in store method)
        if (!isset($validated['created_by'])) {
            $validated['created_by'] = auth()->id();
        }

        // If status is not paid, clear paid_at
        if ($validated['status'] !== 'paid') {
            $validated['paid_at'] = null;
        }

        $bill->update($validated);

        return redirect()->route('admin.bills.index')->with('success', 'Bill updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Bill  $bill
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bill $bill)
    {
        //
    }
}
