<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Requests\SupplierRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SupplierResource;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::paginate();

        return SupplierResource::collection($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Manual ID generation if not auto-increment
        // Map English keys to Spanish DB columns
        $mappedData = [
            'razon_social' => $data['business_name'],
            'nombre_comercial' => $data['trading_name'] ?? null,
            'nit' => $data['tax_id'] ?? null,
            'contacto' => $data['contact_person'] ?? null,
            'telefono' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'direccion' => $data['address'] ?? null,
            'activo' => $data['active'] ?? true,
        ];

        // Manual ID generation if not auto-increment
        if (empty($data['proveedor_id'])) {
            $maxId = Supplier::max('proveedor_id') ?? 0;
            $mappedData['proveedor_id'] = $maxId + 1;
        }

        $supplier = Supplier::create($mappedData);

        return response()->json(new SupplierResource($supplier), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json(new SupplierResource($supplier));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return response()->json(new SupplierResource($supplier));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(Supplier $supplier): Response
    {
        $supplier->delete();

        return response()->noContent();
    }
}