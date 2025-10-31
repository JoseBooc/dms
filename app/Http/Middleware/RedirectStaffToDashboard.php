<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectStaffToDashboard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and is a staff member
        if (Auth::check() && Auth::user()->role === 'staff') {
            // If trying to access the main dashboard, redirect to staff dashboard
            if ($request->is('admin') || $request->is('admin/dashboard') || $request->is('dashboard')) {
                return redirect()->to('/admin/staff-dashboard');
            }
        }

        return $next($request);
    }
}
