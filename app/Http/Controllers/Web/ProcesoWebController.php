<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Proceso;
use Illuminate\Http\Request;

class ProcesoWebController extends Controller
{
    public function index()
    {
        $procesos = Proceso::orderBy('IdProceso','desc')->paginate(10);
        return view('procesos.index', compact('procesos'));
    }

    public function create()
    {
        return view('procesos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Nombre' => 'required|string|max:100',
        ]);
        Proceso::create($data);
        return redirect()->route('procesos.index')->with('status', 'Proceso creado');
    }

    public function show($id)
    {
        $proceso = Proceso::findOrFail($id);
        return view('procesos.show', compact('proceso'));
    }

    public function edit($id)
    {
        $proceso = Proceso::findOrFail($id);
        return view('procesos.edit', compact('proceso'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'Nombre' => 'required|string|max:100',
        ]);
        $proceso = Proceso::findOrFail($id);
        $proceso->update($data);
        return redirect()->route('procesos.index')->with('status', 'Proceso actualizado');
    }

    public function destroy($id)
    {
        Proceso::destroy($id);
        return redirect()->route('procesos.index')->with('status', 'Proceso eliminado');
    }
}



