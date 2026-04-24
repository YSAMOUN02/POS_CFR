<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:vendors,code',
            'name' => 'required',
        ]);

        Vendor::create([
            'code' => $request->code,
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'country' => $request->country,
            'city' => $request->city,
            'email' => $request->email,
            'phone1' => $request->phone1,
            'phone2' => $request->phone2,
            'website' => $request->website,
            'status' => $request->status ? 1 : 0,
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function list(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone1', 'like', "%{$search}%")
                    ->orWhere('phone2', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%");
            });
        }

        if ($request->active == 1) {
            $query->where('status', 1);
        } else if ($request->active == 0) {
            $query->where('status', 0);
        }

        $vendors = $query->orderByDesc('id')->paginate(10);

        return response()->json([
            'data' => $vendors
        ]);
    }


    public function show($id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not found'
            ], 404);
        }

        return response()->json($vendor);
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not found'
            ], 404);
        }

        $request->validate([
            'code' => 'required|unique:vendors,code,' . $id,
            'name' => 'required',
        ]);

        $vendor->update([
            'code' => $request->code,
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'country' => $request->country,
            'city' => $request->city,
            'email' => $request->email,
            'phone1' => $request->phone1,
            'phone2' => $request->phone2,
            'website' => $request->website,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully'
        ]);
    }
public function search(Request $request)
{
    $query = $request->q;

    $vendors = Vendor::where('code', 'like', "%{$query}%")
        ->orWhere('name', 'like', "%{$query}%")
        ->limit(10)
        ->get(['id', 'code', 'name']);

    return response()->json($vendors);
}
}
