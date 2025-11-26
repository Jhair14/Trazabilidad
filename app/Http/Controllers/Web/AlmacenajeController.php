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
        // Mostrar todos los lotes certificados (no fallidos) - permitir múltiples almacenajes como en proyecto antiguo
        $lotes = ProductionBatch::whereHas('latestFinalEvaluation', function($query) {
                $query->whereRaw("LOWER(reason) NOT LIKE '%falló%'");
            })
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
            $batch = ProductionBatch::findOrFail($request->batch_id);

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

            // Actualizar el estado del pedido a "almacenado" si existe (como en proyecto antiguo)
            if ($batch->order_id) {
                $order = $batch->order;
                if ($order) {
                    // En Trazabilidad no hay campo 'status' en CustomerOrder, pero podemos usar 'priority'
                    // O simplemente dejarlo como está ya que la relación storage indica que está almacenado
                    // Si en el futuro se agrega un campo status, se actualizaría aquí
                }
            }

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

