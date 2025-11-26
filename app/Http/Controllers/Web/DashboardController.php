<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\CustomerOrder;
use App\Models\RawMaterialBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas para el dashboard
        $totalLotes = ProductionBatch::count();
        $lotesPendientes = ProductionBatch::whereNull('start_time')->count();
        $lotesEnProceso = ProductionBatch::whereNotNull('start_time')
            ->whereNull('end_time')->count();
        $lotesCompletados = ProductionBatch::whereNotNull('end_time')->count();
        $lotesCertificados = ProductionBatch::whereHas('finalEvaluation', function($query) {
            $query->whereRaw("LOWER(reason) NOT LIKE '%falló%'");
        })->count();
        
        $totalPedidos = CustomerOrder::count();
        $pedidosPendientes = CustomerOrder::where('priority', '>', 0)->count();
        
        $stats = [
            'total_lotes' => $totalLotes,
            'lotes_pendientes' => $lotesPendientes,
            'lotes_en_proceso' => $lotesEnProceso,
            'lotes_completados' => $lotesCompletados,
            'lotes_certificados' => $lotesCertificados,
            'total_pedidos' => $totalPedidos,
            'pedidos_pendientes' => $pedidosPendientes,
            'materias_primas' => RawMaterialBase::where('active', true)->count(),
            'stock_bajo' => RawMaterialBase::whereColumn('available_quantity', '<=', 'minimum_stock')
                ->where('active', true)->count(),
        ];

        // Lotes recientes
        $lotes_recientes = ProductionBatch::with(['order.customer', 'latestFinalEvaluation'])
            ->orderBy('creation_date', 'desc')
            ->limit(5)
            ->get();

        // Pedidos recientes
        $pedidos_recientes = CustomerOrder::with('customer')
            ->orderBy('creation_date', 'desc')
            ->limit(5)
            ->get();

        // Estadísticas para gráficas
        $pedidosPorEstado = [
            'pendiente' => CustomerOrder::where('priority', '>', 0)->count(),
            'materia_prima_solicitada' => 0, // Necesitarías un campo de estado
            'en_proceso' => ProductionBatch::whereNotNull('start_time')->whereNull('end_time')->count(),
            'produccion_finalizada' => ProductionBatch::whereNotNull('end_time')->count(),
            'almacenado' => ProductionBatch::whereHas('storage')->count(),
            'cancelado' => 0,
        ];

        $lotesPorEstado = [
            'pendiente' => $lotesPendientes,
            'en_proceso' => $lotesEnProceso,
            'certificado' => $lotesCertificados,
            'no_certificado' => ProductionBatch::whereHas('finalEvaluation', function($query) {
                $query->whereRaw("LOWER(reason) LIKE '%falló%'");
            })->count(),
            'almacenado' => ProductionBatch::whereHas('storage')->count(),
        ];

        return view('dashboard', compact('stats', 'lotes_recientes', 'pedidos_recientes', 'pedidosPorEstado', 'lotesPorEstado'));
    }
}

