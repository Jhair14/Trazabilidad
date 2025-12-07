<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Storage;
use App\Models\ProductionBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AlmacenajeController extends Controller
{
    public function index()
    {
        // Mostrar TODOS los lotes certificados (incluyendo los ya almacenados)
        $lotes = ProductionBatch::whereHas('latestFinalEvaluation', function($query) {
                $query->whereRaw("LOWER(reason) NOT LIKE '%falló%'");
            })
            ->with(['order.customer', 'latestFinalEvaluation', 'storage'])
            ->orderBy('creation_date', 'desc')
            ->get();

        // Calcular estadísticas sobre TODOS los lotes, no solo los filtrados
        $allLotes = ProductionBatch::with(['latestFinalEvaluation', 'storage'])->get();
        
        // Lotes disponibles para almacenar (certificados sin almacenar)
        $lotesDisponibles = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            $esCertificado = $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló');
            return $esCertificado && $lote->storage->isEmpty();
        });
        
        // Lotes certificados (todos los que tienen evaluación exitosa)
        $lotesCertificados = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            return $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló');
        });
        
        // Lotes sin certificar (sin evaluación o evaluación fallida)
        $lotesSinCertificar = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            return !$eval || str_contains(strtolower($eval->reason ?? ''), 'falló');
        });
        
        // Lotes ya almacenados
        $lotesAlmacenados = $allLotes->filter(function($lote) {
            return $lote->storage->isNotEmpty();
        });

        $stats = [
            'disponibles' => $lotesDisponibles->count(),
            'certificados' => $lotesCertificados->count(),
            'sin_certificar' => $lotesSinCertificar->count(),
            'almacenados' => $lotesAlmacenados->count(),
        ];

        return view('almacenaje', compact('lotes', 'stats'));
    }

    public function obtenerAlmacenajesPorLote($batchId)
    {
        $almacenajes = Storage::where('batch_id', $batchId)
            ->orderBy('storage_date', 'desc')
            ->get();

        return response()->json($almacenajes);
    }

    public function almacenar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|integer|exists:production_batch,batch_id',
            'location' => 'required|string|max:100',
            'condition' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $batch = ProductionBatch::with('storage')->findOrFail($request->batch_id);

            // Verificar que el lote no tenga almacenajes previos
            if ($batch->storage->isNotEmpty()) {
                return redirect()->back()
                    ->with('error', 'Este lote ya ha sido almacenado. Solo se permite almacenar una vez toda la cantidad.')
                    ->withInput();
            }

            // Validar que la cantidad almacenada sea igual a la cantidad producida o objetivo
            $producedQuantity = $batch->produced_quantity ?? 0;
            $targetQuantity = $batch->target_quantity ?? 0;
            
            // Si no hay cantidad producida, usar la cantidad objetivo
            $expectedQuantity = ($producedQuantity > 0) ? $producedQuantity : $targetQuantity;
            $requestedQuantity = $request->quantity;

            if ($expectedQuantity > 0 && abs($requestedQuantity - $expectedQuantity) > 0.01) {
                $tipoCantidad = ($producedQuantity > 0) ? 'producida' : 'objetivo';
                return redirect()->back()
                    ->with('error', "La cantidad almacenada ({$requestedQuantity}) debe ser igual a la cantidad {$tipoCantidad} ({$expectedQuantity}).")
                    ->withInput();
            }

            // Sincronizar la secuencia con el máximo ID existente
            $maxStorageId = DB::table('storage')->max('storage_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            // Si no hay registros, PostgreSQL manejará automáticamente el siguiente valor
            if ($maxStorageId !== null && $maxStorageId > 0) {
                DB::statement("SELECT setval('storage_seq', {$maxStorageId}, true)");
            }

            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('storage_seq') as id")->id;

            Storage::create([
                'storage_id' => $nextId,
                'batch_id' => $request->batch_id,
                'location' => $request->location,
                'condition' => $request->condition,
                'quantity' => $request->quantity,
                'observations' => $request->observations,
                'storage_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('almacenaje')
                ->with('success', 'Lote almacenado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al almacenar lote: ' . $e->getMessage())
                ->withInput();
        }
    }
}

