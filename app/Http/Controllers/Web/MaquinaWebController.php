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
            // Sincronizar la secuencia con el máximo ID existente (si hay registros)
            // Esto asegura que la secuencia siempre esté al día
            DB::statement("SELECT setval('machine_seq', COALESCE((SELECT MAX(machine_id) FROM machine), 0), true)");
            
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
        try {
            $maquina = Machine::findOrFail($id);
            
            // Eliminar la imagen de Cloudinary si existe
            if ($maquina->image_url && strpos($maquina->image_url, 'cloudinary.com') !== false) {
                try {
                    // Extraer el public_id de la URL de Cloudinary
                    preg_match('/\/v\d+\/(.+)$/', $maquina->image_url, $matches);
                    if (isset($matches[1])) {
                        $publicId = pathinfo($matches[1], PATHINFO_FILENAME);
                        $folder = 'maquinas';
                        $fullPublicId = $folder . '/' . $publicId;
                        
                        // Eliminar usando el controlador de carga de imágenes
                        $deleteRequest = new Request(['public_id' => $fullPublicId]);
                        $imageUploadController = new \App\Http\Controllers\Web\ImageUploadController();
                        $imageUploadController->delete($deleteRequest);
                    }
                } catch (\Exception $e) {
                    // Si falla la eliminación de la imagen, continuar con la eliminación del registro
                    \Log::warning('No se pudo eliminar la imagen de la máquina: ' . $e->getMessage());
                }
            }
            
            // Eliminar el registro de la base de datos
            $maquina->delete();
            
            return redirect()->route('maquinas.index')->with('success', 'Máquina eliminada exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('maquinas.index')
                ->with('error', 'Error al eliminar la máquina: ' . $e->getMessage());
        }
    }
}



