<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Storage;
use Illuminate\Http\Request;

class LotesAlmacenadosController extends Controller
{
    public function index()
    {
        $lotes_almacenados = Storage::with([
                'batch.order.customer', 
                'batch.latestFinalEvaluation',
                'batch.processMachineRecords.processMachine.machine'
            ])
            ->orderBy('storage_date', 'desc')
            ->paginate(15);

        // Estadísticas
        $stats = [
            'total' => Storage::count(),
            'buen_estado' => Storage::whereRaw("LOWER(condition) LIKE '%buen%' OR LOWER(condition) LIKE '%excelente%'")->count(),
            'regular' => Storage::whereRaw("LOWER(condition) LIKE '%regular%' OR LOWER(condition) LIKE '%aceptable%'")->count(),
            'total_cantidad' => Storage::sum('quantity'),
        ];

        return view('lotes-almacenados', compact('lotes_almacenados', 'stats'));
    }

    public function obtenerAlmacenajesPorLote($batchId)
    {
        $almacenajes = Storage::where('batch_id', $batchId)
            ->orderBy('storage_date', 'desc')
            ->get();

        return response()->json($almacenajes);
    }
}

