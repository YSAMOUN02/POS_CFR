<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseHeader extends Model
{
    protected $table = 'purchase_headers';

    protected $fillable = [
        'no',
        'vendor_id',
        'posting_date',

        'payment_method',
        'created_by',
        'remark',
    ];
    public function lines()
    {
        return $this->hasMany(PurchaseLine::class, 'document_no', 'no');
    }
}
