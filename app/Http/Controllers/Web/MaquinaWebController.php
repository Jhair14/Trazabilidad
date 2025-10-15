<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Maquina;
use Illuminate\Http\Request;

class MaquinaWebController extends Controller
{
    public function index()
    {
        $maquinas = Maquina::orderBy('IdMaquina','desc')->paginate(10);
        return view('maquinas.index', compact('maquinas'));
    }

    public function create()
    {
        return view('maquinas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Nombre' => 'required|string|max:100',
            'ImagenUrl' => 'nullable|url|max:255',
        ]);
        Maquina::create($data);
        return redirect()->route('maquinas.index')->with('status', 'Máquina creada');
    }

    public function show($id)
    {
        $maquina = Maquina::findOrFail($id);
        return view('maquinas.show', compact('maquina'));
    }

    public function edit($id)
    {
        $maquina = Maquina::findOrFail($id);
        return view('maquinas.edit', compact('maquina'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'Nombre' => 'required|string|max:100',
            'ImagenUrl' => 'nullable|url|max:255',
        ]);
        $maquina = Maquina::findOrFail($id);
        $maquina->update($data);
        return redirect()->route('maquinas.index')->with('status', 'Máquina actualizada');
    }

    public function destroy($id)
    {
        Maquina::destroy($id);
        return redirect()->route('maquinas.index')->with('status', 'Máquina eliminada');
    }
}



