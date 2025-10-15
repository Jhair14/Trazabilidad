@extends('layouts.app')
@section('content')
<h1 class="text-xl font-medium mb-4">Nuevo Operador</h1>
<form method="POST" action="{{ route('operadores.store') }}" class="space-y-3 max-w-xl">
  @csrf
  <div><label class="block text-sm">Nombre</label><input name="Nombre" value="{{ old('Nombre') }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">Cargo</label><input name="Cargo" value="{{ old('Cargo') }}" class="border p-2 w-full"></div>
  <div><label class="block text-sm">Usuario</label><input name="Usuario" value="{{ old('Usuario') }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">PasswordHash</label><input name="PasswordHash" value="{{ old('PasswordHash') }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">Email</label><input type="email" name="Email" value="{{ old('Email') }}" class="border p-2 w-full"></div>
  <div class="pt-2">
    <button class="underline">Guardar</button>
    <a href="{{ route('operadores.index') }}" class="underline ml-3">Cancelar</a>
  </div>
</form>
@endsection



