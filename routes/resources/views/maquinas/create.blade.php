@extends('layouts.app')
@section('content')
<h1 class="text-xl font-medium mb-4">Nueva Máquina</h1>
<form method="POST" action="{{ route('maquinas.store') }}" class="space-y-3 max-w-xl">
  @csrf
  <div><label class="block text-sm">Nombre</label><input name="Nombre" value="{{ old('Nombre') }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">ImagenUrl</label><input name="ImagenUrl" value="{{ old('ImagenUrl') }}" class="border p-2 w-full"></div>
  <div class="pt-2">
    <button class="underline">Guardar</button>
    <a href="{{ route('maquinas.index') }}" class="underline ml-3">Cancelar</a>
  </div>
</form>
@endsection



