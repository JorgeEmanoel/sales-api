<?php

namespace Tests\Controllers\Product;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    protected string $method = 'POST';
    protected string $path = 'products';

    protected array $product_data = [
        'name' => 'Product Name',
        'quantity' => 1,
        'price' => 1,
        'provider_id' => 1
    ];

    /** @var App\Models\Provider */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = Provider::factory()->create();
    }

    /**
     * @test
     * @dataProvider invalidProductDataProvider
     */
    public function itShouldNotAcceptInvalidInputs(array $data)
    {
        $this->json($this->method, $this->path, $data)
            ->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        $this->notSeeInDatabase('products', $data);
    }

    /**
     * @test
     * @depends itShouldNotAcceptInvalidInputs
     */
    public function itShouldReturnUnprocessableWhenReceiveInvalidProviderId()
    {
        $this->json($this->method, $this->path, array_merge(
            $this->product_data,
            [
                    'provider_id' => 2
            ]
        ))
            ->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->notSeeInDatabase('products', $this->product_data);
    }

    /**
     * @test
     */
    public function itShouldAcceptValidInputAndStoreInDatabase()
    {
        $this->json($this->method, $this->path, $this->product_data)
            ->assertResponseStatus(Response::HTTP_CREATED);

        $this->seeInDatabase('products', $this->product_data);
    }

    public function invalidProductDataProvider()
    {
        return [
            'invalid name (max length exceeded)' => [array_merge(
                $this->product_data,
                [
                    'name' => Str::random(241)
                ]
            )],
            'missing name' => [array_merge(
                $this->product_data,
                [
                    'name' => null
                ]
            )],
            'invalid quantity (min)' => [array_merge(
                $this->product_data,
                [
                    'quantity' => -1
                ]
            )],
            'missing quantity' => [array_merge(
                $this->product_data,
                [
                    'quantity' => null
                ]
            )],
            'invalid price (non numeric)' => [array_merge(
                $this->product_data,
                [
                    'price' => 'price'
                ]
            )],
            'missing price' => [array_merge(
                $this->product_data,
                [
                    'price' => null
                ]
            )],
            'missing provider_id' => [array_merge(
                $this->product_data,
                [
                    'provider_id' => null
                ]
            )],
        ];
    }
}
