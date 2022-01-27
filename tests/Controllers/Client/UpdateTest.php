<?php

namespace Tests\Controllers\Client;

use App\Models\Client;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    protected $method = 'PUT';
    protected $path = 'clients';

    protected array $client_data = [
        'name' => 'Client Name',
        'phone' => '00000000000',
        'email' => 'thruthy@email.com'
    ];

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
     * @dataProvider invalidClientDataProvider
     */
    public function itShouldNotAcceptInvalidInputs(array $data)
    {
        $this->json($this->method, $this->path, $data)
            ->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        $this->notSeeInDatabase('clients', $data);
    }

    /**
     * @test
     */
    public function itShouldAcceptValidInputAndStoreInDatabase()
    {
        $this->json($this->method, $this->path, $this->client_data)
            ->assertResponseStatus(Response::HTTP_OK);

        $this->seeInDatabase('clients', array_merge(
            ['id' => $this->client->id],
            $this->client_data
        ));
    }

    /**
     * @test
     */
    public function itShouldNotCareAboutDuplicatedEmail()
    {
        $this->json($this->method, $this->path, $this->client_data)
            ->assertResponseStatus(Response::HTTP_OK);

        $this->json($this->method, $this->path, $this->client_data)
            ->assertResponseStatus(Response::HTTP_OK);

        $this->seeInDatabase('clients', array_merge(
            ['id' => $this->client->id],
            $this->client_data
        ));
    }

    public function invalidClientDataProvider()
    {
        return [
            'invalid name' => [
                array_merge($this->client_data, [
                  'name' => Str::random(241)
                ])
            ],
            'missing name' => [
                array_merge($this->client_data, [
                  'name' => null
                ])
            ],
            'invalid phone (max length of 11 exceeded)' => [
                array_merge($this->client_data, [
                    'phone' => Str::random(12)
                ])
            ],
            'invalid phone (min length less than 11)' => [
                array_merge($this->client_data, [
                    'phone' => Str::random(10)
                ])
            ],
            'missing phone' => [
                array_merge($this->client_data, [
                  'phone' => null
                ])
            ],
            'invalid email' => [
                array_merge($this->client_data, [
                    'email' => 'invalid_email'
                ])
            ],
            'missing email' => [
                array_merge($this->client_data, [
                  'email' => null
                ])
            ],
        ];
    }
}
