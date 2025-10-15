@extends('layouts.app')
@section('content')
<h1 class="text-xl font-medium mb-4">Editar Operador</h1>
<form method="POST" action="{{ route('operadores.update',$operador->IdOperador) }}" class="space-y-3 max-w-xl">
  @csrf @method('PUT')
  <div><label class="block text-sm">Nombre</label><input name="Nombre" value="{{ old('Nombre',$operador->Nombre) }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">Cargo</label><input name="Cargo" value="{{ old('Cargo',$operador->Cargo) }}" class="border p-2 w-full"></div>
  <div><label class="block text-sm">Usuario</label><input name="Usuario" value="{{ old('Usuario',$operador->Usuario) }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">PasswordHash</label><input name="PasswordHash" value="{{ old('PasswordHash',$operador->PasswordHash) }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">Email</label><input type="email" name="Email" value="{{ old('Email',$operador->Email) }}" class="border p-2 w-full"></div>
  <div class="pt-2">
    <button class="underline">Actualizar</button>
    <a href="{{ route('operadores.index') }}" class="underline ml-3">Cancelar</a>
  </div>
</form>
@endsection



