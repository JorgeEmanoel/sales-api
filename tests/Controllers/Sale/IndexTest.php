<?php

namespace Tests\Controllers\Sale;

use App\Models\Client;
use App\Models\Sale;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    /** @var Illuminate\Support\Collection; */
    protected $clients;

    /** @var App\Models\Sale */
    protected $sale;

    protected string $method = 'GET';
    protected string $path = 'sales';

    protected function setUp(): void
    {
        parent::setUp();
        $this->clients = Client::factory()->count(2)->create();
        $this->sales = Sale::factory()->count(2)->create([
            'client_id' => $this->clients[0]->id
        ])->merge(Sale::factory()->count(2)->create([
            'client_id' => $this->clients[1]->id
        ]));
    }

    /**
     * @test
     */
    public function itShouldReturnAllCreatedSales()
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
     * @depends itShouldReturnAllCreatedSales
     * @depends itShouldPaginate
     * @dataProvider filterDataProvider
     */
    public function itShouldFilterByClientId(
        int $client_index,
        array $expected_sale_indexes
    ) {
        $this->json($this->method, $this->path, [
                'client_id' => $this->clients[$client_index]->id
            ])
            ->assertJson(json_encode([
                'data' => [
                    [
                        'id' => $this->sales[$expected_sale_indexes[0]]->id
                    ],
                    [
                        'id' => $this->sales[$expected_sale_indexes[1]]->id
                    ]
                ],
                'pages' => 1
            ]));
    }

    /**
     * @test
     * @depends itShouldReturnAllCreatedSales
     * @depends itShouldPaginate
     * @dataProvider filterDataProvider
     */
    public function itShouldFilterByStatuses()
    {
        $this->json($this->method, $this->path, [
                'statuses' => implode(',', [
                    $this->sales[0]->status,
                    $this->sales[1]->status
                ])
            ])
            ->assertJson(json_encode([
                'data' => [
                    [
                        'id' => $this->sales[0]->id
                    ],
                    [
                        'id' => $this->sales[1]->id
                    ]
                ],
                'pages' => 1
            ]));
    }

    /**
     * @test
     * @depends itShouldReturnAllCreatedSales
     * @depends itShouldPaginate
     * @dataProvider filterDataProvider
     */
    public function itShouldFilterByPaymentMethods()
    {
        $this->json($this->method, $this->path, [
                'payment_methods' => implode(',', [
                    $this->sales[0]->payment_method,
                    $this->sales[1]->payment_method
                ])
            ])
            ->assertJson(json_encode([
                'data' => [
                    [
                        'id' => $this->sales[0]->id
                    ],
                    [
                        'id' => $this->sales[1]->id
                    ]
                ],
                'pages' => 1
            ]));
    }

    public function filterDataProvider()
    {
        return [
            'sales from first client' => [
                0,
                [0, 1]
            ],
            'sales from second client' => [
                1,
                [1, 2]
            ],
        ];
    }
}
