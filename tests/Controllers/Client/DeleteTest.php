<?php

namespace Tests\Controllers\Client;

use App\Models\Client;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    protected $method = 'DELETE';
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
    public function itShouldSoftDeleteTheSpecifiedResourceAndReturnItsData()
    {
        $this->json($this->method, $this->path)
            ->seeJsonDoesntContains([
                'id',
                'name',
                'phone',
                'email'
            ])
            ->assertResponseStatus(Response::HTTP_OK);

        $this->seeInDatabase('clients', [
            'id' => $this->client->id
        ]);

        $this->notSeeInDatabase('clients', [
            'id' => $this->client->id,
            'deleted_at' => null
        ]);
    }
}
