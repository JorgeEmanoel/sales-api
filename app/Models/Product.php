<?php

namespace App\Models;

use App\Helpers\Number;
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

    public function decreaseQuantity(int $quantity)
    {
        $this->quantity -= $quantity;

        if ($this->quantity < 0) {
            throw new \Exception('Invalid quantity');
        }

        return $this;
    }

    public function increaseQuantity(int $quantity)
    {
        $this->quantity += $quantity;
        return $this;
    }

    public function scopeFromProvider($query, $provider)
    {
        if ($provider instanceof Provider) {
            return $query->where('provider_id', $provider->id);
        }

        return $query->where('provider_id', $provider);
    }

    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = (new Number($value))->toInteger();
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (new Number($value))->toFloat();
    }
}
