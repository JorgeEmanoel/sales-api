<?php

namespace Tests\Controllers\Sale;

use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Sale;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    /** @var App\Models\Provider */
    protected $provider;

    /** @var App\Models\Product */
    protected $product;

    /** @var App\Models\Client */
    protected $client;

    /** @var App\Models\Sale */
    protected $sale;

    protected string $method = 'POST';
    protected string $path = 'sales';

    protected array $sale_data = [
        'payment_method' => Sale::PAYMENT_METHOD_CARD,
        'status' => Sale::STATUS_PAID,
        'client_id' => null,
        'products' => [
            [
                'id' => null,
                'quantity' => 10
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Client::factory()->create();
        $this->provider = Provider::factory()->create();
        $this->product = Product::factory()->create([
            'provider_id' => $this->provider->id
        ]);

        $this->sale_data['products'][0]['id'] = $this->product->id;
        $this->sale_data['client_id'] = $this->client->id;
    }

    /**
     * @test
     * @dataProvider invalidSaleDataProvider
     */
    public function itShouldNotAcceptInvalidInput(array $data)
    {
        $this->json($this->method, $this->path, $data)
            ->seeJsonDoesntContains([
                'id',
                'total',
                'status',
                'payment_method',
                'client_id'
            ])
            ->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @depends itShouldNotAcceptInvalidInput
     */
    public function itShouldValidateTheClientExistence()
    {
        $this->json($this->method, $this->path, array_merge(
            $this->sale_data,
            ['client_id' => 0]
        ))
            ->seeJsonDoesntContains([
                'id',
                'total',
                'status',
                'payment_method',
                'client_id'
            ])
            ->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function itShouldValidateEachProductExistence()
    {
        $this->json($this->method, $this->path, array_merge(
            $this->sale_data,
            [
                'products' => [
                    [
                        'id' => 0,
                        'quantity' => 1
                    ]
                ]
            ]
        ))
            ->seeJsonDoesntContains([
                'id',
                'total',
                'status',
                'payment_method',
                'client_id'
            ])
            ->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function itShouldValidateEachProductQuantity()
    {
        $this->json($this->method, $this->path, array_merge(
            $this->sale_data,
            [
                'products' => [
                    [
                        'id' => $this->product->id,
                        'quantity' => $this->product->quantity + 1
                    ]
                ]
            ]
        ))
            ->seeJsonDoesntContains([
                'id',
                'total',
                'status',
                'payment_method',
                'client_id'
            ])
            ->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function itShouldPlaceTheSaleWhenTheDataIsValid()
    {
        $this->json($this->method, $this->path, $this->sale_data)
            ->seeJsonDoesntContains([
                'id',
                'total',
                'status',
                'payment_method',
                'client_id'
            ])
            ->assertResponseStatus(Response::HTTP_CREATED);

        $this->seeInDatabase('sales', [
            'payment_method' => $this->sale_data['payment_method'],
            'status' => $this->sale_data['status'],
            'total' => $this->product->price * $this->sale_data['products'][0]['quantity'],
            'client_id' => $this->sale_data['client_id']
        ]);
    }

    /**
     * @test
     * @depends itShouldPlaceTheSaleWhenTheDataIsValid
     */
    public function itShouldUpdateProductsQuantitiesAfterPlaceTheSale()
    {
        $this->json($this->method, $this->path, $this->sale_data)
            ->assertResponseStatus(Response::HTTP_CREATED);

        $this->seeInDatabase('products', [
            'id' => $this->product->id,
            'quantity' => $this->product->quantity - $this->sale_data['products'][0]['quantity']
        ]);
    }

    public function invalidSaleDataProvider()
    {
        return [
            'invalid payment_method' => [array_merge(
                $this->sale_data,
                [
                    'payment_method' => 'invalid'
                ]
            )],
            'missing payment_method' => [array_merge(
                $this->sale_data,
                [
                    'payment_method' => null
                ]
            )],
            'invalid status' => [array_merge(
                $this->sale_data,
                [
                    'status' => 'invalid'
                ]
            )],
            'missing status' => [array_merge(
                $this->sale_data,
                [
                    'status' => null
                ]
            )],
            'missing client_id' => [array_merge(
                $this->sale_data,
                [
                    'client_id' => null
                ]
            )],
            'missing products' => [array_merge(
                $this->sale_data,
                [
                    'products' => []
                ]
            )],
            'missing products.id' => [array_merge(
                $this->sale_data,
                [
                    [
                        'products' => [
                            [
                                'id' => null
                            ]
                        ]
                    ]
                ]
            )],
            'missing products.quantity' => [array_merge(
                $this->sale_data,
                [
                    [
                        'products' => [
                            [
                                'quantity' => null
                            ]
                        ]
                    ]
                ]
            )],
            'invalid products.paid_unit_price' => [array_merge(
                $this->sale_data,
                [
                    [
                        'products' => [
                            [
                                'paid_unit_price' => 'two cents'
                            ]
                        ]
                    ]
                ]
            )],
        ];
    }
}
