<?php

namespace App\Http\Controllers;

use App\Models\ItemLedgerEntry;
use Illuminate\Http\Request;

class ItemLedgerEntryController extends Controller
{
    public function latest(Request $request)
    {
        $entries = ItemLedgerEntry::query()
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json($entries);
    }
}
