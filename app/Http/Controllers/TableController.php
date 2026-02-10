<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class TableController extends Controller
{

public function GetTables(Request $request)
{
    $hour = $request->input('hour'); // e.g., 14 for 2 PM

    $tables = RestaurantTable::with(['products' => function($q) use ($hour) {
        if ($hour !== null) {
            $q->whereHour('created_at', $hour);
        }
    }])->get();

    $tables->transform(function ($table) {
        // Sum all quantities in this table
        $totalQty = $table->products->sum('qty');

        // Occupied if total quantity > 0
        $table->is_occupied = $totalQty > 0;
        $table->status_text = $table->is_occupied ? 'Occupied' : 'Available';

        return $table;
    });

    return response()->json($tables);
}



}
