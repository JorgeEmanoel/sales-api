<?php

namespace Tests\Controllers\Provider;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Provider;
use Illuminate\Http\Response;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    /** @var Illuminate\Support\Collection; */
    protected $providers = [];

    protected string $method = 'GET';
    protected string $path = 'providers';

    protected function setUp(): void
    {
        parent::setUp();
        $this->providers = Provider::factory()->count(4)->create();
    }

    /**
     * @test
     */
    public function itShouldReturnAllCreatedProviders()
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
     * @depends itShouldReturnAllCreatedProviders
     * @dataProvider filterFieldsDataProvider
     */
    public function itShouldFilterMultipleFields(string $field)
    {
        $provider = $this->providers[0];

        $this->json($this->method, $this->path, [
                $field => $provider[$field]
            ])
            ->assertJson(json_encode([
                'data' => [
                    [
                        'id' => $provider->id
                    ]
                ],
                'pages' => 1
            ]));
    }

    public function filterFieldsDataProvider()
    {
        return [
            ['name'],
            ['document'],
            ['document_type']
        ];
    }
}
