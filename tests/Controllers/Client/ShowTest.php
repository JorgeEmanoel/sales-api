<?php

namespace Tests\Controllers\Client;

use App\Models\Client;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    protected $method = 'GET';
    protected $path = 'clients';

    /** @var App\Models\Client */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Client::factory()->create();
        $this->path = "clients/{$this->client->id}";
    }

    /**
     * @test
     */
    public function itShouldNotReturnDataWhenReceiveAnInvalidId()
    {
        $this->json($this->method, 'clients/2')
            ->seeJsonDoesntContains([
                'id',
                'name',
                'phone',
                'email'
            ])
            ->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function itShouldReturnDataWhenReceiveAValidId()
    {
        $this->json($this->method, $this->path)
            ->seeJson([
                'id' => $this->client->id,
                'name' => $this->client->name,
                'email' => $this->client->email,
                'phone' => $this->client->phone
            ])
            ->assertResponseStatus(Response::HTTP_OK);
    }
}
