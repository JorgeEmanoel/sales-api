<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $providers = Client::query()
            ->when($request->name, function ($query) use ($request) {
                return $query->withName($request->name);
            })
            ->when($request->email, function ($query) use ($request) {
                return $query->withEmail($request->email);
            })
            ->paginate(
                $request->per_page ?? 10
            );

        return response([
            'data' => ClientResource::collection($providers->items()),
            'pages' => $providers->lastPage()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:240',
            'phone' => 'required|min:11|max:11',
            'email' => [
                'required',
                'email',
                Rule::unique('clients')
            ]
        ]);

        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        $client = Client::create($request->all());

        return response(
            new ClientResource($client),
            Response::HTTP_CREATED
        );
    }

    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response([
                'message' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:240',
            'phone' => 'min:11|max:11',
            'email' => [
                'email',
                Rule::unique('clients')->ignore($id)
            ]
        ]);

        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        $client->update($request->all());

        return response(
            new ClientResource($client),
            Response::HTTP_OK
        );
    }

    public function show($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response([
                'message' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response(
            new ClientResource($client),
            Response::HTTP_OK
        );
    }

    public function delete($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response([
                'message' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $client->delete();

        return response(
            new ClientResource($client),
            Response::HTTP_OK
        );
    }
}
