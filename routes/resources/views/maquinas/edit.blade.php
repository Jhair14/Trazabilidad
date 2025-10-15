@extends('layouts.app')
@section('content')
<h1 class="text-xl font-medium mb-4">Editar Máquina</h1>
<form method="POST" action="{{ route('maquinas.update',$maquina->IdMaquina) }}" class="space-y-3 max-w-xl">
  @csrf @method('PUT')
  <div><label class="block text-sm">Nombre</label><input name="Nombre" value="{{ old('Nombre',$maquina->Nombre) }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">ImagenUrl</label><input name="ImagenUrl" value="{{ old('ImagenUrl',$maquina->ImagenUrl) }}" class="border p-2 w-full"></div>
  <div class="pt-2">
    <button class="underline">Actualizar</button>
    <a href="{{ route('maquinas.index') }}" class="underline ml-3">Cancelar</a>
  </div>
</form>
@endsection



