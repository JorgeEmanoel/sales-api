<?php

namespace Tests\Controllers\Provider;

use App\Models\Provider;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    protected $method = 'DELETE';
    protected $path = 'providers';

    /** @var App\Models\Provider */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = Provider::factory()->create();
        $this->path = "providers/{$this->provider->id}";
    }

    /**
     * @test
     */
    public function itShouldNotReturnDataWhenReceiveAnInvalidId()
    {
        $this->json($this->method, 'providers/2')
            ->seeJsonDoesntContains([
                'id',
                'name',
                'document',
                'document_type'
            ])
            ->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function itShouldSoftDeleteTheSpecifiedResourceAndRetournItsData()
    {
        $this->json($this->method, $this->path)
            ->seeJsonDoesntContains([
                'id',
                'name',
                'phone',
                'email'
            ])
            ->assertResponseStatus(Response::HTTP_OK);

        $this->seeInDatabase('providers', [
            'id' => $this->provider->id
        ]);

        $this->notSeeInDatabase('providers', [
            'id' => $this->provider->id,
            'deleted_at' => null
        ]);
    }
}
