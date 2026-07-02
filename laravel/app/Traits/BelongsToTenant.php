<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Shop;

trait BelongsToTenant
{
    /**
     * The "booted" method automatically applies constraints across the Eloquent lifecycle.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->shop_id !== null) {
                // Whenever a record is created, automatically attach the user's shop_id
                $model->shop_id = auth()->user()->shop_id;
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->shop_id !== null) {
                // Whenever a record is queried, automatically append: WHERE shop_id = X
                $builder->where($builder->getModel()->getTable() . '.shop_id', auth()->user()->shop_id);
            }
        });
    }
}