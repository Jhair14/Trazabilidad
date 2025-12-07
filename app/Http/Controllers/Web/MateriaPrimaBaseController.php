<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RawMaterialBase;
use App\Models\RawMaterialCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MateriaPrimaBaseController extends Controller
{
    public function index()
    {
        $materias_primas = RawMaterialBase::with(['category', 'unit', 'rawMaterials'])
            ->where('active', true)
            ->orderBy('name', 'asc')
            ->paginate(15);

        // Calcular available_quantity dinámicamente desde las materias primas relacionadas
        $materias_primas->getCollection()->transform(function ($mp) {
            // Usar la relación cargada o hacer una nueva consulta si no está cargada
            if ($mp->relationLoaded('rawMaterials')) {
                $calculated = $mp->rawMaterials
                    ->where('receipt_conformity', true)
                    ->sum('available_quantity') ?? 0;
            } else {
                $calculated = $mp->rawMaterials()
                    ->where('receipt_conformity', true)
                    ->sum('available_quantity') ?? 0;
            }
            
            // Si no hay materias primas recibidas, usar el valor almacenado en raw_material_base
            if ($calculated == 0 && ($mp->rawMaterials->count() == 0 || !$mp->relationLoaded('rawMaterials'))) {
                $mp->calculated_available_quantity = $mp->available_quantity ?? 0;
            } else {
                $mp->calculated_available_quantity = $calculated;
            }
            return $mp;
        });

        $categorias = RawMaterialCategory::where('active', true)->get();
        $unidades = UnitOfMeasure::where('active', true)->get();

        // Estadísticas basadas en calculated_available_quantity
        $allMaterias = RawMaterialBase::with('rawMaterials')
            ->where('active', true)
            ->get()
            ->map(function ($mp) {
                // Usar la relación cargada
                $calculated = $mp->rawMaterials
                    ->where('receipt_conformity', true)
                    ->sum('available_quantity') ?? 0;
                
                // Si no hay materias primas recibidas, usar el valor almacenado en raw_material_base
                if ($calculated == 0 && $mp->rawMaterials->count() == 0) {
                    $mp->calculated_available_quantity = $mp->available_quantity ?? 0;
                } else {
                    $mp->calculated_available_quantity = $calculated;
                }
                return $mp;
            });

        $disponibles = 0;
        $bajo_stock = 0;
        $agotadas = 0;

        foreach ($allMaterias as $mp) {
            $available = $mp->calculated_available_quantity ?? 0;
            $minimum = $mp->minimum_stock ?? 0;
            
            if ($available <= 0) {
                $agotadas++;
            } elseif ($minimum > 0 && $available <= $minimum) {
                $bajo_stock++;
            } else {
                $disponibles++;
            }
        }

        $stats = [
            'total' => $allMaterias->count(),
            'disponibles' => $disponibles,
            'bajo_stock' => $bajo_stock,
            'agotadas' => $agotadas,
        ];

        return view('materia-prima-base', compact('materias_primas', 'categorias', 'unidades', 'stats'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:raw_material_category,category_id',
            'unit_id' => 'required|integer|exists:unit_of_measure,unit_id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Verificar que la categoría existe
            $categoria = RawMaterialCategory::findOrFail($request->category_id);
            
            // Verificar que la unidad existe
            $unidad = UnitOfMeasure::findOrFail($request->unit_id);
            
            // Sincronizar la secuencia y obtener el siguiente ID
            $maxId = DB::table('raw_material_base')->max('material_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            // Si no hay registros, PostgreSQL manejará automáticamente el siguiente valor
            if ($maxId !== null && $maxId > 0) {
                // Sincronizar la secuencia con el máximo ID existente
                // El tercer parámetro 'true' hace que el siguiente nextval devuelva maxId + 1
                DB::statement("SELECT setval('raw_material_base_seq', {$maxId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('raw_material_base_seq') as id")->id;
            
            // Generar código automáticamente
            $code = 'MP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Crear usando SQL directo para evitar conflictos
            $materialId = DB::selectOne("
                INSERT INTO raw_material_base (material_id, category_id, unit_id, code, name, description, available_quantity, minimum_stock, maximum_stock, active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING material_id
            ", [
                $nextId,
                $request->category_id,
                $request->unit_id,
                $code,
                $request->name,
                $request->description,
                0,
                $request->minimum_stock ?? 0,
                $request->maximum_stock,
                true
            ])->material_id;

            DB::commit();

            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Materia prima creada exitosamente',
                    'material_id' => $nextId
                ]);
            }

            return redirect()->route('materia-prima-base')
                ->with('success', 'Materia prima base creada exitosamente. NOTA: Para tener disponibilidad, debe recibir materia prima usando el formulario de "Recepción de Materia Prima".');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Si es una petición AJAX, retornar JSON
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear materia prima base: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al crear materia prima base: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $materia = RawMaterialBase::with(['category', 'unit', 'rawMaterials'])->findOrFail($id);
            
            // Calcular stock actual
            $calculated = $materia->rawMaterials
                ->where('receipt_conformity', true)
                ->sum('available_quantity') ?? 0;
            
            if ($calculated == 0 && $materia->rawMaterials->count() == 0) {
                $calculated = $materia->available_quantity ?? 0;
            }
            
            return response()->json([
                'material_id' => $materia->material_id,
                'code' => $materia->code,
                'name' => $materia->name,
                'category_id' => $materia->category_id,
                'unit_id' => $materia->unit_id,
                'description' => $materia->description,
                'minimum_stock' => $materia->minimum_stock,
                'maximum_stock' => $materia->maximum_stock,
                'current_stock' => $calculated,
                'available_quantity' => number_format($calculated, 2),
                'active' => $materia->active,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Materia prima no encontrada'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'category_id' => 'required|integer|exists:raw_material_category,category_id',
            'unit_id' => 'required|integer|exists:unit_of_measure,unit_id',
            'description' => 'nullable|string|max:255',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $materia = RawMaterialBase::findOrFail($id);
            
            $updateData = $request->only([
                'name', 'category_id', 'unit_id', 'description', 'minimum_stock', 'maximum_stock', 'active'
            ]);
            
            $materia->update($updateData);

            return redirect()->route('materia-prima-base')
                ->with('success', 'Materia prima base actualizada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }
}

