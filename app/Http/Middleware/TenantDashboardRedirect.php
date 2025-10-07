<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantDashboardRedirect
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
        // If user is authenticated and is a tenant, redirect to tenant dashboard
        if (Auth::check() && Auth::user()->role === 'tenant') {
            // If they're trying to access the main dashboard, redirect to tenant dashboard
            if ($request->is('dashboard') || $request->is('dashboard/*')) {
                // Only redirect if they're not already on a tenant-specific page
                if (!$request->is('dashboard/tenant-dashboard') && 
                    !$request->is('dashboard/rent-details') && 
                    !$request->is('dashboard/utility-details') && 
                    !$request->is('dashboard/room-information') &&
                    !$request->is('dashboard/tenant-bill-resources*')) {
                    return redirect('/dashboard/tenant-dashboard');
                }
            }
        }

        return $next($request);
    }
}
