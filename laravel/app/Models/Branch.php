<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Branch extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'name',
        'phone',
        'address',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branchStocks()
    {
        return $this->hasMany(BranchStock::class);
    }
}
