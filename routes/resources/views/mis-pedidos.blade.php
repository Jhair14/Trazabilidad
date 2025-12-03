@extends('layouts.app')

@section('page_title', 'Mis Pedidos')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<!-- Estadísticas de Mis Pedidos -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $pedidos->total() }}</h3>
                <p>Mis Pedidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $pedidos->where('priority', '>', 0)->count() }}</h3>
                <p>Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $pedidos->where('priority', '>', 0)->where('priority', '<=', 5)->count() }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
        <div class="inner">
                <h3>{{ $pedidos->where('priority', 0)->count() }}</h3>
                <p>Completados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check"></i>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Mis Pedidos -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Mis Pedidos
        </h3>
        <div class="card-tools">
            <a href="{{ route('crear-pedido') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Pedido
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Nombre del Pedido</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Progreso</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                    <tr>
                        <td><strong>{{ $pedido->name ?? 'Sin nombre' }}</strong></td>
                        <td>{{ $pedido->description ?? 'Sin descripción' }}</td>
                        <td>{{ \Carbon\Carbon::parse($pedido->creation_date)->format('d/m/Y') }}</td>
                        <td>
                            @if($pedido->status == 'completado')
                                <span class="badge badge-success">Completado</span>
                            @elseif($pedido->status == 'aprobado')
                                <span class="badge badge-info">Aprobado</span>
                            @elseif($pedido->status == 'rechazado')
                                <span class="badge badge-danger">Rechazado</span>
                            @elseif($pedido->status == 'en_produccion')
                                <span class="badge badge-primary">En Producción</span>
                            @elseif($pedido->status == 'cancelado')
                                <span class="badge badge-secondary">Cancelado</span>
                            @else
                                <span class="badge badge-warning">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            <div class="progress progress-sm">
                                @if($pedido->status == 'completado')
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                @elseif($pedido->status == 'aprobado' || $pedido->status == 'en_produccion')
                                    <div class="progress-bar bg-primary" style="width: 70%"></div>
                                @elseif($pedido->status == 'pendiente')
                                    <div class="progress-bar bg-warning" style="width: 30%"></div>
                                @else
                                    <div class="progress-bar bg-danger" style="width: 0%"></div>
                                @endif
                            </div>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($pedido->status === 'pendiente' && (!$pedido->editable_until || \Carbon\Carbon::parse($pedido->editable_until)->isFuture()))
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('mis-pedidos') }}/{{ $pedido->order_id }}/cancel" method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de cancelar este pedido?');">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-sm btn-danger" title="Cancelar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No tienes pedidos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pedidos->hasPages())
    <div class="card-footer clearfix">
        {{ $pedidos->links() }}
    </div>
    @endif
</div>

@endsection


