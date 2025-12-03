<?php
namespace App\Http\Controllers;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ProveedorController extends Controller {
    public function index()
    {
        $proveedores = Supplier::where('active', true)->get();
        return response()->json($proveedores);
    }
    public function show($id) { 
        return response()->json(Supplier::findOrFail($id)); 
    }
    public function store(Request $request) { 
        $data = $request->validate([
            'business_name' => 'required|string|max:200',
            'trading_name' => 'nullable|string|max:200',
            'tax_id' => 'nullable|string|max:20|unique:supplier,tax_id',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
        ]);
        
        $nextId = DB::selectOne("SELECT nextval('supplier_seq') as id")->id;
        $data['supplier_id'] = $nextId;
        $data['active'] = true;
        
        return response()->json(Supplier::create($data), 201); 
    }
    public function update(Request $request, $id) { 
        $proveedor = Supplier::findOrFail($id); 
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
        $proveedor->update($data); 
        return response()->json($proveedor); 
    }
    public function destroy($id) { 
        $proveedor = Supplier::findOrFail($id);
        $proveedor->update(['active' => false]);
        return response()->json(['message'=>'Eliminado']); 
    }
}
