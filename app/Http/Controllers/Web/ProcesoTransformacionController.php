<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\ProcessMachine;
use App\Models\ProcessMachineRecord;
use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcesoTransformacionController extends Controller
{
    public function index($batchId)
    {
        $batch = ProductionBatch::with([
            'order.customer',
            'rawMaterials.rawMaterial.materialBase',
            'processMachineRecords.processMachine.machine',
            'processMachineRecords.processMachine.process',
            'processMachineRecords.processMachine.variables.standardVariable',
            'processMachineRecords.operator'
        ])->findOrFail($batchId);

        // Obtener el proceso del lote a través de los registros existentes o sesión
        $processId = null;
        $processMachines = collect();
        $formulariosCompletados = [];
        
        // Si hay registros, obtener el process_id del primer registro
        if ($batch->processMachineRecords->isNotEmpty()) {
            $firstRecord = $batch->processMachineRecords->first();
            if ($firstRecord->processMachine) {
                $processId = $firstRecord->processMachine->process_id;
            }
        }
        
        // Si no hay registros pero hay un proceso seleccionado en sesión, usarlo
        if (!$processId) {
            $processId = session('selected_process_' . $batchId);
        }

        // Si hay un proceso identificado, obtener todas sus máquinas
        if ($processId) {
            $processMachines = ProcessMachine::with(['machine', 'variables.standardVariable', 'process'])
                ->where('process_id', $processId)
                ->orderBy('step_order')
                ->get();
            
            // Verificar qué máquinas tienen formularios completados
            foreach ($processMachines as $pm) {
                $record = $batch->processMachineRecords->firstWhere('process_machine_id', $pm->process_machine_id);
                $formulariosCompletados[$pm->process_machine_id] = $record ? true : false;
            }
        }

        // Obtener todos los procesos disponibles para asignar
        $procesos = Process::where('active', true)->get();

        // Calcular progreso
        $totalCompletados = count(array_filter($formulariosCompletados));
        $totalMaquinas = $processMachines->count();
        $procesoListo = $totalCompletados === $totalMaquinas && $totalMaquinas > 0;

        return view('proceso-transformacion', compact(
            'batch', 
            'processMachines', 
            'procesos', 
            'processId',
            'formulariosCompletados',
            'totalCompletados',
            'totalMaquinas',
            'procesoListo'
        ));
    }

    public function asignarProceso(Request $request, $batchId)
    {
        $validator = Validator::make($request->all(), [
            'process_id' => 'required|integer|exists:process,process_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $batch = ProductionBatch::findOrFail($batchId);
            
            // Verificar que no haya registros de otro proceso
            $existingRecords = ProcessMachineRecord::where('batch_id', $batchId)
                ->with('processMachine')
                ->get();
            
            if ($existingRecords->isNotEmpty()) {
                $existingProcessIds = $existingRecords->pluck('processMachine.process_id')->unique()->filter();
                if ($existingProcessIds->isNotEmpty() && !$existingProcessIds->contains($request->process_id)) {
                    return redirect()->back()
                        ->with('error', 'Este lote ya tiene registros de otro proceso. No se puede cambiar.');
                }
            }
            
            // Verificar que el proceso tenga máquinas
            $processMachines = ProcessMachine::where('process_id', $request->process_id)->count();
            if ($processMachines === 0) {
                return redirect()->back()
                    ->with('error', 'El proceso seleccionado no tiene máquinas configuradas.');
            }
            
            // El proceso se "asigna" implícitamente cuando se registra el primer formulario
            // Guardamos el process_id en la sesión para que esté disponible en la vista
            session(['selected_process_' . $batchId => $request->process_id]);
            
            return redirect()->route('proceso-transformacion', $batchId)
                ->with('success', 'Proceso seleccionado. Puede comenzar a registrar formularios.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al asignar proceso: ' . $e->getMessage());
        }
    }

    public function registrarFormulario(Request $request, $batchId, $processMachineId)
    {
        $validator = Validator::make($request->all(), [
            'entered_variables' => 'required|array',
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $operator = Auth::user();
            $processMachine = ProcessMachine::with('variables.standardVariable', 'process')
                ->findOrFail($processMachineId);

            // Validar que si hay otros registros, sean del mismo proceso
            $existingRecords = ProcessMachineRecord::where('batch_id', $batchId)
                ->with('processMachine')
                ->get();
            
            if ($existingRecords->isNotEmpty()) {
                $existingProcessIds = $existingRecords->pluck('processMachine.process_id')->unique()->filter();
                if ($existingProcessIds->isNotEmpty() && !$existingProcessIds->contains($processMachine->process_id)) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', 'Esta máquina pertenece a un proceso diferente al ya registrado en este lote.')
                        ->withInput();
                }
            } else {
                // Si es el primer registro, guardar el proceso en sesión
                session(['selected_process_' . $batchId => $processMachine->process_id]);
            }

            // Validar orden secuencial: verificar que las máquinas anteriores estén completadas
            $allProcessMachines = ProcessMachine::where('process_id', $processMachine->process_id)
                ->orderBy('step_order')
                ->get();
            
            $currentStep = $processMachine->step_order;
            $previousMachines = $allProcessMachines->where('step_order', '<', $currentStep);
            
            foreach ($previousMachines as $prevMachine) {
                $prevRecord = $existingRecords->firstWhere('process_machine_id', $prevMachine->process_machine_id);
                if (!$prevRecord) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', "Debe completar la máquina '{$prevMachine->name}' (paso {$prevMachine->step_order}) antes de continuar.")
                        ->withInput();
                }
            }

            // Validar variables
            $enteredVariables = $request->entered_variables;
            $meetsStandard = true;

            foreach ($processMachine->variables as $variable) {
                $varName = $variable->standardVariable->code ?? $variable->standardVariable->name;
                $enteredValue = $enteredVariables[$varName] ?? null;

                if ($variable->mandatory && $enteredValue === null) {
                    $meetsStandard = false;
                    break;
                }

                if ($enteredValue !== null) {
                    if ($enteredValue < $variable->min_value || $enteredValue > $variable->max_value) {
                        $meetsStandard = false;
                        break;
                    }
                }
            }

            // Buscar si ya existe un registro para esta combinación
            $existingRecord = ProcessMachineRecord::where('batch_id', $batchId)
                ->where('process_machine_id', $processMachineId)
                ->first();

            if ($existingRecord) {
                // Actualizar registro existente
                $existingRecord->update([
                    'operator_id' => $operator->operator_id,
                    'entered_variables' => $enteredVariables, // El cast 'array' maneja la conversión
                    'meets_standard' => $meetsStandard,
                    'observations' => $request->observations,
                    'start_time' => now(),
                    'end_time' => now(),
                    'record_date' => now(),
                ]);
            } else {
                // Crear nuevo registro
                $nextId = DB::selectOne("SELECT nextval('process_machine_record_seq') as id")->id;
                
                ProcessMachineRecord::create([
                    'record_id' => $nextId,
                    'batch_id' => $batchId,
                    'process_machine_id' => $processMachineId,
                    'operator_id' => $operator->operator_id,
                    'entered_variables' => $enteredVariables, // El cast 'array' maneja la conversión
                    'meets_standard' => $meetsStandard,
                    'observations' => $request->observations,
                    'start_time' => now(),
                    'end_time' => now(),
                    'record_date' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('proceso-transformacion', $batchId)
                ->with('success', $meetsStandard ? 'Proceso completado correctamente' : 'Proceso completado con advertencias');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al registrar formulario: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar el formulario para completar una máquina específica
     */
    public function mostrarFormulario($batchId, $processMachineId)
    {
        try {
            $batch = ProductionBatch::with(['order.customer'])->findOrFail($batchId);
            $processMachine = ProcessMachine::with([
                'machine', 
                'variables.standardVariable',
                'process'
            ])->findOrFail($processMachineId);

            // Obtener registro existente si existe
            $record = ProcessMachineRecord::where('batch_id', $batchId)
                ->where('process_machine_id', $processMachineId)
                ->first();

            // Validar orden secuencial
            $allProcessMachines = ProcessMachine::where('process_id', $processMachine->process_id)
                ->orderBy('step_order')
                ->get();

            $currentMachineIndex = $allProcessMachines->search(function ($item) use ($processMachineId) {
                return $item->process_machine_id === $processMachineId;
            });

            $canAccess = true;
            $errorMessage = null;

            if ($currentMachineIndex > 0) {
                $previousMachine = $allProcessMachines[$currentMachineIndex - 1];
                $previousRecordExists = ProcessMachineRecord::where('batch_id', $batchId)
                    ->where('process_machine_id', $previousMachine->process_machine_id)
                    ->exists();

                if (!$previousRecordExists) {
                    $canAccess = false;
                    $errorMessage = 'Debe completar el formulario de la máquina anterior (' . $previousMachine->name . ') primero.';
                }
            }

            return view('proceso-transformacion-formulario', compact(
                'batch', 
                'processMachine', 
                'record',
                'canAccess',
                'errorMessage'
            ));
        } catch (\Exception $e) {
            return redirect()->route('proceso-transformacion', $batchId)
                ->with('error', 'Error al cargar formulario: ' . $e->getMessage());
        }
    }

    /**
     * Obtener el formulario de una máquina específica (API)
     */
    public function obtenerFormulario($batchId, $processMachineId)
    {
        try {
            $batch = ProductionBatch::findOrFail($batchId);
            $processMachine = ProcessMachine::with(['machine', 'variables.standardVariable', 'process'])
                ->findOrFail($processMachineId);

            $record = ProcessMachineRecord::where('batch_id', $batchId)
                ->where('process_machine_id', $processMachineId)
                ->first();

            return response()->json([
                'process_machine' => $processMachine,
                'record' => $record,
                'has_record' => $record !== null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener las máquinas de un proceso específico (para cuando se selecciona un proceso)
     */
    public function obtenerMaquinasProceso($processId)
    {
        try {
            $processMachines = ProcessMachine::with(['machine', 'variables.standardVariable', 'process'])
                ->where('process_id', $processId)
                ->orderBy('step_order')
                ->get();

            return response()->json($processMachines);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener máquinas: ' . $e->getMessage()
            ], 500);
        }
    }
}
