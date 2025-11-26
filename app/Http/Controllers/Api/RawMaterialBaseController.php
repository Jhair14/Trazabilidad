<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterialBase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RawMaterialBaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $materials = RawMaterialBase::with(['category', 'unit'])
                ->where('active', true)
                ->paginate($request->get('per_page', 15));

            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener materias primas base',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $material = RawMaterialBase::with(['category', 'unit'])->findOrFail($id);
            return response()->json($material);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Materia prima base no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:raw_material_category,category_id',
            'unit_id' => 'required|integer|exists:unit_of_measure,unit_id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Nombre y unidad son requeridos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('raw_material_base_seq') as id")->id;
            
            // Generar código automáticamente
            $code = 'MP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            
            $material = RawMaterialBase::create([
                'material_id' => $nextId,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'code' => $code,
                'name' => $request->name,
                'description' => $request->description,
                'available_quantity' => 0,
                'minimum_stock' => $request->minimum_stock ?? 0,
                'maximum_stock' => $request->maximum_stock,
                'active' => true,
            ]);

            return response()->json([
                'id' => $material->material_id,
                'message' => 'Materia prima base creada'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear materia prima base',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $material = RawMaterialBase::findOrFail($id);
            $material->update($request->only([
                'name', 'description', 'minimum_stock', 'maximum_stock', 'active'
            ]));

            return response()->json([
                'message' => 'Materia prima base actualizada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar materia prima base',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $material = RawMaterialBase::findOrFail($id);
            $material->update(['active' => false]);

            return response()->json([
                'message' => 'Materia prima base eliminada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar materia prima base',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

