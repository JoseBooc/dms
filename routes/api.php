<?php

use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();
    
    // Check if user can login (not blocked or inactive)
    if (!AuthenticationService::checkUserStatusBeforeLogin($user)) {
        AuthenticationService::logBlockedLoginAttempt(
            $user,
            $request->ip(),
            $request->userAgent()
        );
        
        return response()->json([
            'message' => AuthenticationService::getBlockedLoginMessage($user),
            'error' => 'account_blocked_or_inactive',
        ], 403);
    }
    
    return $request->user();
});
