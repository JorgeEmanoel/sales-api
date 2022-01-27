<?php

namespace Tests\Controllers\Provider;

use App\Models\Provider;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    protected $method = 'GET';
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
    public function itShouldReturnDataWhenReceiveAValidId()
    {
        $this->json($this->method, $this->path)
            ->seeJson([
                'id' => $this->provider->id,
                'name' => $this->provider->name,
                'document' => $this->provider->document,
                'document_type' => $this->provider->document_type
            ])
            ->assertResponseStatus(Response::HTTP_OK);
    }
}
