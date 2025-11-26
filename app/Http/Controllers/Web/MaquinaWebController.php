<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaquinaWebController extends Controller
{
    public function index()
    {
        $maquinas = Machine::orderBy('machine_id','desc')
            ->paginate(15);
        return view('maquinas', compact('maquinas'));
    }

    public function create()
    {
        return view('maquinas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:500', // Cambiado de url a string para aceptar URLs de Cloudinary
        ]);
        
        try {
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('machine_seq') as id")->id;
            
            // Generar código automáticamente
            $code = 'MAQ-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            
            Machine::create([
                'machine_id' => $nextId,
                'code' => $code,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'active' => true,
            ]);
            
            return redirect()->route('maquinas.index')->with('success', 'Máquina creada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear máquina: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $maquina = Machine::with('operators')->findOrFail($id);
        return view('maquinas.show', compact('maquina'));
    }

    public function edit($id)
    {
        $maquina = Machine::findOrFail($id);
        return view('maquinas.edit', compact('maquina'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:500', // Cambiado de url a string para aceptar URLs de Cloudinary
            'current_image_url' => 'nullable|string|max:500', // Para mantener la imagen actual si no se sube una nueva
            'active' => 'nullable|boolean',
        ]);
        
        $maquina = Machine::findOrFail($id);
        
        // Si no se proporciona una nueva imagen, mantener la actual
        if (empty($data['image_url']) && !empty($data['current_image_url'])) {
            $data['image_url'] = $data['current_image_url'];
        }
        
        unset($data['current_image_url']); // Eliminar del array antes de actualizar
        
        $maquina->update($data);
        return redirect()->route('maquinas.index')->with('success', 'Máquina actualizada exitosamente');
    }

    public function destroy($id)
    {
        $maquina = Machine::findOrFail($id);
        $maquina->update(['active' => false]);
        return redirect()->route('maquinas.index')->with('success', 'Máquina eliminada exitosamente');
    }
}



