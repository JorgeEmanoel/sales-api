<?php

namespace Tests\Controllers\Client;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Client;
use Illuminate\Http\Response;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    /** @var Illuminate\Support\Collection; */
    protected $clients = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->clients = Client::factory()->count(4)->create();
    }

    /**
     * @test
     */
    public function itShouldReturnAllCreatedClients()
    {
        $response = $this->json('GET', 'clients');
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
        $this->json('GET', 'clients', [
                'per_page' => 1
            ])
            ->seeJsonContains([
                'pages' => 4
            ]);
    }

    /**
     * @test
     * @depends itShouldReturnAllCreatedClients
     */
    public function itShouldFilterByName()
    {
        $response = $this->call('GET', 'clients', [
            'name' => $this->clients[0]->name
        ]);

        $data = $response->json();
        $this->assertEquals(count($data['data']), 1);
    }

    /**
     * @test
     * @depends itShouldReturnAllCreatedClients
     */
    public function itShouldFilterByEmail()
    {
        $response = $this->call('GET', 'clients', [
            'email' => $this->clients[0]->email
        ]);

        $data = $response->json();
        $this->assertEquals(count($data['data']), 1);
    }
}
