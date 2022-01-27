<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'total' => mt_rand(1, 200),
            'status' => $this->faker->randomElement([
                Sale::STATUS_CANCELLED,
                Sale::STATUS_PAID,
                Sale::STATUS_PENDING,
                Sale::STATUS_PLACED
            ]),
            'payment_method' => $this->faker->randomElement([
                Sale::PAYMENT_METHOD_CARD,
                Sale::PAYMENT_METHOD_CASH
            ]),
            'client_id' => mt_rand(1, 200)
        ];
    }
}
