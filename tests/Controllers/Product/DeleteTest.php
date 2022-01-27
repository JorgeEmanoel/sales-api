<?php

namespace Tests\Controllers\Product;

use App\Models\Product;
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
    protected $path = 'products';

    /** @var App\Models\Product */
    protected $product;

    /** @var App\Models\Provider */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = Provider::factory()->create();
        $this->product = Product::factory()->create([
            'provider_id' => $this->provider->id
        ]);
        $this->path = "products/{$this->product->id}";
    }

    /**
     * @test
     */
    public function itShouldNotReturnDataWhenReceiveAnInvalidId()
    {
        $this->json($this->method, 'products/2')
            ->seeJsonDoesntContains([
                'id',
                'name',
                'quantity',
                'price',
                'provider_id'
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
                'quantity',
                'price',
                'provider_id'
            ])
            ->assertResponseStatus(Response::HTTP_OK);

        $this->seeInDatabase('products', [
            'id' => $this->product->id
        ]);

        $this->notSeeInDatabase('products', [
            'id' => $this->product->id,
            'deleted_at' => null
        ]);
    }
}
