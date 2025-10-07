<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->role === 'tenant') {
            return redirect('/dashboard/tenant-dashboard');
        }
        return redirect('/dashboard');
    }
    return redirect('/login');
});

// Redirect non-Filament login to Filament login
Route::get('/login', function () {
    return redirect('/dashboard/login');
})->name('login.redirect');

// Redirect register to Filament (if registration is disabled, this will show appropriate message)
Route::get('/register', function () {
    return redirect('/dashboard/login');
})->name('register.redirect');

// Redirect any admin URLs without proper prefix to dashboard
Route::get('/admin', function () {
    return redirect('/dashboard');
});

// Redirect old filament-admin URLs to new dashboard URLs
Route::get('/filament-admin{path?}', function ($path = '') {
    return redirect('/dashboard' . ($path ? '/' . ltrim($path, '/') : ''));
})->where('path', '.*');

Route::middleware(['auth'])->group(function () {
    // Legacy dashboard routes - redirect to Filament
    Route::get('/admin/dashboard', function () {
        return redirect('/dashboard');
    })->name('admin.dashboard');
    
    Route::get('/tenant/dashboard', function () {
        if (auth()->check() && auth()->user()->isTenant()) {
            return redirect()->route('tenant.dashboard');
        }
        return redirect('/dashboard');
    });
    

    
    // Debug route to check and update user role
    Route::get('/debug/user', function () {
        $user = auth()->user();
        $output = "Current User: " . $user->name . " (" . $user->email . ")<br>";
        $output .= "Current Role: " . $user->role . "<br>";
        
        // Update to tenant role if needed
        if ($user->role !== 'tenant') {
            $user->role = 'tenant';
            $user->save();
            $output .= "Role updated to: tenant<br>";
        }
        
        $output .= '<a href="/dashboard/tenant-dashboard">Go to Tenant Dashboard</a>';
        return $output;
    });
    
    Route::get('/staff/dashboard', function () {
        return redirect('/dashboard');
    })->name('staff.dashboard');

    // Admin routes
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::resource('room-assignments', \App\Http\Controllers\RoomAssignmentController::class);
        Route::get('room-assignments/check-availability', [\App\Http\Controllers\RoomAssignmentController::class, 'checkAvailability'])
            ->name('room-assignments.check-availability');
        Route::patch('room-assignments/{roomAssignment}/end', [\App\Http\Controllers\RoomAssignmentController::class, 'end'])
            ->name('room-assignments.end');
        Route::resource('rooms', \App\Http\Controllers\Admin\RoomController::class)->names('admin.rooms');
        Route::patch('rooms/{room}/toggle-hidden', [\App\Http\Controllers\Admin\RoomController::class, 'toggleHidden'])->name('admin.rooms.toggle-hidden');
        Route::patch('rooms/{room}/toggle-status', [\App\Http\Controllers\Admin\RoomController::class, 'toggleStatus'])->name('admin.rooms.toggle-status');
        Route::patch('rooms/{room}/update-rate', [\App\Http\Controllers\Admin\RoomController::class, 'updateRate'])->name('admin.rooms.update-rate');
        Route::resource('tenants', \App\Http\Controllers\TenantController::class);
        
        // Billing routes
        Route::resource('bills', \App\Http\Controllers\Admin\BillController::class)->names('admin.bills');
        Route::post('bills/generate-monthly', [\App\Http\Controllers\Admin\BillController::class, 'generateMonthlyBills'])
            ->name('admin.bills.generate-monthly');
        Route::patch('bills/{bill}/update-payment', [\App\Http\Controllers\Admin\BillController::class, 'updatePayment'])
            ->name('admin.bills.update-payment');
        
        // Utility Management routes
        Route::resource('utility-types', \App\Http\Controllers\Admin\UtilityTypeController::class)->names('admin.utility-types');
        Route::resource('utility-rates', \App\Http\Controllers\Admin\UtilityRateController::class)->names('admin.utility-rates');
        Route::resource('utility-readings', \App\Http\Controllers\Admin\UtilityReadingController::class)->names('admin.utility-readings');
        Route::get('utility-readings/previous-reading', [\App\Http\Controllers\Admin\UtilityReadingController::class, 'getPreviousReading'])
            ->name('admin.utility-readings.previous-reading');
        
        // Utility Billing routes
        Route::get('utility-billing', [\App\Http\Controllers\Admin\UtilityBillingController::class, 'index'])
            ->name('admin.utility-billing.index');
        Route::get('utility-billing/generate', [\App\Http\Controllers\Admin\UtilityBillingController::class, 'showGenerateForm'])
            ->name('admin.utility-billing.generate');
        Route::post('utility-billing/generate', [\App\Http\Controllers\Admin\UtilityBillingController::class, 'generateBills'])
            ->name('admin.utility-billing.generate.post');
        Route::get('utility-billing/calculate-consumption', [\App\Http\Controllers\Admin\UtilityBillingController::class, 'calculateConsumption'])
            ->name('admin.utility-billing.calculate-consumption');
    });

    // Tenant routes
    Route::middleware(['role:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/test', function() {
            return view('tenant.test-dashboard');
        })->name('test');
        Route::get('/dashboard', [\App\Http\Controllers\TenantController::class, 'dashboard'])->name('dashboard');
        Route::get('/bills', [\App\Http\Controllers\TenantController::class, 'bills'])->name('bills');
        Route::get('/bills/{bill}', [\App\Http\Controllers\TenantController::class, 'showBill'])->name('bills.show');
        
        Route::get('/maintenance', [\App\Http\Controllers\TenantController::class, 'maintenanceRequests'])->name('maintenance.index');
        Route::get('/maintenance/create', [\App\Http\Controllers\TenantController::class, 'createMaintenanceRequest'])->name('maintenance.create');
        Route::post('/maintenance', [\App\Http\Controllers\TenantController::class, 'storeMaintenanceRequest'])->name('maintenance.store');
        
        Route::get('/complaints', [\App\Http\Controllers\TenantController::class, 'complaints'])->name('complaints.index');
        Route::get('/complaints/create', [\App\Http\Controllers\TenantController::class, 'createComplaint'])->name('complaints.create');
        Route::post('/complaints', [\App\Http\Controllers\TenantController::class, 'storeComplaint'])->name('complaints.store');
        
        Route::get('/profile', [\App\Http\Controllers\TenantController::class, 'profile'])->name('profile');
        
        // Rent Information routes
        Route::prefix('rent')->name('rent.')->group(function () {
            Route::get('/details', [\App\Http\Controllers\TenantController::class, 'rentDetails'])->name('details');
            Route::get('/utilities', [\App\Http\Controllers\TenantController::class, 'utilityDetails'])->name('utilities');
            Route::get('/room-info', [\App\Http\Controllers\TenantController::class, 'roomInformation'])->name('room-info');
        });
    });

    Route::middleware(['role:tenant'])->group(function () {
        Route::resource('maintenance-requests', \App\Http\Controllers\MaintenanceRequestController::class);
    });

    // Staff routes
    Route::middleware(['role:staff'])->group(function () {
        Route::patch('/maintenance-tasks/{task}/status', [App\Http\Controllers\MaintenanceRequestController::class, 'updateStatus'])
            ->name('maintenance-tasks.update-status');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::middleware(['admin'])->name('admin.')->prefix('admin')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::patch('users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::resource('tenants', \App\Http\Controllers\Admin\TenantController::class);
        Route::resource('rooms', \App\Http\Controllers\Admin\RoomController::class);
        Route::patch('rooms/{room}/update-status', [\App\Http\Controllers\Admin\RoomController::class, 'updateStatus'])->name('rooms.update-status');
    });
});

require __DIR__.'/auth.php';
