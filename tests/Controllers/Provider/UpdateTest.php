<?php

namespace Tests\Controllers\Provider;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Provider;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;

    /** @var App\Models\Provider */
    protected $provider;

    protected string $method = 'PUT';
    protected string $path = 'providers';

    protected array $provider_data = [
        'name' => 'Provider Name',
        'document_type' => Provider::DOCUMENT_CPF,
        'document' => '11111111111',
        'shared' => false
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = Provider::factory()->create();
        $this->path = "providers/{$this->provider->id}";
    }

    /**
     * @test
     * @dataProvider invalidProviderDataProvider
     */
    public function itShouldNotAcceptInvalidInputs(array $data)
    {
        $this->json($this->method, $this->path, $data)
            ->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        $this->notSeeInDatabase('providers', $data);
    }

    /**
     * @test
     */
    public function itShouldAcceptValidInputAndStoreInDatabase()
    {
        $this->json($this->method, $this->path, $this->provider_data)
            ->assertResponseStatus(Response::HTTP_OK);

        $this->seeInDatabase('providers', $this->provider_data);
    }

    /**
     * @test
     */
    public function itShouldNotCareAboutDuplicatedDocument()
    {
        $this->json($this->method, $this->path, $this->provider_data)
            ->assertResponseStatus(Response::HTTP_OK);

        $this->json($this->method, $this->path, $this->provider_data)
            ->assertResponseStatus(Response::HTTP_OK);

        $this->seeInDatabase('providers', $this->provider_data);
    }

    public function invalidProviderDataProvider()
    {
        return [
            'invalid name (max length exceeded)' => [array_merge(
                $this->provider_data,
                [
                    'name' => Str::random(241)
                ]
            )],
            'missing name' => [array_merge(
                $this->provider_data,
                [
                    'name' => null
                ]
            )],
            'invalid document (numeric)' => [array_merge(
                $this->provider_data,
                [
                    'document' => 90
                ]
            )],
            'invalid document (min)' => [array_merge(
                $this->provider_data,
                [
                    'document' => '1111111111',
                    'document_type' => Provider::DOCUMENT_CPF
                ]
            )],
            'invalid document (max)' => [array_merge(
                $this->provider_data,
                [
                    'document' => '1111111111111111',
                    'document_type' => Provider::DOCUMENT_CNPJ
                ]
            )],
            'invalid document (type do not match length) 1' => [array_merge(
                $this->provider_data,
                [
                    'document' => '1111111111',
                    'document_type' => Provider::DOCUMENT_CNPJ
                ]
            )],
            'invalid document (type do not match length) 2' => [array_merge(
                $this->provider_data,
                [
                    'document' => '111111111111',
                    'document_type' => Provider::DOCUMENT_CPF
                ]
            )],
        ];
    }
}
