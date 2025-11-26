<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\RawMaterialBase;
use App\Models\Supplier;
use App\Models\MaterialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RecepcionMateriaPrimaController extends Controller
{
    public function index()
    {
        // Obtener solicitudes pendientes (con detalles de materiales)
        $solicitudes = MaterialRequest::with(['order.customer', 'details.material.unit'])
            ->where('priority', '>', 0)
            ->orderBy('required_date', 'asc')
            ->paginate(15);

        // Obtener materias primas recibidas
        $materias_primas = RawMaterial::with(['materialBase.unit', 'supplier'])
            ->orderBy('receipt_date', 'desc')
            ->limit(10)
            ->get();

        $materias_base = RawMaterialBase::where('active', true)
            ->with('unit')
            ->orderBy('name', 'asc')
            ->get();
        $proveedores = Supplier::where('active', true)
            ->orderBy('business_name', 'asc')
            ->get();

        // Estadísticas
        $stats = [
            'total_recepciones' => RawMaterial::count(),
            'completadas' => RawMaterial::whereNotNull('receipt_date')->count(),
            'pendientes' => MaterialRequest::where('priority', '>', 0)->count(),
        ];

        // Preparar datos de solicitudes para JavaScript
        $solicitudesJson = $solicitudes->getCollection()->map(function($s) {
            $details = $s->details->map(function($d) {
                return [
                    'material_id' => $d->material_id,
                    'requested_quantity' => (float)$d->requested_quantity,
                    'approved_quantity' => (float)($d->approved_quantity ?? 0),
                ];
            })->values()->toArray();
            
            return [
                'request_id' => $s->request_id,
                'request_number' => $s->request_number,
                'details' => $details,
            ];
        })->values()->toArray();

        return view('recepcion-materia-prima', compact('solicitudes', 'materias_primas', 'materias_base', 'proveedores', 'stats', 'solicitudesJson'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|integer|exists:raw_material_base,material_id',
            'supplier_id' => 'required|integer|exists:supplier,supplier_id',
            'supplier_batch' => 'nullable|string|max:100',
            'invoice_number' => 'required|string|max:100',
            'receipt_date' => 'required|date',
            'expiration_date' => 'nullable|date|after:receipt_date',
            'quantity' => 'required|numeric|min:0',
            'receipt_conformity' => 'nullable|boolean',
            'receipt_signature' => 'nullable|string',
            'observations' => 'nullable|string|max:500',
            'request_id' => 'nullable|integer|exists:material_request,request_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Verificar que la materia prima base existe
            $materialBase = RawMaterialBase::findOrFail($request->material_id);
            
            // Verificar que el proveedor existe
            $supplier = Supplier::findOrFail($request->supplier_id);
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('raw_material_seq') as id")->id;
            
            // Convertir receipt_conformity a boolean correctamente
            // El formulario envía "1" o "0" como string
            $receiptConformity = true; // Por defecto true
            if ($request->has('receipt_conformity')) {
                $receiptConformity = $request->receipt_conformity == '1' || $request->receipt_conformity === 1 || $request->receipt_conformity === true;
            }
            
            // Guardar el balance anterior antes de actualizar
            $previousBalance = $materialBase->available_quantity ?? 0;
            
            // Crear registro en raw_material
            $rawMaterial = RawMaterial::create([
                'raw_material_id' => $nextId,
                'material_id' => $request->material_id,
                'supplier_id' => $request->supplier_id,
                'supplier_batch' => $request->supplier_batch,
                'invoice_number' => $request->invoice_number,
                'receipt_date' => $request->receipt_date,
                'expiration_date' => $request->expiration_date,
                'quantity' => $request->quantity,
                'available_quantity' => $request->quantity,
                'receipt_conformity' => $receiptConformity,
                'receipt_signature' => $request->receipt_signature ?? null,
                'observations' => $request->observations,
            ]);

            // Actualizar cantidad disponible en materia prima base solo si receipt_conformity es true
            if ($receiptConformity) {
                $materialBase->available_quantity = ($materialBase->available_quantity ?? 0) + $request->quantity;
                $materialBase->save();
            }

            // Si se recepciona desde una solicitud, actualizar el detalle y verificar si está completa
            if ($request->has('request_id') && $request->request_id) {
                $materialRequest = MaterialRequest::with('details')->findOrFail($request->request_id);
                
                // Buscar el detalle correspondiente al material recepcionado
                $detail = $materialRequest->details->firstWhere('material_id', $request->material_id);
                
                if ($detail) {
                    // Actualizar approved_quantity sumando la cantidad recepcionada
                    $currentApproved = $detail->approved_quantity ?? 0;
                    $detail->approved_quantity = $currentApproved + $request->quantity;
                    $detail->save();
                }
                
                // Verificar si todos los detalles de la solicitud han sido recepcionados completamente
                $allCompleted = true;
                foreach ($materialRequest->details as $det) {
                    $approvedQty = $det->approved_quantity ?? 0;
                    $requestedQty = $det->requested_quantity ?? 0;
                    if ($approvedQty < $requestedQty) {
                        $allCompleted = false;
                        break;
                    }
                }
                
                // Si todos los materiales han sido recepcionados, marcar la solicitud como completa
                if ($allCompleted) {
                    $materialRequest->priority = 0;
                    $materialRequest->save();
                }
            }

            // Obtener el siguiente ID de la secuencia para log
            $logId = DB::selectOne("SELECT nextval('material_movement_log_seq') as id")->id;
            
            // Registrar en log de movimientos
            \App\Models\MaterialMovementLog::create([
                'log_id' => $logId,
                'material_id' => $request->material_id,
                'movement_type_id' => 1, // Entrada
                'user_id' => auth()->id(),
                'quantity' => $request->quantity,
                'previous_balance' => $previousBalance,
                'new_balance' => $materialBase->available_quantity,
                'description' => 'Recepción de materia prima' . ($receiptConformity ? ' (Conforme)' : ' (No conforme)'),
                'movement_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('recepcion-materia-prima')
                ->with('success', 'Materia prima recibida exitosamente. Registro creado en raw_material con ID: ' . $nextId);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al recibir materia prima: ' . $e->getMessage())
                ->withInput();
        }
    }
}

