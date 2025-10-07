<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UtilityReading;
use App\Models\UtilityType;
use App\Models\UtilityRate;
use App\Models\Room;
use App\Models\Bill;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UtilityBillingController extends Controller
{
    /**
     * Show utility billing dashboard
     */
    public function index()
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');

        // Get utility consumption for current month
        $currentMonthConsumption = UtilityReading::with(['utilityType', 'room'])
            ->where('reading_date', '>=', now()->startOfMonth())
            ->where('reading_date', '<=', now()->endOfMonth())
            ->sum('consumption');

        // Get utility consumption for last month
        $lastMonthConsumption = UtilityReading::with(['utilityType', 'room'])
            ->where('reading_date', '>=', now()->subMonth()->startOfMonth())
            ->where('reading_date', '<=', now()->subMonth()->endOfMonth())
            ->sum('consumption');

        // Get unbilled readings
        $unbilledReadings = UtilityReading::with(['utilityType', 'room'])
            ->whereNull('bill_id')
            ->orderBy('reading_date', 'desc')
            ->paginate(10);

        // Get recent utility bills
        $recentBills = Bill::with(['tenant', 'room'])
            ->where('bill_type', 'utility')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.utility-billing.index', compact(
            'currentMonthConsumption',
            'lastMonthConsumption',
            'unbilledReadings',
            'recentBills'
        ));
    }

    /**
     * Generate utility bills for a specific period
     */
    public function generateBills(Request $request)
    {
        $request->validate([
            'billing_month' => 'required|date_format:Y-m',
            'utility_types' => 'array',
            'rooms' => 'array',
        ]);

        $billingMonth = Carbon::createFromFormat('Y-m', $request->billing_month);
        $startDate = $billingMonth->copy()->startOfMonth();
        $endDate = $billingMonth->copy()->endOfMonth();

        DB::beginTransaction();

        try {
            $billsGenerated = 0;
            $totalAmount = 0;

            // Get readings for the billing period
            $readingsQuery = UtilityReading::with(['utilityType', 'room.currentTenant'])
                ->whereBetween('reading_date', [$startDate, $endDate])
                ->where('consumption', '>', 0);

            // Filter by utility types if specified
            if ($request->has('utility_types') && !empty($request->utility_types)) {
                $readingsQuery->whereIn('utility_type_id', $request->utility_types);
            }

            // Filter by rooms if specified
            if ($request->has('rooms') && !empty($request->rooms)) {
                $readingsQuery->whereIn('room_id', $request->rooms);
            }

            $readings = $readingsQuery->get();

            // Group readings by room and utility type
            $groupedReadings = $readings->groupBy(['room_id', 'utility_type_id']);

            foreach ($groupedReadings as $roomId => $roomReadings) {
                $room = Room::with('currentTenant')->find($roomId);
                
                if (!$room || !$room->currentTenant) {
                    continue; // Skip rooms without tenants
                }

                foreach ($roomReadings as $utilityTypeId => $utilityReadings) {
                    $utilityType = UtilityType::find($utilityTypeId);
                    $totalConsumption = $utilityReadings->sum('consumption');

                    // Get applicable rate for the billing period
                    $rate = UtilityRate::where('utility_type_id', $utilityTypeId)
                        ->where('effective_from', '<=', $endDate)
                        ->where(function ($query) use ($startDate) {
                            $query->whereNull('effective_until')
                                  ->orWhere('effective_until', '>=', $startDate);
                        })
                        ->orderBy('effective_from', 'desc')
                        ->first();

                    if (!$rate) {
                        continue; // Skip if no rate is available
                    }

                    $amount = $totalConsumption * $rate->rate_per_unit;

                    // Create utility bill
                    $bill = Bill::create([
                        'tenant_id' => $room->currentTenant->id,
                        'room_id' => $room->id,
                        'bill_type' => 'utility',
                        'created_by' => auth()->id(),
                        'bill_date' => now(),
                        'room_rate' => 0,
                        'electricity' => $utilityType->name === 'Electricity' ? $amount : 0,
                        'water' => $utilityType->name === 'Water' ? $amount : 0,
                        'other_charges' => !in_array($utilityType->name, ['Electricity', 'Water']) ? $amount : 0,
                        'total_amount' => $amount,
                        'due_date' => $endDate->copy()->addDays(15), // 15 days after month end
                        'status' => 'unpaid',
                        'description' => "{$utilityType->name} consumption for {$billingMonth->format('F Y')} - {$totalConsumption} {$utilityType->unit}",
                        'details' => json_encode([
                            'utility_type' => $utilityType->name,
                            'consumption' => $totalConsumption,
                            'unit' => $utilityType->unit,
                            'rate_per_unit' => $rate->rate_per_unit,
                            'billing_period' => [
                                'start' => $startDate->format('Y-m-d'),
                                'end' => $endDate->format('Y-m-d'),
                            ],
                            'readings_count' => $utilityReadings->count(),
                        ]),
                    ]);

                    // Associate readings with the bill
                    foreach ($utilityReadings as $reading) {
                        $reading->update(['bill_id' => $bill->id]);
                    }

                    $billsGenerated++;
                    $totalAmount += $amount;
                }
            }

            DB::commit();

            return redirect()->route('admin.utility-billing.index')
                ->with('success', "Generated {$billsGenerated} utility bills totaling $" . number_format($totalAmount, 2));

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Failed to generate utility bills: ' . $e->getMessage());
        }
    }

    /**
     * Show bill generation form
     */
    public function showGenerateForm()
    {
        $utilityTypes = UtilityType::where('status', 'active')->get();
        $rooms = Room::with(['activeAssignment.tenant'])->orderBy('room_number')->get();
        
        // Get available billing months (months with readings)
        $availableMonths = UtilityReading::selectRaw('DATE_FORMAT(reading_date, "%Y-%m") as month')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->pluck('month')
            ->map(function ($month) {
                return [
                    'value' => $month,
                    'label' => Carbon::createFromFormat('Y-m', $month)->format('F Y'),
                ];
            });

        return view('admin.utility-billing.generate', compact('utilityTypes', 'rooms', 'availableMonths'));
    }

    /**
     * Calculate utility consumption for a room and period
     */
    public function calculateConsumption(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'utility_type_id' => 'required|exists:utility_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $readings = UtilityReading::with(['utilityType'])
            ->where('room_id', $request->room_id)
            ->where('utility_type_id', $request->utility_type_id)
            ->whereBetween('reading_date', [$request->start_date, $request->end_date])
            ->orderBy('reading_date')
            ->get();

        $totalConsumption = $readings->sum('consumption');
        
        // Get current rate
        $rate = UtilityRate::where('utility_type_id', $request->utility_type_id)
            ->where('effective_from', '<=', $request->end_date)
            ->where(function ($query) use ($request) {
                $query->whereNull('effective_until')
                      ->orWhere('effective_until', '>=', $request->start_date);
            })
            ->orderBy('effective_from', 'desc')
            ->first();

        $estimatedCost = $rate ? ($totalConsumption * $rate->rate_per_unit) : 0;

        return response()->json([
            'readings' => $readings->map(function ($reading) {
                return [
                    'date' => $reading->reading_date->format('Y-m-d'),
                    'consumption' => $reading->consumption,
                    'current_reading' => $reading->current_reading,
                    'previous_reading' => $reading->previous_reading,
                ];
            }),
            'total_consumption' => $totalConsumption,
            'rate_per_unit' => $rate ? $rate->rate_per_unit : 0,
            'estimated_cost' => $estimatedCost,
            'utility_type' => $readings->first()->utilityType ?? null,
        ]);
    }
}
