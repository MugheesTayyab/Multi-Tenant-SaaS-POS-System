<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Setting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'key',
        'value',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
