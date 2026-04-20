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
}
