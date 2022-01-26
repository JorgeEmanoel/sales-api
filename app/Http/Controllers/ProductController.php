<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $providers = Product::query()
            ->when($request->provider_id, function ($query) use ($request) {
                return $query->fromProvider($request->provider_id);
            })
            ->paginate(
                $request->per_page ?? 10
            );

        return response([
            'data' => ProductResource::collection($providers->items()),
            'pages' => $providers->lastPage()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:240',
            'quantity' => 'required|integer|numeric|min:0',
            'price' => 'required|numeric',
            'provider_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!Provider::where('id', $request->provider_id)->exists()) {
            return response([
                'message' => 'Provider not found'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $provider = Product::create($request->all());

        return response(
            new ProductResource($provider),
            Response::HTTP_CREATED
        );
    }

    public function update(Request $request, $id)
    {
        $provider = Product::find($id);

        if (!$provider) {
            return response([
                'message' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:240',
            'quantity' => 'numeric|min:0',
            'price' => 'numeric'
        ]);

        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($request->provider_id && !Provider::where('id', $request->provider_id)->exists()) {
            return response([
                'message' => 'Provider not found'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $provider->update($request->all());

        return response(
            new ProductResource($provider),
            Response::HTTP_OK
        );
    }

    public function show($id)
    {
        $provider = Product::find($id);

        if (!$provider) {
            return response([
                'message' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response(
            new ProductResource($provider),
            Response::HTTP_OK
        );
    }

    public function delete($id)
    {
        $provider = Product::find($id);

        if (!$provider) {
            return response([
                'message' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $provider->delete();

        return response(
            new ProductResource($provider),
            Response::HTTP_OK
        );
    }
}
