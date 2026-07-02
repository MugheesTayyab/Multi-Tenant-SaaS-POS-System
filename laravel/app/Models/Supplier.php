<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Supplier extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'name',
        'contact_name',
        'email',
        'phone',
        'address',
        'balance',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
