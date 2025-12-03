<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\OperatorRole;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OperadorWebController extends Controller
{
    public function index()
    {
        $operadores = Operator::with('role')
            ->orderBy('first_name','asc')
            ->paginate(10);
        return view('operadores.index', compact('operadores'));
    }

    public function create()
    {
        $roles = OperatorRole::where('active', true)->get();
        $maquinas = Machine::where('active', true)->get();
        return view('operadores.create', compact('roles', 'maquinas'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|max:60|unique:operator,username',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|max:100',
            'role_id' => 'required|integer|exists:operator_role,role_id',
            'maquina_ids' => 'nullable|array',
            'maquina_ids.*' => 'integer|exists:machine,machine_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('operator_seq') as id")->id;
            
            $operador = Operator::create([
                'operator_id' => $nextId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'password_hash' => Hash::make($request->password),
                'email' => $request->email,
                'role_id' => $request->role_id,
                'active' => true,
            ]);

            // Asignar máquinas
            if ($request->has('maquina_ids')) {
                $operador->machines()->attach($request->maquina_ids);
            }

            return redirect()->route('operadores.web.index')
                ->with('success', 'Operador creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear operador: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $operador = Operator::with(['role', 'machines'])->findOrFail($id);
        return view('operadores.show', compact('operador'));
    }

    public function edit($id)
    {
        $operador = Operator::with('machines')->findOrFail($id);
        $roles = OperatorRole::where('active', true)->get();
        $maquinas = Machine::where('active', true)->get();
        return view('operadores.edit', compact('operador', 'roles', 'maquinas'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|max:60|unique:operator,username,' . $id . ',operator_id',
            'password' => 'nullable|string|min:6',
            'email' => 'nullable|email|max:100',
            'role_id' => 'required|integer|exists:operator_role,role_id',
            'active' => 'nullable|boolean',
            'maquina_ids' => 'nullable|array',
            'maquina_ids.*' => 'integer|exists:machine,machine_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $operador = Operator::findOrFail($id);
            $data = $request->only([
                'first_name', 'last_name', 'username', 'email', 'role_id', 'active'
            ]);

            if ($request->filled('password')) {
                $data['password_hash'] = Hash::make($request->password);
            }

            $operador->update($data);

            // Actualizar máquinas asignadas
            if ($request->has('maquina_ids')) {
                $operador->machines()->sync($request->maquina_ids);
            }

            return redirect()->route('operadores.web.index')
                ->with('success', 'Operador actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar operador: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $operador = Operator::findOrFail($id);
            $operador->update(['active' => false]);
            return redirect()->route('operadores.web.index')
                ->with('success', 'Operador eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar operador: ' . $e->getMessage());
        }
    }
}



