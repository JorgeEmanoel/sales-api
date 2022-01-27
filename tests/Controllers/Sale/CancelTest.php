<?php

namespace Tests\Controllers\Sale;

use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Sale;
use App\Models\SaleProduct;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\TestCase;

class CancelTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    /** @var App\Models\Client */
    protected $client;

    /** @var App\Models\Sale */
    protected $sale;

    /** @var App\Models\SaleProduct */
    protected $sale_product;

    /** @var App\Models\Provider */
    protected $provider;

    /** @var App\Models\Product */
    protected $product;

    protected string $method = 'DELETE';
    protected string $path = 'sales';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Client::factory()->create();
        $this->provider = Provider::factory()->create();
        $this->product = Product::factory()->create([
            'provider_id' => $this->provider->id
        ]);

        $this->sale = Sale::factory()->create([
            'client_id' => $this->client->id,
            'status' => Sale::STATUS_PAID
        ]);
        $this->sale_product = SaleProduct::factory()->create([
            'quantity' => 1,
            'paid_unit_price' => $this->product->price,
            'sale_id' => $this->sale->id,
            'product_id' => $this->product->id
        ]);

        $this->sale_product->calculateTotal()->save();
        $this->sale->calculateTotal()->save();
        $this->path = "sales/{$this->sale->id}/cancel";
    }

    /**
     * @test
     */
    public function itShouldRestoreProductsQuantitiesAfterCancelling()
    {
        $this->json($this->method, $this->path)
            ->assertResponseStatus(Response::HTTP_OK);

        $this->seeInDatabase('products', [
            'id' => $this->product->id,
            'quantity' => $this->product->quantity + $this->sale_product->quantity
        ]);
    }
}
