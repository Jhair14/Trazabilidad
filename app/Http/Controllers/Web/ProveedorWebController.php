<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorWebController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('IdProveedor','desc')->paginate(10);
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Nombre' => 'required|string|max:100',
            'Contacto' => 'nullable|string|max:100',
            'Telefono' => 'nullable|string|max:20',
            'Email' => 'nullable|email|max:100',
            'Direccion' => 'nullable|string|max:255',
        ]);
        $proveedor = Proveedor::create($data);
        return redirect()->route('proveedores.index')->with('status', 'Proveedor creado');
    }

    public function show($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'Nombre' => 'required|string|max:100',
            'Contacto' => 'nullable|string|max:100',
            'Telefono' => 'nullable|string|max:20',
            'Email' => 'nullable|email|max:100',
            'Direccion' => 'nullable|string|max:255',
        ]);
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update($data);
        return redirect()->route('proveedores.index')->with('status', 'Proveedor actualizado');
    }

    public function destroy($id)
    {
        Proveedor::destroy($id);
        return redirect()->route('proveedores.index')->with('status', 'Proveedor eliminado');
    }
}



