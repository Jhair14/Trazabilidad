<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\ProcessFinalEvaluation;
use Illuminate\Http\Request;

class CertificadosController extends Controller
{
    public function index()
    {
        $certificados = ProductionBatch::whereHas('finalEvaluation')
            ->with([
                'order.customer',
                'latestFinalEvaluation.inspector',
                'processMachineRecords.processMachine.machine',
                'rawMaterials.rawMaterial.materialBase',
                'storage'
            ])
            ->orderBy('creation_date', 'desc')
            ->paginate(15);

        return view('certificados', compact('certificados'));
    }

    public function show($id)
    {
        $lote = ProductionBatch::with([
            'order.customer',
            'latestFinalEvaluation.inspector',
            'processMachineRecords.processMachine.machine',
            'processMachineRecords.processMachine.process',
            'processMachineRecords.operator',
            'rawMaterials.rawMaterial.materialBase',
            'storage'
        ])->findOrFail($id);

        if (!$lote->latestFinalEvaluation) {
            return redirect()->route('certificados')
                ->with('error', 'Este lote aún no ha sido certificado');
        }

        return view('certificados.show', compact('lote'));
    }

    public function qr($id)
    {
        $lote = ProductionBatch::with([
            'order.customer',
            'finalEvaluation.inspector',
            'processMachineRecords.processMachine.machine',
            'rawMaterials.rawMaterial.materialBase',
            'storage'
        ])->findOrFail($id);

        return view('codigo-qr', compact('lote'));
    }
}

