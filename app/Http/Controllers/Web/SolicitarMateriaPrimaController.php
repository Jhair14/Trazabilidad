<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestDetail;
use App\Models\CustomerOrder;
use App\Models\RawMaterialBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SolicitarMateriaPrimaController extends Controller
{
    public function index()
    {
        $solicitudes = MaterialRequest::with(['order.customer', 'details.material.unit'])
            ->orderBy('request_date', 'desc')
            ->paginate(15);

        // Obtener IDs de pedidos que ya tienen solicitudes de materia prima
        $pedidosConSolicitud = MaterialRequest::pluck('order_id')->unique()->toArray();

        // Excluir pedidos que ya tienen solicitudes
        $pedidos = CustomerOrder::where('priority', '>', 0)
            ->whereNotIn('order_id', $pedidosConSolicitud)
            ->with('customer')
            ->orderBy('creation_date', 'desc')
            ->get();
            
        $materias_primas = RawMaterialBase::where('active', true)
            ->with('unit')
            ->orderBy('name', 'asc')
            ->get();

        return view('solicitar-materia-prima', compact('solicitudes', 'pedidos', 'materias_primas'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:customer_order,order_id',
            'required_date' => 'required|date',
            'priority' => 'nullable|integer|min:1|max:10',
            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|integer|exists:raw_material_base,material_id',
            'materials.*.requested_quantity' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('material_request_seq') as id")->id;
            
            // Generar número de solicitud automáticamente
            $requestNumber = 'SOL-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            $materialRequest = MaterialRequest::create([
                'request_id' => $nextId,
                'order_id' => $request->order_id,
                'request_number' => $requestNumber,
                'request_date' => now()->toDateString(),
                'required_date' => $request->required_date,
                'priority' => $request->priority ?? 1,
                'observations' => $request->observations,
            ]);

            foreach ($request->materials as $material) {
                // Obtener el siguiente ID de la secuencia para detail
                $detailId = DB::selectOne("SELECT nextval('material_request_detail_seq') as id")->id;
                
                MaterialRequestDetail::create([
                    'detail_id' => $detailId,
                    'request_id' => $materialRequest->request_id,
                    'material_id' => $material['material_id'],
                    'requested_quantity' => $material['requested_quantity'],
                ]);
            }

            DB::commit();

            return redirect()->route('solicitar-materia-prima')
                ->with('success', 'Solicitud de materia prima creada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear solicitud: ' . $e->getMessage())
                ->withInput();
        }
    }
}

