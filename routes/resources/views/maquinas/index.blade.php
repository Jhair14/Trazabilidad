@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-medium">Máquinas</h1>
  <a href="{{ route('maquinas.create') }}" class="inline-flex items-center px-3 py-1 border border-[#e3e3e0] hover:border-black rounded-sm text-sm">Nueva</a>
</div>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="card-title m-0">Listado</span>
    <a href="{{ route('maquinas.create') }}" class="btn btn-primary btn-sm">Nueva</a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover table-striped mb-0 text-sm">
      <thead class="thead-light"><tr><th>ID</th><th>Nombre</th><th>ImagenUrl</th><th class="text-right pr-3">Acciones</th></tr></thead>
      <tbody>
  @foreach($maquinas as $m)
    <tr class="border-b"><td>{{ $m->IdMaquina }}</td><td>{{ $m->Nombre }}</td><td class="truncate max-w-[240px]">{{ $m->ImagenUrl }}</td>
      <td class="text-right pr-3">
        <div class="btn-group btn-group-sm" role="group">
          <a class="btn btn-secondary" href="{{ route('maquinas.show',$m->IdMaquina) }}"><i class="far fa-eye mr-1"></i> Ver</a>
          <a class="btn btn-primary" href="{{ route('maquinas.edit',$m->IdMaquina) }}"><i class="far fa-edit mr-1"></i> Editar</a>
          <form method="POST" action="{{ route('maquinas.destroy',$m->IdMaquina) }}" onsubmit="return confirm('¿Eliminar esta máquina?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><i class="far fa-trash-alt mr-1"></i> Eliminar</button>
          </form>
        </div>
      </td></tr>
  @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{ $maquinas->links() }}</div>
</div>
@endsection



