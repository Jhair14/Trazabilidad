<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorWebController extends Controller
{
    public function index()
    {
        $proveedores = Supplier::orderBy('supplier_id','desc')
            ->paginate(15);
        return view('proveedores', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:200',
            'trading_name' => 'nullable|string|max:200',
            'tax_id' => 'nullable|string|max:20|unique:supplier,tax_id',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
        ]);
        
        try {
            // Obtener el siguiente ID de la secuencia
            $nextId = \DB::selectOne("SELECT nextval('supplier_seq') as id")->id;
            
            $data['supplier_id'] = $nextId;
            $data['active'] = true;
            Supplier::create($data);
            return redirect()->route('proveedores.index')->with('success', 'Proveedor creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear proveedor: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $proveedor = Supplier::with('rawMaterials.materialBase')->findOrFail($id);
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit($id)
    {
        $proveedor = Supplier::findOrFail($id);
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:200',
            'trading_name' => 'nullable|string|max:200',
            'tax_id' => 'nullable|string|max:20|unique:supplier,tax_id,' . $id . ',supplier_id',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ]);
        
        $proveedor = Supplier::findOrFail($id);
        $proveedor->update($data);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy($id)
    {
        $proveedor = Supplier::findOrFail($id);
        $proveedor->update(['active' => false]);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado exitosamente');
    }
}



