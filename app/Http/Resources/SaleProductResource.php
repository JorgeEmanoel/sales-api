<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->product->name,
            'total' => $this->total,
            'quantity' => $this->quantity,
            'paid_unit_price' => $this->paid_unit_price,
        ];
    }
}
