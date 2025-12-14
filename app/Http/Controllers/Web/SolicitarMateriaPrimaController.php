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
        $solicitudes = MaterialRequest::with([
                'order.customer', 
                'details.material.unit',
                'details' => function($query) {
                    $query->orderBy('detalle_id', 'asc');
                }
            ])
            ->orderBy('fecha_solicitud', 'desc')
            ->orderBy('solicitud_id', 'desc')
            ->paginate(15);

        // Calcular estadísticas correctas basadas en detalles
        $allSolicitudes = MaterialRequest::with('details')->get();
        
        $totalSolicitudes = $allSolicitudes->count();
        $completadas = 0;
        $pendientes = 0;
        
        foreach ($allSolicitudes as $solicitud) {
            $todosCompletos = true;
            foreach ($solicitud->details as $detail) {
                $cantidadAprobada = $detail->cantidad_aprobada ?? 0;
                $cantidadSolicitada = $detail->cantidad_solicitada ?? 0;
                if ($cantidadAprobada < $cantidadSolicitada) {
                    $todosCompletos = false;
                    break;
                }
            }
            if ($todosCompletos && $solicitud->details->isNotEmpty()) {
                $completadas++;
            } else {
                $pendientes++;
            }
        }

        // Obtener IDs de pedidos que ya tienen solicitudes de materia prima
        $pedidosConSolicitud = MaterialRequest::pluck('pedido_id')->unique()->toArray();

        // Excluir pedidos que ya tienen solicitudes
        // Mostrar pedidos pendientes y aprobados que aún no tienen solicitud
        $pedidos = CustomerOrder::whereIn('estado', ['pendiente', 'aprobado'])
            ->whereNotIn('pedido_id', $pedidosConSolicitud)
            ->with('customer')
            ->orderBy('fecha_creacion', 'desc')
            ->get();
            
        $materias_primas = RawMaterialBase::where('activo', true)
            ->with('unit')
            ->orderBy('nombre', 'asc')
            ->get();
        
        // Preparar datos para JavaScript (array)
        $materias_primas_json = $materias_primas->map(function($mp) {
            return [
                'material_id' => $mp->material_id,
                'nombre' => $mp->nombre,
                'unit' => $mp->unit ? [
                    'codigo' => $mp->unit->codigo,
                    'nombre' => $mp->unit->nombre,
                ] : null
            ];
        });

        $stats = [
            'total' => $totalSolicitudes,
            'completadas' => $completadas,
            'pendientes' => $pendientes,
        ];

        return view('solicitar-materia-prima', compact('solicitudes', 'pedidos', 'materias_primas', 'materias_primas_json', 'stats'));
    }

    /**
     * Obtiene las materias primas necesarias para un pedido específico
     */
    public function getMateriasPrimasPorPedido($pedidoId)
    {
        try {
            $pedido = CustomerOrder::with(['orderProducts.product.unit'])->findOrFail($pedidoId);
            
            // Obtener nombres de productos del pedido
            $nombresProductos = $pedido->orderProducts->pluck('product.nombre')->filter()->unique()->toArray();
            
            // Buscar materias primas que coincidan con los nombres de productos
            $materiasPrimas = RawMaterialBase::where('activo', true)
                ->whereIn('nombre', $nombresProductos)
                ->with(['unit', 'rawMaterials'])
                ->get()
                ->map(function($mp) use ($pedido) {
                    // Encontrar el producto del pedido correspondiente
                    $productoPedido = $pedido->orderProducts->first(function($op) use ($mp) {
                        return $op->product && $op->product->nombre === $mp->nombre;
                    });
                    
                    // Calcular cantidad disponible
                    $cantidadDisponible = $mp->rawMaterials()
                        ->where('conformidad_recepcion', true)
                        ->sum('cantidad_disponible') ?? 0;
                    if ($cantidadDisponible == 0 && $mp->rawMaterials->count() == 0) {
                        $cantidadDisponible = $mp->cantidad_disponible ?? 0;
                    }
                    
                    // Cantidad requerida del pedido
                    $cantidadRequerida = $productoPedido ? $productoPedido->cantidad : 0;
                    
                    // Cantidad mínima a solicitar (requerida - disponible, mínimo 0)
                    $cantidadMinimaSolicitar = max(0, $cantidadRequerida - $cantidadDisponible);
                    
                    return [
                        'material_id' => $mp->material_id,
                        'nombre' => $mp->nombre,
                        'codigo' => $mp->codigo,
                        'cantidad_requerida' => $cantidadRequerida,
                        'cantidad_disponible' => $cantidadDisponible,
                        'cantidad_minima_solicitar' => $cantidadMinimaSolicitar,
                        'unidad' => [
                            'codigo' => $mp->unit->codigo ?? 'KG',
                            'nombre' => $mp->unit->nombre ?? 'Kilogramo'
                        ],
                        'tiene_suficiente' => $cantidadDisponible >= $cantidadRequerida
                    ];
                })
                ->filter(function($mp) {
                    // Solo incluir materias primas que realmente necesiten ser solicitadas
                    // (tienen cantidad mínima a solicitar > 0 o no tienen suficiente)
                    return $mp['cantidad_minima_solicitar'] > 0 || !$mp['tiene_suficiente'];
                })
                ->values();
            
            return response()->json([
                'success' => true,
                'materias_primas' => $materiasPrimas,
                'pedido' => [
                    'numero_pedido' => $pedido->numero_pedido,
                    'nombre' => $pedido->nombre,
                    'fecha_entrega' => $pedido->fecha_entrega ? $pedido->fecha_entrega->format('Y-m-d') : null,
                    'fecha_entrega_formatted' => $pedido->fecha_entrega ? $pedido->fecha_entrega->format('d/m/Y') : null,
                    'fecha_creacion' => $pedido->fecha_creacion ? $pedido->fecha_creacion->format('d/m/Y') : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener materias primas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pedido_id' => 'required|integer|exists:pedido_cliente,pedido_id',
            'fecha_requerida' => ['required', 'date', 'after_or_equal:today'],
            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|integer|exists:materia_prima_base,material_id',
            'materials.*.cantidad_solicitada' => 'required|numeric|min:0',
        ], [
            'fecha_requerida.after_or_equal' => 'La fecha requerida no puede ser anterior a hoy.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Sincronizar secuencia y obtener el siguiente ID
            $maxId = DB::table('solicitud_material')->max('solicitud_id');
            if ($maxId !== null && $maxId > 0) {
                DB::statement("SELECT setval('solicitud_material_seq', {$maxId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('solicitud_material_seq') as id")->id;
            
            // Generar número de solicitud automáticamente
            $requestNumber = 'SOL-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            $materialRequest = MaterialRequest::create([
                'solicitud_id' => $nextId,
                'pedido_id' => $request->pedido_id,
                'numero_solicitud' => $requestNumber,
                'fecha_solicitud' => now()->toDateString(),
                'fecha_requerida' => $request->fecha_requerida,
                'observaciones' => $request->observaciones ?? null,
            ]);

            foreach ($request->materials as $material) {
                // Sincronizar secuencia y obtener el siguiente ID para detail
                $maxDetailId = DB::table('detalle_solicitud_material')->max('detalle_id');
                if ($maxDetailId !== null && $maxDetailId > 0) {
                    DB::statement("SELECT setval('detalle_solicitud_material_seq', {$maxDetailId}, true)");
                }
                
                $detailId = DB::selectOne("SELECT nextval('detalle_solicitud_material_seq') as id")->id;
                
                MaterialRequestDetail::create([
                    'detalle_id' => $detailId,
                    'solicitud_id' => $materialRequest->solicitud_id,
                    'material_id' => $material['material_id'],
                    'cantidad_solicitada' => $material['cantidad_solicitada'],
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

