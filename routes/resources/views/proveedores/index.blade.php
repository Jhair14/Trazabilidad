@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-medium">Proveedores</h1>
  <a href="{{ route('proveedores.create') }}" class="inline-flex items-center px-3 py-1 border border-[#e3e3e0] hover:border-black rounded-sm text-sm">Nuevo</a>
</div>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="card-title m-0">Listado</span>
    <a href="{{ route('proveedores.create') }}" class="btn btn-primary btn-sm">Nuevo</a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover table-striped mb-0 text-sm">
      <thead class="thead-light"><tr><th>ID</th><th>Nombre</th><th>Contacto</th><th class="text-right pr-3">Acciones</th></tr></thead>
      <tbody>
  @foreach($proveedores as $p)
    <tr class="border-b"><td>{{ $p->IdProveedor }}</td><td>{{ $p->Nombre }}</td><td>{{ $p->Contacto }}</td>
      <td class="text-right pr-3">
        <div class="btn-group btn-group-sm" role="group">
          <a class="btn btn-secondary" href="{{ route('proveedores.show',$p->IdProveedor) }}"><i class="far fa-eye mr-1"></i> Ver</a>
          <a class="btn btn-primary" href="{{ route('proveedores.edit',$p->IdProveedor) }}"><i class="far fa-edit mr-1"></i> Editar</a>
          <form method="POST" action="{{ route('proveedores.destroy',$p->IdProveedor) }}" onsubmit="return confirm('¿Eliminar este proveedor?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><i class="far fa-trash-alt mr-1"></i> Eliminar</button>
          </form>
        </div>
      </td></tr>
  @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{ $proveedores->links() }}</div>
</div>
@endsection



