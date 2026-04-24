<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ItemLedgerEntry extends Model
{


    protected $table = 'item_ledger_entries';

    protected $fillable = [
        'entry_no',
        'posting_date',

        'document_type',
        'document_no',

        'source_id',
        'source_table',

        'product_id',
        'barcode',
        'item_code',
        'name',
        'variant',
        'description',
        'unit',
        'category_name',
        'type',

        'warehouse_id',
        'warehouse_name',
        'lot',
        'expire_date',

        'quantity',
        'remaining_quantity',
        'entry_type',

        'unit_cost',
        'unit_price',
        'sell_price',
        'discount_percent',
        'discount_amount',
        'vat',
        'vat_amount',
        'line_amount',
        'net_amount',
        'grand_total_amount',

        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'vendor_id',

        'payment_method',
        'remark',
        'created_by',
    ];

    protected $casts = [
        'posting_date'         => 'date',
        'expire_date'          => 'date',

        'quantity'             => 'decimal:4',
        'remaining_quantity'   => 'decimal:4',

        'unit_cost'            => 'decimal:4',
        'unit_price'           => 'decimal:4',
        'sell_price'           => 'decimal:4',

        'discount_percent'     => 'decimal:2',
        'discount_amount'      => 'decimal:4',

        'vat'                  => 'decimal:2',
        'vat_amount'           => 'decimal:4',

        'line_amount'          => 'decimal:4',
        'net_amount'           => 'decimal:4',
        'grand_total_amount'   => 'decimal:4',
    ];
}
