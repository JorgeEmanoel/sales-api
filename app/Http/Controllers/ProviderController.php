<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $providers = Provider::query()
            ->when($request->name, function ($query) use ($request) {
                return $query->nameLike($request->name);
            })
            ->when($request->document, function ($query) use ($request) {
                return $query->documentLike($request->document);
            })
            ->when($request->document_type, function ($query) use ($request) {
                return $query->documentType($request->document_type);
            })
            ->paginate(
                $request->per_page ?? 10
            );

        return response([
            'data' => ProviderResource::collection($providers->items()),
            'pages' => $providers->lastPage()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:240',
            'document' => [
                'required',
                'string',
                'unique:providers',
                Rule::when($request->document_type === Provider::DOCUMENT_CPF, [
                    'digits:11'
                ]),
                Rule::when($request->document_type === Provider::DOCUMENT_CNPJ, [
                    'digits:14'
                ]),
            ],
            'document_type' => [
                'required',
                Rule::in([
                    Provider::DOCUMENT_CPF,
                    Provider::DOCUMENT_CNPJ
                ])
            ],
            'shared' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($request->document) == 11 &&
            $request->document_type !== Provider::DOCUMENT_CPF) {
            return response([
                'message' => 'Invalid document'
            ]);
        }

        if (strlen($request->document) == 14 &&
            $request->document_type !== Provider::DOCUMENT_CNPJ) {
            return response([
                'message' => 'Invalid document'
            ]);
        }

        $provider = Provider::create($request->all());

        return response(
            new ProviderResource($provider),
            Response::HTTP_CREATED
        );
    }

    public function update(Request $request, $id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response([
                'message' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:240',
            'document' => [
                'string',
                Rule::unique('providers')->ignore($provider->id),
                Rule::when($request->document_type === Provider::DOCUMENT_CPF, [
                    'digits:11'
                ]),
                Rule::when($request->document_type === Provider::DOCUMENT_CNPJ, [
                    'digits:14'
                ]),
            ],
            'document_type' => [
                Rule::in([
                    Provider::DOCUMENT_CPF,
                    Provider::DOCUMENT_CNPJ
                ])
            ],
            'shared' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()->first()
            ], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($request->document) == 11 &&
            $request->document_type !== Provider::DOCUMENT_CPF) {
            return response([
                'message' => 'Invalid document'
            ]);
        }

        if (strlen($request->document) == 14 &&
            $request->document_type !== Provider::DOCUMENT_CNPJ) {
            return response([
                'message' => 'Invalid document'
            ]);
        }

        $provider->update($request->all());

        return response(
            new ProviderResource($provider),
            Response::HTTP_OK
        );
    }

    public function show($id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response([
                'message' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response(
            new ProviderResource($provider),
            Response::HTTP_OK
        );
    }

    public function delete($id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response([
                'message' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $provider->delete();

        return response(
            new ProviderResource($provider),
            Response::HTTP_OK
        );
    }
}
