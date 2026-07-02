<?php

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ShopController extends Controller
{
    /**
     * Public Route: Process a new vendor shop registration inquiry request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'shop_name'   => 'required|string|max:255',
            'owner_name'  => 'required|string|max:255',
            'email'       => 'required|email|unique:shops,email|unique:users,email',
            'phone'       => 'required|string',
            'address'     => 'required|string',
            'subscription_id' => 'sometimes|integer|exists:subscriptions,id'
        ]);

        $subId = $request->subscription_id ?? 1; // Default to Free tier

        Shop::create([
            'name'            => $request->shop_name,
            'owner_name'       => $request->owner_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'address'         => $request->address,
            'subscription_id' => $subId, 
            'status'          => 'pending',
        ]);

        return response()->json(['message' => 'Registration submitted successfully! Your account is currently pending review from our system Super Admin.']);
    }

    /**
     * Admin Route: Display all registered storefront containers across the network.
     */
    public function index()
    {
        $shops = Shop::with('subscription')->get();
        return response()->json($shops);
    }

    /**
     * Admin Route: Approve a tenant storefront and generate their administrative corporate owner credentials.
     */
    public function approve($id)
    {
        $shop = Shop::findOrFail($id);
        
        if ($shop->status !== 'pending' && $shop->status !== 'rejected') {
            return response()->json(['error' => 'This shop has already been approved or processed.'], 400);
        }

        // 1. Transition the corporate status marker
        $shop->update(['status' => 'approved']);

        // 2. Check if owner user already exists, or create a new one
        $owner = User::where('email', $shop->email)->first();
        if (!$owner) {
            $owner = User::create([
                'shop_id'   => $shop->id,
                'name'      => $shop->owner_name,
                'email'     => $shop->email,
                'password'  => bcrypt('welcome123'), // Temporary starter password configuration
            ]);
        } else {
            $owner->update([
                'shop_id' => $shop->id,
            ]);
        }

        // 3. Set Spatie scope context to their brand new shop ID and award them the Shop Owner role
        app(PermissionRegistrar::class)->setPermissionsTeamId($shop->id);
        
        $role = Role::findOrCreate('Shop Owner', 'web');
        $owner->assignRole($role);

        return response()->json(['message' => "Shop '{$shop->name}' has been approved successfully! Owner account generated."]);
    }

    /**
     * Admin Route: Reject a shop registration.
     */
    public function reject($id)
    {
        $shop = Shop::findOrFail($id);
        $shop->update(['status' => 'rejected']);
        return response()->json(['message' => "Shop '{$shop->name}' has been rejected."]);
    }

    /**
     * Admin Route: Suspend an approved shop.
     */
    public function suspend($id)
    {
        $shop = Shop::findOrFail($id);
        $shop->update(['status' => 'suspended']);
        return response()->json(['message' => "Shop '{$shop->name}' has been suspended."]);
    }

    /**
     * Admin Route: Re-activate a suspended shop.
     */
    public function activate($id)
    {
        $shop = Shop::findOrFail($id);
        $shop->update(['status' => 'approved']);
        return response()->json(['message' => "Shop '{$shop->name}' has been activated."]);
    }
}
