<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Provider::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $data = [
            'name' => $this->faker->name,
            'document_type' => $this->faker->randomElement([
                Provider::DOCUMENT_CPF,
                Provider::DOCUMENT_CNPJ
            ]),
            'shared' => $this->faker->randomElement([true, false])
        ];

        $data['document'] = $this->documentByType($data['document_type']);
        return $data;
    }

    private function documentByType(string $type)
    {
        $document = [
            Provider::DOCUMENT_CPF => $this->faker->numerify('###########'),
            Provider::DOCUMENT_CNPJ => $this->faker->numerify('##############')
        ];

        return $document[$type];
    }
}
