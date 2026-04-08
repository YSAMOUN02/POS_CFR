<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function userListData(Request $request)
    {
        $query = \App\Models\User::query();

        // Search text
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role') && $request->role !== 'All') {
            $query->where('role', $request->role);
        }

        if ( $request->active != 'All') {
            $active = $request->active == '1' ? 1 : 0;
            $query->where('status', $active);
        }

        $users = $query->orderBy('id', 'desc')->get();

        return response()->json($users);
    }
 public function store_user(Request $request)
    {
        try {
            $request->validate([
                'display_name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'role' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'warehouses.*' => 'nullable|exists:warehouses,id',
                'password' => 'nullable|string|min:4',
            ]);

            $user = User::create([
                'name' => $request->display_name,
                'username' => $request->username,
                'role' => $request->role,
                'email' => $request->email,
                'password' => Hash::make($request->password), // default password
                'status' => 1, // active
            ]);

            // Attach warehouses
            if ($request->filled('warehouses')) {
                foreach ($request->warehouses as $wh_id) {
                    UserWarehouse::create([
                        'user_id' => $user->id,
                        'warehouse_id' => $wh_id,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function get_warehouse_list()
    {
        $warehouse =  Warehouse::select('id', 'name', 'location')
            ->get();

        return response()->json($warehouse);
    }

    public function store(Request $request)
    {
        // ---------------------------
        // Validation
        // ---------------------------
        $request->validate([
            'display_name' => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username',
            'role'         => 'required|string',
            'email'        => 'nullable|email',
            'password'     => 'required|min:6', // 👈 important
            'warehouses'   => 'required|array|min:1',
        ]);

        \DB::beginTransaction();

        try {
            // ---------------------------
            // Create User
            // ---------------------------
            $user = User::create([
                'name'     => $request->display_name,
                'username' => $request->username,
                'email'    => $request->email,
                'role'     => $request->role,
                'password' => Hash::make($request->password), // 🔥 hash here
                // optional:
                // 'password' => bcrypt('123456'),
            ]);

            // ---------------------------
            // Attach Warehouses
            // ---------------------------
            $user->warehouses()->sync($request->warehouses);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
