<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use App\Models\OperatorRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{
    public function index()
    {
        $usuarios = Operator::with('role')
            ->orderBy('first_name', 'asc')
            ->paginate(15);

        $roles = OperatorRole::where('active', true)->get();

        return view('usuarios', compact('usuarios', 'roles'));
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Obtener el máximo ID actual en la tabla
            $maxId = Operator::max('operator_id') ?? 0;
            
            // Obtener el siguiente valor de la secuencia de forma segura
            try {
                $seqResult = DB::selectOne("SELECT last_value FROM operator_seq");
                $seqValue = $seqResult->last_value ?? 0;
            } catch (\Exception $e) {
                // Si hay error al obtener la secuencia, usar el máximo ID
                $seqValue = 0;
            }
            
            // Si la secuencia está por debajo del máximo, sincronizarla
            if ($seqValue < $maxId) {
                DB::statement("SELECT setval('operator_seq', $maxId, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('operator_seq') as id")->id;
            
            Operator::create([
                'operator_id' => $nextId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'password_hash' => Hash::make($request->password),
                'email' => $request->email,
                'role_id' => $request->role_id,
                'active' => true,
            ]);

            return redirect()->route('usuarios')
                ->with('success', 'Usuario creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|max:60|unique:operator,username,' . $id . ',operator_id',
            'email' => 'nullable|email|max:100',
            'role_id' => 'required|integer|exists:operator_role,role_id',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $usuario = Operator::findOrFail($id);
            $data = $request->only([
                'first_name', 'last_name', 'username', 'email', 'role_id', 'active'
            ]);

            if ($request->filled('password')) {
                $data['password_hash'] = Hash::make($request->password);
            }

            $usuario->update($data);

            return redirect()->route('usuarios')
                ->with('success', 'Usuario actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }
}

