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
        // Mostrar solo lotes certificados que aún no han sido almacenados
        $lotes = ProductionBatch::whereHas('latestFinalEvaluation', function($query) {
                $query->whereRaw("LOWER(reason) NOT LIKE '%falló%'");
            })
            ->whereDoesntHave('storage') // Solo lotes sin almacenajes previos
            ->with(['order.customer', 'latestFinalEvaluation', 'storage'])
            ->orderBy('creation_date', 'desc')
            ->get();

        return view('almacenaje', compact('lotes'));
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

            // Validar que la cantidad almacenada sea igual a la cantidad producida
            $producedQuantity = $batch->produced_quantity ?? 0;
            $requestedQuantity = $request->quantity;

            if (abs($requestedQuantity - $producedQuantity) > 0.01) {
                return redirect()->back()
                    ->with('error', "La cantidad almacenada ({$requestedQuantity}) debe ser igual a la cantidad producida ({$producedQuantity}).")
                    ->withInput();
            }

            // Sincronizar la secuencia con el máximo ID existente
            DB::statement("SELECT setval('storage_seq', COALESCE((SELECT MAX(storage_id) FROM storage), 0), true)");

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

