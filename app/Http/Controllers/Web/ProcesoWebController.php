<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\ProcessMachine;
use App\Models\Machine;
use App\Models\StandardVariable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProcesoWebController extends Controller
{
    public function index()
    {
        $procesos = Process::with('processMachines.machine')
            ->orderBy('process_id','desc')
            ->paginate(15);
        $maquinas = Machine::where('active', true)->get();
        $variables = StandardVariable::where('active', true)->get();
        return view('procesos', compact('procesos', 'maquinas', 'variables'));
    }

    public function create()
    {
        $maquinas = Machine::where('active', true)->orderBy('name')->get();
        $variables = StandardVariable::where('active', true)->orderBy('name')->get();
        return view('procesos.create', compact('maquinas', 'variables'));
    }

    public function store(Request $request)
    {
        // Validación básica para creación simple desde modal (sin máquinas o con array vacío)
        if (!$request->has('maquinas') || !is_array($request->maquinas) || count($request->maquinas) === 0) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            try {
                // Obtener el siguiente ID de la secuencia
                $nextId = DB::selectOne("SELECT nextval('process_seq') as id")->id;
                
                // Generar código automáticamente
                $code = 'PROC-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                
                Process::create([
                    'process_id' => $nextId,
                    'code' => $code,
                    'name' => $request->name,
                    'description' => $request->description,
                    'active' => true,
                ]);

                return redirect()->route('procesos.index')
                    ->with('success', 'Proceso creado exitosamente');
            } catch (\Exception $e) {
                return redirect()->back()
                    ->with('error', 'Error al crear proceso: ' . $e->getMessage())
                    ->withInput();
            }
        }

        // Validación completa para creación con máquinas y variables
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'maquinas' => 'required|array|min:1',
            'maquinas.*.machine_id' => 'required|integer|exists:machine,machine_id',
            'maquinas.*.step_order' => 'required|integer|min:1',
            'maquinas.*.name' => 'required|string|max:100',
            'maquinas.*.variables' => 'required|array|min:1',
            'maquinas.*.variables.*.standard_variable_id' => 'required|integer|exists:standard_variable,variable_id',
            'maquinas.*.variables.*.min_value' => 'required|numeric',
            'maquinas.*.variables.*.max_value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('process_seq') as id")->id;
            
            // Generar código automáticamente
            $code = 'PROC-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            
            $proceso = Process::create([
                'process_id' => $nextId,
                'code' => $code,
                'name' => $request->name,
                'description' => $request->description,
                'active' => true,
            ]);

            foreach ($request->maquinas as $maquinaData) {
                // Obtener el siguiente ID de la secuencia para process_machine
                $processMachineId = DB::selectOne("SELECT nextval('process_machine_seq') as id")->id;
                
                $processMachine = ProcessMachine::create([
                    'process_machine_id' => $processMachineId,
                    'process_id' => $proceso->process_id,
                    'machine_id' => $maquinaData['machine_id'],
                    'step_order' => $maquinaData['step_order'],
                    'name' => $maquinaData['name'],
                    'description' => $maquinaData['description'] ?? null,
                    'estimated_time' => $maquinaData['estimated_time'] ?? null,
                ]);

                foreach ($maquinaData['variables'] as $variableData) {
                    // Validar que max_value sea mayor que min_value
                    if (floatval($variableData['max_value']) <= floatval($variableData['min_value'])) {
                        throw new \Exception("El valor máximo debe ser mayor que el valor mínimo para la variable en la máquina '{$maquinaData['name']}'");
                    }
                    
                    // Obtener el siguiente ID de la secuencia para process_machine_variable
                    $variableId = DB::selectOne("SELECT nextval('process_machine_variable_seq') as id")->id;
                    
                    \App\Models\ProcessMachineVariable::create([
                        'variable_id' => $variableId,
                        'process_machine_id' => $processMachine->process_machine_id,
                        'standard_variable_id' => $variableData['standard_variable_id'],
                        'min_value' => $variableData['min_value'],
                        'max_value' => $variableData['max_value'],
                        'target_value' => null,
                        'mandatory' => true, // Todas las variables son obligatorias
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('procesos.index')
                ->with('success', 'Proceso creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear proceso: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $proceso = Process::with(['processMachines.machine', 'processMachines.variables.standardVariable'])
                ->findOrFail($id);
            
            return response()->json([
                'process_id' => $proceso->process_id,
                'code' => $proceso->code,
                'name' => $proceso->name,
                'description' => $proceso->description,
                'active' => $proceso->active,
                'process_machines' => $proceso->processMachines->map(function($pm) {
                    return [
                        'name' => $pm->name,
                        'machine_name' => $pm->machine->name ?? 'N/A',
                        'step_order' => $pm->step_order,
                        'description' => $pm->description,
                        'estimated_time' => $pm->estimated_time,
                        'variables' => $pm->variables->map(function($v) {
                            return [
                                'variable_name' => $v->standardVariable->name ?? 'N/A',
                                'unit' => $v->standardVariable->unit ?? 'N/A',
                                'min_value' => $v->min_value,
                                'max_value' => $v->max_value,
                                'target_value' => $v->target_value,
                                'mandatory' => $v->mandatory,
                            ];
                        }),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Proceso no encontrado'], 404);
        }
    }

    public function edit($id)
    {
        try {
            $proceso = Process::with(['processMachines.machine', 'processMachines.variables.standardVariable'])->findOrFail($id);
            
            return response()->json([
                'process_id' => $proceso->process_id,
                'name' => $proceso->name,
                'description' => $proceso->description,
                'active' => $proceso->active,
                'process_machines' => $proceso->processMachines->map(function($pm) {
                    return [
                        'process_machine_id' => $pm->process_machine_id,
                        'machine_id' => $pm->machine_id,
                        'machine_name' => $pm->machine->name ?? 'N/A',
                        'step_order' => $pm->step_order,
                        'name' => $pm->name,
                        'description' => $pm->description,
                        'estimated_time' => $pm->estimated_time,
                        'variables' => $pm->variables->map(function($v) {
                            return [
                                'variable_id' => $v->variable_id,
                                'standard_variable_id' => $v->standard_variable_id,
                                'variable_name' => $v->standardVariable->name ?? 'N/A',
                                'unit' => $v->standardVariable->unit ?? 'N/A',
                                'min_value' => $v->min_value,
                                'max_value' => $v->max_value,
                                'target_value' => $v->target_value,
                                'mandatory' => $v->mandatory,
                            ];
                        }),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Proceso no encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        // Validación básica si no hay máquinas
        if (!$request->has('maquinas')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string|max:255',
                'active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();
            try {
                $proceso = Process::findOrFail($id);
                $proceso->update($request->only(['name', 'description', 'active']));

                DB::commit();

                return redirect()->route('procesos.index')
                    ->with('success', 'Proceso actualizado exitosamente');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Error al actualizar proceso: ' . $e->getMessage())
                    ->withInput();
            }
        }

        // Validación completa para actualización con máquinas y variables
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
            'maquinas' => 'required|array|min:1',
            'maquinas.*.machine_id' => 'required|integer|exists:machine,machine_id',
            'maquinas.*.step_order' => 'required|integer|min:1',
            'maquinas.*.name' => 'required|string|max:100',
            'maquinas.*.variables' => 'required|array|min:1',
            'maquinas.*.variables.*.standard_variable_id' => 'required|integer|exists:standard_variable,variable_id',
            'maquinas.*.variables.*.min_value' => 'required|numeric',
            'maquinas.*.variables.*.max_value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $proceso = Process::with(['processMachines.variables'])->findOrFail($id);
            
            // Actualizar datos básicos del proceso
            $proceso->update($request->only(['name', 'description', 'active']));

            // Eliminar máquinas existentes y sus variables
            foreach ($proceso->processMachines as $processMachine) {
                foreach ($processMachine->variables as $variable) {
                    $variable->delete();
                }
                $processMachine->delete();
            }

            // Crear nuevas máquinas y variables
            foreach ($request->maquinas as $maquinaData) {
                // Obtener el siguiente ID de la secuencia para process_machine
                $processMachineId = DB::selectOne("SELECT nextval('process_machine_seq') as id")->id;
                
                $processMachine = ProcessMachine::create([
                    'process_machine_id' => $processMachineId,
                    'process_id' => $proceso->process_id,
                    'machine_id' => $maquinaData['machine_id'],
                    'step_order' => $maquinaData['step_order'],
                    'name' => $maquinaData['name'],
                    'description' => $maquinaData['description'] ?? null,
                    'estimated_time' => $maquinaData['estimated_time'] ?? null,
                ]);

                foreach ($maquinaData['variables'] as $variableData) {
                    // Validar que max_value sea mayor que min_value
                    if (floatval($variableData['max_value']) <= floatval($variableData['min_value'])) {
                        throw new \Exception("El valor máximo debe ser mayor que el valor mínimo para la variable en la máquina '{$maquinaData['name']}'");
                    }
                    
                    // Obtener el siguiente ID de la secuencia para process_machine_variable
                    $variableId = DB::selectOne("SELECT nextval('process_machine_variable_seq') as id")->id;
                    
                    \App\Models\ProcessMachineVariable::create([
                        'variable_id' => $variableId,
                        'process_machine_id' => $processMachine->process_machine_id,
                        'standard_variable_id' => $variableData['standard_variable_id'],
                        'min_value' => $variableData['min_value'],
                        'max_value' => $variableData['max_value'],
                        'target_value' => $variableData['target_value'] ?? null,
                        'mandatory' => isset($variableData['mandatory']) ? (bool)$variableData['mandatory'] : true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('procesos.index')
                ->with('success', 'Proceso actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar proceso: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $proceso = Process::with(['processMachines.variables'])->findOrFail($id);
            
            // Eliminar variables de máquinas primero
            foreach ($proceso->processMachines as $processMachine) {
                foreach ($processMachine->variables as $variable) {
                    $variable->delete();
                }
            }
            
            // Eliminar máquinas del proceso
            foreach ($proceso->processMachines as $processMachine) {
                $processMachine->delete();
            }
            
            // Eliminar el proceso
            $proceso->delete();
            
            DB::commit();
            
            return redirect()->route('procesos.index')
                ->with('success', 'Proceso eliminado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al eliminar proceso: ' . $e->getMessage());
        }
    }
}



