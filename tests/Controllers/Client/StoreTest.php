<?php

namespace Tests\Controllers\Client;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    protected array $client_data = [
        'name' => 'Client Name',
        'phone' => '00000000000',
        'email' => 'thruthy@email.com'
    ];

    /**
     * @test
     * @dataProvider invalidClientDataProvider
     */
    public function itShouldNotAcceptInvalidInputs(array $data)
    {
        $this->json('POST', 'clients', $data)
            ->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        $this->notSeeInDatabase('clients', $data);
    }

    /**
     * @test
     */
    public function itShouldAccepValidInputAndStoreInDatabase()
    {
        $this->json('POST', 'clients', $this->client_data)
            ->assertResponseStatus(Response::HTTP_CREATED);

        $this->seeInDatabase('clients', $this->client_data);
    }

    /**
     * @test
     * @depends itShouldAccepValidInputAndStoreInDatabase
     */
    public function itShouldNotStoreDuplicatedEmails()
    {
        $this->json('POST', 'clients', $this->client_data)
            ->assertResponseStatus(Response::HTTP_CREATED);

        $this->json('POST', 'clients', $this->client_data)
            ->assertResponseStatus(Response::HTTP_BAD_REQUEST);
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
