<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{



    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_code' => 'required|unique:customers,customer_code',
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'address' => 'required|string|max:255',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'type' => 'required|in:walk_in,member,vip',
                'credit_limit' => 'nullable|numeric|min:0',
            ],
            [
                'customer_code.required' => 'Customer code is required.',
                'customer_code.unique'   => 'This customer code already exists.',
                'name.required'          => 'Please enter customer name.',
                'address.required'       => 'Please enter address.',
                'type.required'          => 'Please select customer type.',
                'email.email'            => 'Please enter a valid email address.',
                'credit_limit.numeric'   => 'Credit limit must be a number.',
            ]
        );

        $validated['status']  = $request->has('status');
        $validated['balance'] = 0;
        $validated['point']   = 0;

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'customer' => $customer
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        // Search by customer code OR name
        $customers = Customer::where('customer_code', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->limit(10) // limit results
            ->get(['customer_code', 'name']);

        // Return JSON
        return response()->json($customers);
    }

    public function list_search(Request $request)
    {
        $limit = $request->query('limit', 20);

        $query = Customer::query();

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('customer_code', 'like', "%$s%")
                    ->orWhere('name', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%");
            });
        }

        // Filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        if ($request->filled('sort_by') && in_array($request->sort_by, [
            'id',
            'customer_code',
            'name',
            'phone',
            'email',
            'type',
            'credit_limit',
            'balance',
            'point',
            'status'
        ])) {
            $dir = $request->query('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $dir);
        } else {
            $query->orderBy('id', 'desc');
        }

        return response()->json($query->paginate($limit));
    }
    // UPDATE
    public function update(Request $request, Customer $customer)
    {
        try {
            $customer->update($request->only([
                'name',
                'phone',
                'email',
                'address',
                'city',
                'country',
                'type',
                'credit_limit',
                'balance',
                'point',
                'status'
            ]));

            return response()->json([
                'message' => 'Customer updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE (you already have this)
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return response()->json([
                'message' => 'Customer deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
