<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            return redirect()->route('proveedores.web.index')->with('success', 'Proveedor creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear proveedor: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $proveedor = Supplier::with('rawMaterials.materialBase')->findOrFail($id);
        
        // Si es una petición AJAX, devolver JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'supplier_id' => $proveedor->supplier_id,
                'business_name' => $proveedor->business_name,
                'trading_name' => $proveedor->trading_name,
                'tax_id' => $proveedor->tax_id,
                'contact_person' => $proveedor->contact_person,
                'phone' => $proveedor->phone,
                'email' => $proveedor->email,
                'address' => $proveedor->address,
                'active' => $proveedor->active,
                'raw_materials_count' => $proveedor->rawMaterials->count(),
            ]);
        }
        
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit($id)
    {
        $proveedor = Supplier::findOrFail($id);
        
        // Si es una petición AJAX, devolver JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'supplier_id' => $proveedor->supplier_id,
                'business_name' => $proveedor->business_name,
                'trading_name' => $proveedor->trading_name,
                'tax_id' => $proveedor->tax_id,
                'contact_person' => $proveedor->contact_person,
                'phone' => $proveedor->phone,
                'email' => $proveedor->email,
                'address' => $proveedor->address,
                'active' => $proveedor->active,
            ]);
        }
        
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $proveedor = Supplier::findOrFail($id);
        
        $rules = [
            'business_name' => 'required|string|max:200',
            'trading_name' => 'nullable|string|max:200',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ];
        
        // Validar tax_id con unicidad solo si tiene valor
        $taxId = $request->input('tax_id');
        if (!empty($taxId) && trim($taxId) !== '') {
            $rules['tax_id'] = [
                'nullable',
                'string',
                'max:20',
                Rule::unique('supplier', 'tax_id')->ignore($id, 'supplier_id')
            ];
        } else {
            $rules['tax_id'] = 'nullable|string|max:20';
        }
        
        $data = $request->validate($rules);
        
        // Manejar el campo active (si no viene en el request o es "0", es false)
        $data['active'] = $request->has('active') && ($request->active == '1' || $request->active === true || $request->active === 1);
        
        // Si tax_id está vacío, establecerlo como null
        if (empty($data['tax_id']) || trim($data['tax_id']) === '') {
            $data['tax_id'] = null;
        }
        
        $proveedor->update($data);
        return redirect()->route('proveedores.web.index')->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy($id)
    {
        $proveedor = Supplier::findOrFail($id);
        $proveedor->update(['active' => false]);
        return redirect()->route('proveedores.web.index')->with('success', 'Proveedor eliminado exitosamente');
    }
}



