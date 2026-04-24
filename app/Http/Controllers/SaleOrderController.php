<?php

namespace App\Http\Controllers;

use App\Models\SaleOrderHeader;
use Illuminate\Http\Request;

class SaleOrderController extends Controller
{
    public function getSaleOrders(Request $request)
{
    $query = SaleOrderHeader::query();

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('contact_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('document_no', 'like', "%{$search}%");
        });
    }

    // Payment priority first
    $query->orderByRaw("
        CASE payment_status
            WHEN 'unpaid' THEN 1
            WHEN 'partial' THEN 2
            WHEN 'paid' THEN 3
            ELSE 4
        END
    ");

    // Then document status
    $query->orderByRaw("
        CASE status
            WHEN 'ordered' THEN 1
            WHEN 'deposit_paid' THEN 2
            WHEN 'draft' THEN 3
            WHEN 'completed' THEN 4
            WHEN 'cancelled' THEN 5
            ELSE 6
        END
    ");

    // latest inside same group
    $query->orderByDesc('id');

    return response()->json($query->paginate(20));
}
public function getSaleOrderLines($id)
{
    $saleOrder = SaleOrderHeader::with('lines')->find($id);

    if (!$saleOrder) {
        return response()->json([], 404);
    }

    $lines = $saleOrder->lines->map(function ($line) {
        $quantity = (float) ($line->quantity ?? 0);
        $sellPrice = (float) ($line->sell_price ?? 0);
        $discountAmount = (float) ($line->discount_amount ?? 0);
        $vatAmount = (float) ($line->vat_amount ?? 0);

        $subTotal = $quantity * $sellPrice;
        $grandTotal = ($subTotal - $discountAmount) + $vatAmount;

        return [
            'id' => $line->id,
            'item_code' => $line->item_code ?? '',
            'name' => $line->name ?? '',
            'quantity' => $quantity,
            'sell_price' => $sellPrice,
            'sub_total' => $subTotal,
            'discount_amount' => $discountAmount,
            'vat_amount' => $vatAmount,
            'grand_total_amount' => $line->grand_total_amount ?? $grandTotal,
        ];
    });

    return response()->json([
        'header' => $saleOrder,
        'lines' => $lines
    ]);
}
}
