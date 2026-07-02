<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display listing of employees.
     */
    public function index()
    {
        return response()->json(User::with('roles')->get());
    }

    /**
     * Store a new employee user account.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'branch_id' => 'nullable|exists:branches,id',
            'role'      => 'required|string',
        ]);

        $shop = auth()->user()->shop;
        
        // CHECK PLAN CAPACITY LIMIT FOR USERS
        if ($shop) {
            $plan = $shop->subscription;
            if ($plan) {
                $employeeCount = User::count();
                if ($employeeCount >= $plan->user_limit) {
                    return response()->json([
                        'error' => "Plan Limit Receeded: Your subscription plan tier ('{$plan->name}') caps staff at {$plan->user_limit} users."
                    ], 403);
                }
            }
        }

        $user = DB::transaction(function () use ($request, $shop) {
            $employee = User::create([
                'shop_id'   => auth()->user()->shop_id,
                'branch_id' => $request->branch_id,
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => bcrypt($request->password),
            ]);

            // Set Spatie scope to active shop
            app(PermissionRegistrar::class)->setPermissionsTeamId(auth()->user()->shop_id);
            
            $role = Role::findOrCreate($request->role, 'web');
            $employee->assignRole($role);

            return $employee;
        });

        return response()->json(['message' => 'Employee created successfully.', 'user' => $user->load('roles')]);
    }

    /**
     * Update employee profile or roles.
     */
    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $employee->id,
            'password'  => 'nullable|string|min:6',
            'branch_id' => 'nullable|exists:branches,id',
            'role'      => 'sometimes|string',
        ]);

        DB::transaction(function () use ($request, $employee) {
            $employee->fill($request->only(['name', 'email', 'branch_id']));
            
            if ($request->filled('password')) {
                $employee->password = bcrypt($request->password);
            }
            
            $employee->save();

            if ($request->filled('role')) {
                app(PermissionRegistrar::class)->setPermissionsTeamId(auth()->user()->shop_id);
                // Sync roles
                $role = Role::findOrCreate($request->role, 'web');
                $employee->syncRoles([$role]);
            }
        });

        return response()->json(['message' => 'Employee updated successfully.', 'user' => $employee->load('roles')]);
    }

    /**
     * Remove employee from shop.
     */
    public function destroy($id)
    {
        $employee = User::findOrFail($id);
        if ($employee->id === auth()->id()) {
            return response()->json(['error' => 'Cannot delete your own account.'], 400);
        }

        $employee->delete();
        return response()->json(['message' => 'Employee account deactivated successfully.']);
    }

    /**
     * List all Spatie system roles.
     */
    public function roles()
    {
        return response()->json(Role::all());
    }
}
