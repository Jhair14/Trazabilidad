<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::with('unit');

            // Filtro por tipo
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filtro por activo
            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            $products = $query->orderBy('name')->paginate($request->get('per_page', 15));

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $product = Product::with('unit')->findOrFail($id);
            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Producto no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:product,code',
            'name' => 'required|string|max:200',
            'type' => 'required|in:organico,marca_univalle,comestibles',
            'weight' => 'nullable|numeric|min:0',
            'unit_id' => 'nullable|integer|exists:unit_of_measure,unit_id',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $product = Product::create($request->all());
            return response()->json([
                'message' => 'Producto creado exitosamente',
                'product' => $product->load('unit')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|string|max:50|unique:product,code,' . $id . ',product_id',
            'name' => 'sometimes|string|max:200',
            'type' => 'sometimes|in:organico,marca_univalle,comestibles',
            'weight' => 'nullable|numeric|min:0',
            'unit_id' => 'nullable|integer|exists:unit_of_measure,unit_id',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $product = Product::findOrFail($id);
            $product->update($request->all());
            return response()->json([
                'message' => 'Producto actualizado exitosamente',
                'product' => $product->load('unit')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->json([
                'message' => 'Producto eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}



