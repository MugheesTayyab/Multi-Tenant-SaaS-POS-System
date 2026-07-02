<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class BranchStock extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'branch_id',
        'product_variant_id',
        'quantity',
        'low_stock_alert',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
