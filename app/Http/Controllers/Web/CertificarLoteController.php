<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\ProcessMachineRecord;
use App\Models\ProcessFinalEvaluation;
use App\Models\ProcessMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CertificarLoteController extends Controller
{
    public function index()
    {
        // Mostrar todos los lotes que pueden ser certificados
        // Incluye lotes sin registros de proceso (para que puedan ir a proceso-transformacion primero)
        // y lotes con registros pero sin certificar
        $lotes = ProductionBatch::with([
            'order.customer', 
            'processMachineRecords.processMachine.process',
            'latestFinalEvaluation'
        ])
            ->orderBy('creation_date', 'desc')
            ->get();

        return view('certificar-lote', compact('lotes'));
    }

    public function finalizar($batchId)
    {
        DB::beginTransaction();
        try {
            $batch = ProductionBatch::findOrFail($batchId);

            // ✅ 1. Obtener el proceso del lote a través de los registros existentes
            $records = ProcessMachineRecord::where('batch_id', $batchId)
                ->with('processMachine.process')
                ->get();

            if ($records->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'El lote no tiene registros de proceso. Debe registrar formularios primero.');
            }

            // Obtener el process_id del primer registro (todos deben ser del mismo proceso)
            $firstRecord = $records->first();
            if (!$firstRecord->processMachine || !$firstRecord->processMachine->process_id) {
                return redirect()->back()
                    ->with('error', 'No se pudo identificar el proceso del lote.');
            }

            $processId = $firstRecord->processMachine->process_id;

            // Verificar que todos los registros sean del mismo proceso
            $processIds = $records->pluck('processMachine.process_id')->unique()->filter();
            if ($processIds->count() > 1) {
                return redirect()->back()
                    ->with('error', 'El lote tiene registros de múltiples procesos. Esto no es válido.');
            }

            // ✅ 2. Obtener cantidad real de máquinas del proceso asignado al lote (como en proyecto antiguo)
            $processMachines = ProcessMachine::where('process_id', $processId)
                ->orderBy('step_order')
                ->get();
            
            $expectedCount = $processMachines->count();
            $actualCount = $records->count();

            if ($actualCount < $expectedCount) {
                return redirect()->back()
                    ->with('error', "Faltan formularios. Solo hay {$actualCount} de {$expectedCount} máquinas.");
            }

            // ✅ 3. Evaluar si alguna máquina falló
            $failed = $records->firstWhere('meets_standard', false);
            $status = $failed ? 'No Certificado' : 'Certificado';
            
            $machineName = 'N/A';
            if ($failed && $failed->processMachine) {
                $machineName = $failed->processMachine->name;
            }
            
            $reason = $failed 
                ? "Falló en la máquina {$machineName}"
                : 'Todas las máquinas cumplen los valores estándar';

            // ✅ 4. Guardar evaluación final
            $existingEvaluation = ProcessFinalEvaluation::where('batch_id', $batchId)->first();
            
            if ($existingEvaluation) {
                // Actualizar evaluación existente
                $existingEvaluation->update([
                    'inspector_id' => Auth::id(),
                    'reason' => $reason,
                    'observations' => request('observations'),
                    'evaluation_date' => now(),
                ]);
            } else {
                // Obtener el siguiente ID de la secuencia
                $nextId = DB::selectOne("SELECT nextval('process_final_evaluation_seq') as id")->id;
                
                // Crear evaluación final
                ProcessFinalEvaluation::create([
                    'evaluation_id' => $nextId,
                    'batch_id' => $batchId,
                    'inspector_id' => Auth::id(),
                    'reason' => $reason,
                    'observations' => request('observations'),
                    'evaluation_date' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('certificados')
                ->with('success', $status . ' - El proceso ha sido finalizado');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al finalizar proceso: ' . $e->getMessage());
        }
    }

    /**
     * Obtener el log completo del proceso (similar al proyecto antiguo)
     */
    public function obtenerLog($batchId)
    {
        try {
            $batch = ProductionBatch::with([
                'processMachineRecords.processMachine.machine',
                'processMachineRecords.processMachine.process',
                'finalEvaluation.inspector'
            ])->findOrFail($batchId);

            if (!$batch->finalEvaluation) {
                return response()->json([
                    'message' => 'El lote aún no ha sido evaluado'
                ], 404);
            }

            // Obtener registros de máquinas ordenados por step_order
            $records = ProcessMachineRecord::where('batch_id', $batchId)
                ->with(['processMachine.machine', 'processMachine.process', 'operator'])
                ->get()
                ->sortBy(function($record) {
                    return $record->processMachine ? $record->processMachine->step_order : 999;
                })
                ->values();

            // Formatear máquinas similar al proyecto antiguo
            $maquinas = $records->map(function($record) {
                return [
                    'NumeroMaquina' => $record->processMachine ? $record->processMachine->step_order : null,
                    'NombreMaquina' => $record->processMachine ? $record->processMachine->name : 'N/A',
                    'VariablesIngresadas' => $record->entered_variables ?? [],
                    'CumpleEstandar' => $record->meets_standard ?? false,
                    'FechaRegistro' => $record->record_date ? $record->record_date->toDateTimeString() : null,
                ];
            });

            // Formatear resultado final
            $resultadoFinal = [
                'EstadoFinal' => str_contains(strtolower($batch->finalEvaluation->reason ?? ''), 'falló') 
                    ? 'No Certificado' 
                    : 'Certificado',
                'Motivo' => $batch->finalEvaluation->reason ?? 'N/A',
                'FechaEvaluacion' => $batch->finalEvaluation->evaluation_date 
                    ? $batch->finalEvaluation->evaluation_date->toDateTimeString() 
                    : null,
                'Inspector' => $batch->finalEvaluation->inspector 
                    ? $batch->finalEvaluation->inspector->name 
                    : 'N/A',
            ];

            return response()->json([
                'Maquinas' => $maquinas,
                'ResultadoFinal' => $resultadoFinal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener log: ' . $e->getMessage()
            ], 500);
        }
    }
}

