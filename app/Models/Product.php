<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'quantity',
        'price',
        'provider_id'
    ];

    public function scopeFromProvider($query, $provider)
    {
        if ($provider instanceof Provider) {
            return $query->where('provider_id', $provider->id);
        }

        return $query->where('provider_id', $provider);
    }

    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = intval($value);
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (float) str_replace(
            ',',
            '.',
            str_replace(
                '.',
                '',
                $value
            )
        );
    }
}
