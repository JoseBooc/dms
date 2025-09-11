<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\RoomAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaintenanceRequestController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        $requests = MaintenanceRequest::with('room')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(10);

        return view('maintenance-requests.index', compact('requests'));
    }

    public function create()
    {
        $tenant = auth()->user()->tenant;
        $active = RoomAssignment::with('room')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->first();

        if (!$active) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'You need an active room assignment to submit a maintenance request.');
        }

        return view('maintenance-requests.create', ['assignment' => $active]);
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high'],
            'area' => ['nullable', 'string', 'max:255'],
            'photos.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $active = RoomAssignment::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->first();

        if (!$active) {
            return back()->with('error', 'No active room assignment found.');
        }

        $paths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $paths[] = $file->store('maintenance-photos', 'public');
            }
        }

        MaintenanceRequest::create([
            'tenant_id' => $tenant->id,
            'room_id' => $active->room_id,
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'area' => $validated['area'] ?? null,
            'status' => 'pending',
            'photos' => $paths ?: null,
        ]);

        return redirect()->route('maintenance-requests.index')
            ->with('success', 'Maintenance request submitted.');
    }

    public function show(MaintenanceRequest $maintenance_request)
    {
        $this->authorizeOwner($maintenance_request);
        $maintenance_request->load(['room', 'tenant']);
        return view('maintenance-requests.show', compact('maintenance_request'));
    }

    public function edit(MaintenanceRequest $maintenance_request)
    {
        $this->authorizeOwner($maintenance_request);
        if ($maintenance_request->status !== 'pending') {
            return redirect()->route('maintenance-requests.index')
                ->with('error', 'Only pending requests can be edited.');
        }
        return view('maintenance-requests.edit', compact('maintenance_request'));
    }

    public function update(Request $request, MaintenanceRequest $maintenance_request)
    {
        $this->authorizeOwner($maintenance_request);
        if ($maintenance_request->status !== 'pending') {
            return redirect()->route('maintenance-requests.index')
                ->with('error', 'Only pending requests can be updated.');
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high'],
            'area' => ['nullable', 'string', 'max:255'],
            'photos.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $paths = $maintenance_request->photos ?? [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $paths[] = $file->store('maintenance-photos', 'public');
            }
        }

        $maintenance_request->update([
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'area' => $validated['area'] ?? null,
            'photos' => $paths ?: null,
        ]);

        return redirect()->route('maintenance-requests.index')
            ->with('success', 'Maintenance request updated.');
    }

    public function destroy(MaintenanceRequest $maintenance_request)
    {
        $this->authorizeOwner($maintenance_request);
        if ($maintenance_request->photos) {
            foreach ($maintenance_request->photos as $p) {
                Storage::disk('public')->delete($p);
            }
        }
        $maintenance_request->delete();
        return redirect()->route('maintenance-requests.index')
            ->with('success', 'Maintenance request deleted.');
    }

    public function updateStatus(Request $request, MaintenanceRequest $task)
    {
        $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed']
        ]);

        $task->status = $request->input('status');
        if ($task->status === 'in_progress' && !$task->assigned_to) {
            $task->assigned_to = auth()->id();
        }
        if ($task->status === 'completed' && !$task->assigned_to) {
            $task->assigned_to = auth()->id();
        }
        $task->save();

        return response()->json(['success' => true]);
    }

    protected function authorizeOwner(MaintenanceRequest $maintenance_request): void
    {
        $tenant = auth()->user()->tenant;
        abort_unless($maintenance_request->tenant_id === ($tenant->id ?? null), 403);
    }
}
