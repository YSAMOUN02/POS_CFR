<?php

namespace App\Http\Controllers;

use App\Models\SaleOrderHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // Status priority first
    $query->orderByRaw("
        CASE status
            WHEN 'Ordered' THEN 1
            WHEN 'Deposit' THEN 2
            WHEN 'Quotation' THEN 3
            WHEN 'Draft' THEN 4
            WHEN 'Completed' THEN 5
            WHEN 'Returned' THEN 6
            WHEN 'Cancelled' THEN 7
            ELSE 8
        END
    ");

    // Payment priority second
    $query->orderByRaw("
        CASE payment_status
            WHEN 'Unpaid' THEN 1
            WHEN 'Partial' THEN 2
            WHEN 'Paid' THEN 3
            WHEN 'Refunded' THEN 4
            WHEN 'N/A' THEN 5
            ELSE 6
        END
    ");

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
                'unit' => $line->unit ?? '',
                'sell_price' => $sellPrice,
                'sub_total' => $subTotal,
                'discount_amount' => $discountAmount,
                'vat_amount' => $vatAmount,
                'grand_total_amount' => $line->grand_total_amount ?? $grandTotal,
            ];
        });

        return response()->json([
            'header' => [
                'id' => $saleOrder->id,
                'document_no' => $saleOrder->document_no,
                'contact_name' => $saleOrder->contact_name,
                'phone' => $saleOrder->phone,
                'address' => $saleOrder->address,

                'posting_date' => $saleOrder->posting_date,
                'delivery_date' => $saleOrder->delivery_date,
                'order_date' => $saleOrder->order_date,
                'status' => $saleOrder->status,
                'payment_status' => $saleOrder->payment_status,

                'customer_type' => $saleOrder->customer_type,
                'payment_method' => $saleOrder->payment_method,

                'delivery_status' => $saleOrder->delivery_status,
                'delivery_info' => $saleOrder->delivery_info,
                'driver_name' => $saleOrder->driver_name,
                'driver_phone' => $saleOrder->driver_phone,

                'total_amount' => $saleOrder->total_amount,
                'vat_amount' => $saleOrder->vat_amount,
                'discount_amount' => $saleOrder->discount_amount,
                'paid_amount' => $saleOrder->paid_amount,
                'balance_amount' => $saleOrder->balance_amount,
                'grand_total' => $saleOrder->grand_total,

                'remarks' => $saleOrder->remarks,
                'created_by' => $saleOrder->created_by,
            ],
            'lines' => $lines
        ]);
    }
    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'sale_order_id' => 'required|integer|exists:sale_order_headers,id',
                'status' => 'required|string|in:Draft,Quotation,Ordered,Deposit,Completed,Cancelled,Returned',
            ]);

            $saleOrder = SaleOrderHeader::findOrFail($request->sale_order_id);

            $updateData = [
                'status' => $request->status,
                'updated_by' => Auth::user()->name ?? 'System',
            ];

            if ($request->status === 'Cancelled') {
                $updateData['payment_status'] = 'N/A';
                $updateData['delivery_status'] = 'N/A';
            }

            if ($request->status === 'Returned') {
                $updateData['payment_status'] = 'Refunded';
                $updateData['delivery_status'] = 'Returned';
            }

            $saleOrder->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Sale order status updated successfully',
                'status' => $saleOrder->status,
                'payment_status' => $saleOrder->payment_status,
                'delivery_status' => $saleOrder->delivery_status,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sale order not found',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
