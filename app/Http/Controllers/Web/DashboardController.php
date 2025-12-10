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
        $lotesPendientes = ProductionBatch::whereNull('hora_inicio')->count();
        $lotesEnProceso = ProductionBatch::whereNotNull('hora_inicio')
            ->whereNull('hora_fin')->count();
        $lotesCompletados = ProductionBatch::whereNotNull('hora_fin')->count();
        $lotesCertificados = ProductionBatch::whereHas('finalEvaluation', function($query) {
            $query->whereRaw("LOWER(razon) NOT LIKE '%falló%'");
        })->count();
        
        $totalPedidos = CustomerOrder::count();
        $pedidosPendientes = CustomerOrder::where('estado', 'pendiente')->count();
        
        $stats = [
            'total_lotes' => $totalLotes,
            'lotes_pendientes' => $lotesPendientes,
            'lotes_en_proceso' => $lotesEnProceso,
            'lotes_completados' => $lotesCompletados,
            'lotes_certificados' => $lotesCertificados,
            'total_pedidos' => $totalPedidos,
            'pedidos_pendientes' => $pedidosPendientes,
            'materias_primas' => RawMaterialBase::where('activo', true)->count(),
            'stock_bajo' => RawMaterialBase::whereColumn('cantidad_disponible', '<=', 'stock_minimo')
                ->where('activo', true)->count(),
        ];

        // Lotes recientes
        $lotes_recientes = ProductionBatch::with(['order.customer', 'latestFinalEvaluation'])
            ->orderBy('fecha_creacion', 'desc')
            ->limit(5)
            ->get();

        // Pedidos recientes
        $pedidos_recientes = CustomerOrder::with('customer')
            ->orderBy('fecha_creacion', 'desc')
            ->limit(5)
            ->get();

        // Estadísticas para gráficas
        $pedidosPorEstado = [
            'pendiente' => CustomerOrder::where('estado', 'pendiente')->count(),
            'materia_prima_solicitada' => 0, // Necesitarías un campo de estado
            'en_proceso' => ProductionBatch::whereNotNull('hora_inicio')->whereNull('hora_fin')->count(),
            'produccion_finalizada' => ProductionBatch::whereNotNull('hora_fin')->count(),
            'almacenado' => ProductionBatch::whereHas('storage')->count(),
            'cancelado' => 0,
        ];

        $lotesPorEstado = [
            'pendiente' => $lotesPendientes,
            'en_proceso' => $lotesEnProceso,
            'certificado' => $lotesCertificados,
            'no_certificado' => ProductionBatch::whereHas('finalEvaluation', function($query) {
                $query->whereRaw("LOWER(razon) LIKE '%falló%'");
            })->count(),
            'almacenado' => ProductionBatch::whereHas('storage')->count(),
        ];

        return view('dashboard', compact('stats', 'lotes_recientes', 'pedidos_recientes', 'pedidosPorEstado', 'lotesPorEstado'));
    }
}

