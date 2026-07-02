<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_name',
        'sku',
        'barcode',
        'cost_price',
        'selling_price',
        'tax_percentage',
        'discount',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branchStocks()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
