<?php

namespace App\Models;

use App\Helpers\Number;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleProduct extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'quantity',
        'total',
        'paid_unit_price',
        'sale_id',
        'product_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateTotal()
    {
        $this->total = $this->paid_unit_price * $this->quantity;
        return $this;
    }

    public function setTotalAttribute($value)
    {
        $this->attributes['total'] = (new Number($value))->toFloat();
    }

    public function setPaidUnitPrice($value)
    {
        $this->attributes['paid_unit_price'] = (new Number($value))->toFloat();
    }
}
