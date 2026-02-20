<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TableController extends Controller
{

    public function GetTables(Request $request)
    {
        $hour = $request->input('hour'); // e.g., 14 for 2 PM

        $tables = RestaurantTable::with(['products'])->get();

        $tables->transform(function ($table) {
            // Sum all quantities in this table
            $totalQty = count($table->products);

            if($totalQty >0){

            } else {

                $table->queue_no = 0;
                $table->save();
            }

            return $table;
        });

        return response()->json($tables);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $table = RestaurantTable::create([

            'name' => $request->name,
            'queue_no' => 4,
            'customer_qty' => 1,
            'is_occupied' => false,
        ]);

        return response()->json([
            'success' => true,
            'table' => $table
        ]);
    }
}
