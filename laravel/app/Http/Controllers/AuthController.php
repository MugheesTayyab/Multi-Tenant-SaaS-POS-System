<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Authenticate an employee user and start a session.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();

            // Set Spatie permissions team ID context immediately after login
            $teamId = $user->shop_id ?? 0;
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($teamId);
            
            // Check if shop is active if user belongs to a shop
            if ($user->shop_id) {
                $shop = $user->shop;
                if (!$shop || $shop->status !== 'approved') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return response()->json(['error' => 'Your shop is currently pending approval or is suspended.'], 403);
                }
            }

            return response()->json([
                'message' => 'Logged in successfully.',
                'user'    => $user->load('roles')
            ]);
        }

        return response()->json(['error' => 'The provided credentials do not match our records.'], 422);
    }

    /**
     * Close the user session.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Retrieve the currently authenticated user context.
     */
    public function me()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['user' => null], 401);
        }

        // Set Spatie permissions team ID context
        $teamId = $user->shop_id ?? 0;
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($teamId);

        // Return user with roles, permissions, shop details, and branch details
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'shop_id' => $user->shop_id,
                'branch_id' => $user->branch_id,
                'shop' => $user->shop_id ? $user->shop()->first() : null,
                'branch' => $user->branch_id ? $user->branch()->first() : null,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
        ]);
    }
}
