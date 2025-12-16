<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;
use App\Http\Requests\MaterialRequestRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\MaterialRequestResource;

class MaterialRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MaterialRequest::with(['order.customer', 'details.material.unit']);

        // Filtro por estado
        if ($request->has('estado') && $request->estado) {
            $estado = $request->estado;
            if ($estado === 'pendiente') {
                $query->whereHas('details', function($q) {
                    $q->whereRaw('COALESCE(cantidad_aprobada, 0) < cantidad_solicitada');
                });
            } elseif ($estado === 'completada') {
                $query->whereDoesntHave('details', function($q) {
                    $q->whereRaw('COALESCE(cantidad_aprobada, 0) < cantidad_solicitada');
                });
            }
        }

        $materialRequests = $query->orderBy('fecha_solicitud', 'desc')
            ->orderBy('solicitud_id', 'desc')
            ->paginate($request->get('per_page', 15));

        return MaterialRequestResource::collection($materialRequests);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'pedido_id' => 'required|integer|exists:pedido_cliente,pedido_id',
            'fecha_requerida' => ['required', 'date', 'after_or_equal:today'],
            'direccion' => 'required|string|max:500',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|integer|exists:materia_prima_base,material_id',
            'materials.*.cantidad_solicitada' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Sincronizar secuencia y obtener el siguiente ID
            $maxId = \Illuminate\Support\Facades\DB::table('solicitud_material')->max('solicitud_id');
            if ($maxId !== null && $maxId > 0) {
                \Illuminate\Support\Facades\DB::statement("SELECT setval('solicitud_material_seq', {$maxId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = \Illuminate\Support\Facades\DB::selectOne("SELECT nextval('solicitud_material_seq') as id")->id;
            
            // Generar número de solicitud automáticamente
            $requestNumber = 'SOL-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            $materialRequest = MaterialRequest::create([
                'solicitud_id' => $nextId,
                'pedido_id' => $request->pedido_id,
                'numero_solicitud' => $requestNumber,
                'fecha_solicitud' => now()->toDateString(),
                'fecha_requerida' => $request->fecha_requerida,
                'observaciones' => $request->observaciones ?? null,
                'direccion' => $request->direccion,
                'latitud' => $request->latitud ?? null,
                'longitud' => $request->longitud ?? null,
            ]);

            $details = [];
            foreach ($request->materials as $material) {
                // Sincronizar secuencia y obtener el siguiente ID para detail
                $maxDetailId = \Illuminate\Support\Facades\DB::table('detalle_solicitud_material')->max('detalle_id');
                if ($maxDetailId !== null && $maxDetailId > 0) {
                    \Illuminate\Support\Facades\DB::statement("SELECT setval('detalle_solicitud_material_seq', {$maxDetailId}, true)");
                }
                
                $detailId = \Illuminate\Support\Facades\DB::selectOne("SELECT nextval('detalle_solicitud_material_seq') as id")->id;
                
                $detail = \App\Models\MaterialRequestDetail::create([
                    'detalle_id' => $detailId,
                    'solicitud_id' => $materialRequest->solicitud_id,
                    'material_id' => $material['material_id'],
                    'cantidad_solicitada' => $material['cantidad_solicitada'],
                ]);
                
                $details[] = $detail;
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Solicitud creada exitosamente',
                'solicitud' => $materialRequest->load('details')
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'message' => 'Error al crear solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MaterialRequest $materialRequest): JsonResponse
    {
        return response()->json(new MaterialRequestResource($materialRequest));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MaterialRequestRequest $request, MaterialRequest $materialRequest): JsonResponse
    {
        $materialRequest->update($request->validated());

        return response()->json(new MaterialRequestResource($materialRequest));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(MaterialRequest $materialRequest): Response
    {
        $materialRequest->delete();

        return response()->noContent();
    }
}