@extends('layouts.app')

@section('page_title', 'Detalle de Máquina')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-1"></i>
                    Máquina #{{ $maquina->machine_id }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('maquinas.edit', $maquina->machine_id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                    <a href="{{ route('maquinas.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($maquina->image_url)
                            <img src="{{ $maquina->image_url }}" alt="{{ $maquina->name }}" 
                                 class="img-fluid img-thumbnail" style="max-width: 100%;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 300px; border: 1px solid #ddd; border-radius: 4px;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p>Sin imagen</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <dl class="row">
                            <dt class="col-sm-3">Código</dt>
                            <dd class="col-sm-9">
                                <span class="badge badge-primary">{{ $maquina->code }}</span>
                            </dd>
                            
                            <dt class="col-sm-3">Nombre</dt>
                            <dd class="col-sm-9">{{ $maquina->name }}</dd>
                            
                            <dt class="col-sm-3">Descripción</dt>
                            <dd class="col-sm-9">{{ $maquina->description ?? 'Sin descripción' }}</dd>
                            
                            <dt class="col-sm-3">Imagen</dt>
                            <dd class="col-sm-9">
                                @if($maquina->image_url)
                                    <a href="{{ $maquina->image_url }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-external-link-alt mr-1"></i> Ver imagen completa
                                    </a>
                                @else
                                    <span class="text-muted">No hay imagen</span>
                                @endif
                            </dd>
                            
                            <dt class="col-sm-3">Estado</dt>
                            <dd class="col-sm-9">
                                @if($maquina->active)
                                    <span class="badge badge-success">Operativa</span>
                                @else
                                    <span class="badge badge-danger">Fuera de Servicio</span>
                                @endif
                            </dd>
                            
                            @if($maquina->operators->isNotEmpty())
                            <dt class="col-sm-3">Operadores</dt>
                            <dd class="col-sm-9">
                                <ul class="list-unstyled mb-0">
                                    @foreach($maquina->operators as $operator)
                                        <li>
                                            <i class="fas fa-user mr-1"></i>
                                            {{ $operator->first_name }} {{ $operator->last_name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
