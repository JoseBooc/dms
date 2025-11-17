<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Bill;
use App\Models\MaintenanceRequest;
use App\Models\Complaint;
use App\Models\RoomAssignment;
use App\Models\UtilityReading;
use App\Models\UtilityType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isTenant()) {
                abort(403, 'Access denied. Tenant access required.');
            }
            return $next($request);
        });
    }



    public function bills()
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        $bills = collect(); // Empty collection by default
        
        if ($tenant) {
            $bills = Bill::where('tenant_id', $tenant->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $bills = Bill::whereRaw('1 = 0')->paginate(10); // Empty paginated collection
        }

        return view('tenant.bills.index', compact('bills'));
    }

    public function showBill(Bill $bill)
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant || $bill->tenant_id !== $tenant->id) {
            abort(403);
        }

        return view('tenant.bills.show', compact('bill'));
    }

    public function maintenanceRequests()
    {
        $requests = MaintenanceRequest::where('tenant_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tenant.maintenance.index', compact('requests'));
    }

    public function createMaintenanceRequest()
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'No tenant profile found.');
        }
        
        // Check if tenant has an active room assignment
        $activeAssignment = $tenant->roomAssignments()
            ->where('status', 'active')
            ->exists();
            
        if (!$activeAssignment) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'You cannot submit a maintenance request because you do not have an active room assignment. Please contact the administration if you believe this is an error.');
        }
        
        return view('tenant.maintenance.create');
    }

    public function storeMaintenanceRequest(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'No tenant profile found.');
        }
        
        // Check if tenant has an active room assignment
        $activeAssignment = $tenant->roomAssignments()
            ->where('status', 'active')
            ->first();
            
        if (!$activeAssignment) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'You cannot submit a maintenance request because you do not have an active room assignment. Please contact the administration if you believe this is an error.');
        }

        $validated = $request->validate([
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'area' => 'nullable|string|max:100',
        ]);

        MaintenanceRequest::create([
            'tenant_id' => $user->id,
            'room_id' => $activeAssignment->room_id,
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'area' => $validated['area'],
            'status' => 'pending',
        ]);

        return redirect()->route('tenant.maintenance.index')
            ->with('success', 'Maintenance request submitted successfully.');
    }

    public function complaints()
    {
        $complaints = Complaint::where('tenant_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tenant.complaints.index', compact('complaints'));
    }

    public function createComplaint()
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'No tenant profile found.');
        }
        
        // Check if tenant has an active room assignment
        $activeAssignment = $tenant->roomAssignments()
            ->where('status', 'active')
            ->exists();
            
        if (!$activeAssignment) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'You cannot submit a complaint because you do not have an active room assignment. Please contact the administration if you believe this is an error.');
        }
        
        return view('tenant.complaints.create');
    }

    public function storeComplaint(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'No tenant profile found.');
        }
        
        // Check if tenant has an active room assignment
        $activeAssignment = $tenant->roomAssignments()
            ->where('status', 'active')
            ->first();
            
        if (!$activeAssignment) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'You cannot submit a complaint because you do not have an active room assignment. Please contact the administration if you believe this is an error.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
        ]);

        Complaint::create([
            'tenant_id' => $user->id,
            'room_id' => $activeAssignment->room_id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'status' => 'pending',
        ]);

        return redirect()->route('tenant.complaints.index')
            ->with('success', 'Complaint submitted successfully.');
    }

    public function profile()
    {
        return view('tenant.profile', ['user' => Auth::user()]);
    }

    // New Rent Information Views
    public function rentDetails()
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return view('tenant.rent.details', [
                'currentAssignment' => null,
                'latestBill' => null,
                'nextDueBill' => null,
                'monthlyRate' => 0
            ]);
        }
        
        // Get current room assignment (active, inactive, or pending)
        $currentAssignment = RoomAssignment::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'inactive', 'pending'])
            ->with('room')
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'inactive' THEN 2 WHEN status = 'pending' THEN 3 END")
            ->first();

        if (!$currentAssignment) {
            return view('tenant.rent.details', [
                'currentAssignment' => null,
                'latestBill' => null,
                'nextDueBill' => null,
                'monthlyRate' => 0
            ]);
        }

        // Get latest bill
        $latestBill = Bill::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get next due bill (unpaid bill with nearest due date)
        $nextDueBill = Bill::where('tenant_id', $tenant->id)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->first();

        $monthlyRate = $currentAssignment->room->rate ?? 0;

        return view('tenant.rent.details', compact(
            'currentAssignment',
            'latestBill',
            'nextDueBill',
            'monthlyRate'
        ));
    }

    public function utilityDetails()
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return view('tenant.rent.utilities', [
                'currentAssignment' => null,
                'utilityReadings' => collect(),
                'utilityTypes' => collect()
            ]);
        }
        
        // Get current room assignment (active, inactive, or pending)
        $currentAssignment = RoomAssignment::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'inactive', 'pending'])
            ->with('room')
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'inactive' THEN 2 WHEN status = 'pending' THEN 3 END")
            ->first();

        if (!$currentAssignment) {
            return view('tenant.rent.utilities', [
                'currentAssignment' => null,
                'utilityReadings' => collect(),
                'utilityTypes' => collect()
            ]);
        }

        // Get all utility types
        $utilityTypes = UtilityType::where('status', 'active')->get();

        // Get latest utility readings for this room
        $utilityReadings = UtilityReading::where('room_id', $currentAssignment->room_id)
            ->where(function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id) // Tenant-specific readings
                      ->orWhereNull('tenant_id'); // Room-level readings (shared/split)
            })
            ->with(['utilityType', 'recordedBy'])
            ->whereIn('utility_type_id', $utilityTypes->pluck('id'))
            ->orderBy('reading_date', 'desc')
            ->get()
            ->groupBy('utility_type_id')
            ->map(function ($readings) {
                return $readings->first(); // Get the latest reading for each utility type
            });

        return view('tenant.rent.utilities', compact(
            'currentAssignment',
            'utilityReadings',
            'utilityTypes'
        ));
    }

    public function roomInformation()
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return view('tenant.rent.room-info', [
                'currentAssignment' => null,
                'roommates' => collect(),
                'room' => null
            ]);
        }
        
        // Get current room assignment (active, inactive, or pending)
        $currentAssignment = RoomAssignment::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'inactive', 'pending'])
            ->with('room')
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'inactive' THEN 2 WHEN status = 'pending' THEN 3 END")
            ->first();

        if (!$currentAssignment) {
            return view('tenant.rent.room-info', [
                'currentAssignment' => null,
                'roommates' => collect(),
                'room' => null
            ]);
        }

        $room = $currentAssignment->room;

        // Get all active roommates in the same room (excluding current user)
        $roommates = RoomAssignment::where('room_id', $room->id)
            ->where('status', 'active')
            ->where('tenant_id', '!=', $user->id)
            ->with(['tenant' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'phone_number', 'personal_email');
            }])
            ->get();

        return view('tenant.rent.room-info', compact(
            'currentAssignment',
            'roommates',
            'room'
        ));
    }
}