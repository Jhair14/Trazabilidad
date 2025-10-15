<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Operador;
use Illuminate\Http\Request;

class OperadorWebController extends Controller
{
    public function index()
    {
        $operadores = Operador::orderBy('IdOperador','desc')->paginate(10);
        return view('operadores.index', compact('operadores'));
    }

    public function create()
    {
        return view('operadores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Nombre' => 'required|string|max:100',
            'Cargo' => 'nullable|string|max:50',
            'Usuario' => 'required|string|max:60|unique:Operador,Usuario',
            'PasswordHash' => 'required|string|max:255',
            'Email' => 'nullable|email|max:100',
        ]);
        Operador::create($data);
        return redirect()->route('operadores.index')->with('status', 'Operador creado');
    }

    public function show($id)
    {
        $operador = Operador::findOrFail($id);
        return view('operadores.show', compact('operador'));
    }

    public function edit($id)
    {
        $operador = Operador::findOrFail($id);
        return view('operadores.edit', compact('operador'));
    }

    public function update(Request $request, $id)
    {
        $operador = Operador::findOrFail($id);
        $data = $request->validate([
            'Nombre' => 'required|string|max:100',
            'Cargo' => 'nullable|string|max:50',
            'Usuario' => 'required|string|max:60|unique:Operador,Usuario,'.$operador->IdOperador.',IdOperador',
            'PasswordHash' => 'required|string|max:255',
            'Email' => 'nullable|email|max:100',
        ]);
        $operador->update($data);
        return redirect()->route('operadores.index')->with('status', 'Operador actualizado');
    }

    public function destroy($id)
    {
        Operador::destroy($id);
        return redirect()->route('operadores.index')->with('status', 'Operador eliminado');
    }
}



