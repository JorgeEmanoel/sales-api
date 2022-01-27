<?php

namespace Tests\Controllers\Product;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Response;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    /** @var Illuminate\Support\Collection; */
    protected $products = [];

    /** @var Illuminate\Support\Collection; */
    protected $providers = [];

    protected string $method = 'GET';
    protected string $path = 'products';

    protected function setUp(): void
    {
        parent::setUp();
        $this->providers = Provider::factory()->count(2)->create();
        $this->products = Product::factory()->count(2)->create([
            'provider_id' => $this->providers[0]->id
        ]);

        $this->products = $this->products->merge(Product::factory()->count(2)->create([
            'provider_id' => $this->providers[1]->id
        ]));
    }

    /**
     * @test
     */
    public function itShouldReturnAllCreatedProducts()
    {
        $response = $this->json($this->method, $this->path);
        $response->seeJson([
                'pages' => 1,
            ])
            ->seeJsonStructure([
                'data',
                'pages'
            ])
            ->assertResponseStatus(Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function itShouldPaginate()
    {
        $this->json($this->method, $this->path, [
                'per_page' => 1
            ])
            ->seeJsonContains([
                'pages' => 4
            ]);
    }

    /**
     * @test
     * @depends itShouldReturnAllCreatedProducts
     * @dataProvider filterDataProvider
     */
    public function itShouldFilterByProviderId(
        int $provider_index,
        array $expected_product_indexes
    ) {
        $this->json($this->method, $this->path, [
                'provider_id' => $this->providers[$provider_index]->id
            ])
            ->assertJson(json_encode([
                'data' => [
                    [
                        'id' => $this->products[$expected_product_indexes[0]]->id
                    ],
                    [
                        'id' => $this->products[$expected_product_indexes[1]]->id
                    ]
                ],
                'pages' => 1
            ]));
    }

    public function filterDataProvider()
    {
        return [
            'products from first provider' => [
                0,
                [0, 1]
            ],
            'products from second provider' => [
                1,
                [1, 2]
            ],
        ];
    }
}
