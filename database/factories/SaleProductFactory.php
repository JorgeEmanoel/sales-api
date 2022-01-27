<?php

namespace Database\Factories;

use App\Models\SaleProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SaleProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $data = [
            'quantity' => mt_rand(1, 200),
            'paid_unit_price' => mt_rand(1, 200),
            'sale_id' => mt_rand(1, 200),
            'product_id' => mt_rand(1, 200),
        ];

        $data['total'] = $data['quantity'] * $data['paid_unit_price'];
        return $data;
    }
}
