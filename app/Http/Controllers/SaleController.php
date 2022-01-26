<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleProductResource;
use App\Http\Resources\SaleResource;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'statuses' => 'string',
            'payment_methods' => 'string'
        ]);

        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        $sales = Sale::query()
            ->when($request->client_id, function ($query) use ($request) {
                return $query->fromClient($request->client_id);
            })
            ->when($request->statuses, function ($query) use ($request) {
                $statuses = explode(',', $request->statuses);
                return $query->withStatuses($statuses);
            })
            ->when($request->payment_methods, function ($query) use ($request) {
                $methods = implode(',', $request->payment_methods);
                return $query->withPaymentMethods($methods);
            })
            ->paginate(
                $request->per_page ?? 20
            );

        return response([
            'data' => SaleResource::collection($sales),
            'pages' => $sales->lastPage()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => [
                'required',
                Rule::in([
                    Sale::PAYMENT_METHOD_CARD,
                    Sale::PAYMENT_METHOD_CASH,
                ])
            ],
            'status' => [
                Rule::in([
                    Sale::STATUS_CANCELLED,
                    Sale::STATUS_PLACED,
                    Sale::STATUS_PENDING,
                    Sale::STATUS_PAID
                ])
            ],
            'client_id' => 'required',
            'products' => 'required',
            'products.*.id' => 'required',
            'products.*.quantity' => 'required',
            'products.*.paid_unit_price' => 'numeric'
        ]);

        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!Client::where('id', $request->client_id)->exists()) {
            return response([
                'message' => 'The specified client was not found'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $product_ids = array_unique(array_map(function ($product) {
            return $product['id'];
        }, $request->products));

        $products = Product::whereIn('id', $product_ids)->get([
            'id',
            'name',
            'price',
            'quantity'
        ]);

        if ($products->count() < count($product_ids)) {
            return response([
                'message' => 'One or more products are not available'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $mapped_products = collect($request->products)
            ->map(function ($mapped_product) use ($products) {
                $product = $products->find($mapped_product['id']);
                $mapped_product['paid_unit_price'] = $mapped_product['paid_unit_price'] ?? $product->price;
                $mapped_product['record'] = $product;

                return $mapped_product;
            })
            ->keyBy('id')
            ->toArray();

        foreach ($products as $product) {
            if ($product->quantity < $mapped_products[$product->id]['quantity']) {
                return response([
                    'message' => "The product '$product->name' only has $product->quantity units"
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $sale = Sale::create([
            'total' => 0,
            'status' => Sale::STATUS_PLACED,
            'payment_method' => $request->payment_method,
            'client_id' => $request->client_id
        ]);

        foreach ($mapped_products as $product) {
            $sale_product = SaleProduct::create([
                'quantity' => $product['quantity'],
                'paid_unit_price' => $product['paid_unit_price'],
                'product_id' => $product['id'],
                'sale_id' => $sale->id,
                'total' => 0,
            ]);

            $sale_product->calculateTotal()->save();
            $product['record']->decreaseQuantity($sale_product->quantity)->save();
        }

        $sale->calculateTotal()
            ->fill([
                'status' => $request->status ?? Sale::STATUS_PLACED
            ])
            ->save();

        return response(
            new SaleResource($sale),
            Response::HTTP_CREATED
        );
    }

    public function products($id)
    {
        $sale = Sale::find($id);

        if (!$sale) {
            return response([
                'message' => 'Sale not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $products = $sale->saleProducts()->with('product')->get();

        return response(
            SaleProductResource::collection($products),
            Response::HTTP_OK
        );
    }

    public function cancel($id)
    {
        $sale = Sale::find($id);

        if (!$sale) {
            return response([
                'message' => 'Sale not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($sale->cancelled()) {
            return response([
                'message' => 'Sale already cancelled'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        foreach ($sale->saleProducts()->with('product')->get() as $sale_product) {
            $sale_product->product->increaseQuantity($sale_product->quantity)->save();
        }

        $sale->cancel();

        return response(
            new SaleResource($sale),
            Response::HTTP_OK
        );
    }
}
