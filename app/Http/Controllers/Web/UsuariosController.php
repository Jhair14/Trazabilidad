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
            // Sincronizar la secuencia con el máximo ID existente
            $maxId = Operator::max('operator_id') ?? 0;
            if ($maxId > 0) {
                try {
                    DB::statement("SELECT setval('operator_seq', $maxId, true)");
                } catch (\Exception $e) {
                    // Si la secuencia no existe, crearla
                    DB::statement("CREATE SEQUENCE IF NOT EXISTS operator_seq START WITH " . ($maxId + 1));
                }
            }
            
            // Insertar usando SQL directo con nextval para evitar conflictos
            $passwordHash = Hash::make($request->password);
            $email = $request->email ?? null;
            
            $operatorId = DB::selectOne("
                INSERT INTO operator (operator_id, first_name, last_name, username, password_hash, email, role_id, active)
                VALUES (nextval('operator_seq'), ?, ?, ?, ?, ?, ?, ?)
                RETURNING operator_id
            ", [
                $request->first_name,
                $request->last_name,
                $request->username,
                $passwordHash,
                $email,
                $request->role_id,
                true
            ])->operator_id;
            
            // Obtener el operador creado
            $operator = Operator::find($operatorId);
            
            // Asignar el rol de Spatie basado en el role_id
            $spatieRole = null;
            if ($request->role_id == 1) {
                $spatieRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
            } elseif ($request->role_id == 2) {
                $spatieRole = \Spatie\Permission\Models\Role::where('name', 'operador')->first();
            } elseif ($request->role_id == 3) {
                $spatieRole = \Spatie\Permission\Models\Role::where('name', 'cliente')->first();
            }
            
            if ($spatieRole) {
                $operator->assignRole($spatieRole);
            }

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

