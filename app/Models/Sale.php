<?php

namespace App\Models;

use App\Helpers\Number;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'total',
        'status',
        'payment_method',
        'client_id'
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_PAID = 'paid';
    public const STATUS_PLACED = 'placed';

    public const PAYMENT_METHOD_CARD = 'card';
    public const PAYMENT_METHOD_CASH = 'cash';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleProducts()
    {
        return $this->hasMany(SaleProduct::class);
    }

    /**
     * @return $this
     */
    public function calculateTotal()
    {
        $this->total = $this->saleProducts()->get()->reduce(function ($total, $sale_product) {
            return $total + ($sale_product->quantity * $sale_product->paid_unit_price);
        }, 0);

        return $this;
    }

    public function cancel()
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }

    /**
     * @return bool
     */
    public function cancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function scopeFromClient($query, $client)
    {
        if ($client instanceof Client) {
            return $query->where('client_id', $client->id);
        }

        return $query->where('client_id', $client);
    }

    public function scopeWithStatuses($query, array $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopeWithPaymentMethods($query, array $methods)
    {
        return $query->whereIn('payment_methods', $methods);
    }

    public function setTotalAttribute($value)
    {
        $this->attributes['total'] = (new Number($value))->toFloat();
    }
}
