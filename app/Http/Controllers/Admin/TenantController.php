<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Tenant::with(['user', 'roomAssignments.room'])->whereHas('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('personal_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by room assigned
        if ($request->filled('room')) {
            $query->whereHas('roomAssignments', function($q) use ($request) {
                $q->where('room_id', $request->room)
                  ->where('status', 'active');
            });
        }

        // Filter by status (based on room assignment)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('roomAssignments', function($q) {
                    $q->where('status', 'active');
                });
            } elseif ($request->status === 'inactive') {
                $query->whereDoesntHave('roomAssignments', function($q) {
                    $q->where('status', 'active');
                });
            }
        }

        // Filter by date created
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $tenants = $query->latest()->paginate(10)->appends($request->query());
        
        // Get rooms for filter dropdown
        $rooms = Room::orderBy('room_number')->get();
        
        return view('admin.tenants.index', compact('tenants', 'rooms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.tenants.create');
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
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:' . now()->subYears(18)->format('Y-m-d'),
            'gender' => 'required|in:male,female,other',
            'civil_status' => 'required|in:single,married,divorced,widowed,separated',
            'nationality' => 'required|string|max:255',
            'occupation' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'personal_email' => 'required|email|unique:tenants,personal_email',
            'permanent_address' => 'required|string',
            'current_address' => 'nullable|string',
            'id_type' => 'required|string|max:255',
            'id_number' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        // Create user account for the tenant
        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->personal_email,
            'password' => bcrypt('password'), // Default password
            'role' => 'tenant',
            'status' => 'active',
            'gender' => $request->gender,
        ]);

        // Create tenant record
        $tenantData = $request->all();
        $tenantData['user_id'] = $user->id;
        
        Tenant::create($tenantData);

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant and user account created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tenant = Tenant::with(['user', 'roomAssignments.room', 'emergencyContacts'])->findOrFail($id);
        return view('admin.tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);
        return view('admin.tenants.edit', compact('tenant'));
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
        $tenant = Tenant::findOrFail($id);
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:' . now()->subYears(18)->format('Y-m-d'),
            'gender' => 'required|in:male,female,other',
            'civil_status' => 'required|in:single,married,divorced,widowed,separated',
            'nationality' => 'required|string|max:255',
            'occupation' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'personal_email' => 'required|email|unique:tenants,personal_email,' . $tenant->id,
            'permanent_address' => 'required|string',
            'current_address' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $tenant->update($validated);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant updated successfully.');
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
