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
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevoPedido">
                <i class="fas fa-plus"></i> Nuevo Pedido
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
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
                        <td>#{{ $pedido->order_number ?? $pedido->order_id }}</td>
                        <td>{{ $pedido->description ?? 'Sin descripción' }}</td>
                        <td>{{ \Carbon\Carbon::parse($pedido->creation_date)->format('d/m/Y') }}</td>
                        <td>
                            @if($pedido->priority == 0)
                                <span class="badge badge-success">Completado</span>
                            @elseif($pedido->priority > 5)
                                <span class="badge badge-danger">Urgente</span>
                            @elseif($pedido->priority > 0)
                                <span class="badge badge-warning">Pendiente</span>
                            @else
                                <span class="badge badge-primary">En Proceso</span>
                            @endif
                        </td>
                        <td>
                            <div class="progress progress-sm">
                                @if($pedido->priority == 0)
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                @elseif($pedido->priority > 5)
                                    <div class="progress-bar bg-danger" style="width: 20%"></div>
                                @elseif($pedido->priority > 0)
                                    <div class="progress-bar bg-warning" style="width: 40%"></div>
                                @else
                                    <div class="progress-bar bg-primary" style="width: 60%"></div>
                                @endif
                            </div>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($pedido->priority > 0)
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
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

<!-- Modal para Nuevo Pedido -->
<div class="modal fade" id="modalNuevoPedido" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo Pedido</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('mis-pedidos.store') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="description">Descripción del Pedido</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3" placeholder="Describe detalladamente tu pedido...">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="delivery_date">Fecha de Entrega Deseada</label>
                                <input type="date" class="form-control" id="delivery_date" 
                                       name="delivery_date" value="{{ old('delivery_date') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">Prioridad</label>
                                <select class="form-control" id="priority" name="priority">
                                    <option value="1" {{ old('priority', 1) == 1 ? 'selected' : '' }}>Normal</option>
                                    <option value="5" {{ old('priority') == 5 ? 'selected' : '' }}>Alta</option>
                                    <option value="10" {{ old('priority') == 10 ? 'selected' : '' }}>Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observations">Observaciones Adicionales</label>
                        <textarea class="form-control" id="observations" name="observations" 
                                  rows="2" placeholder="Cualquier observación especial...">{{ old('observations') }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Actualizar token CSRF cuando se abre el modal para evitar expiración
    $('#modalNuevoPedido').on('show.bs.modal', function() {
        // Obtener el token CSRF actual del meta tag
        var token = $('meta[name="csrf-token"]').attr('content');
        
        // Actualizar el token en el formulario del modal si existe
        var $tokenInput = $('#modalNuevoPedido form input[name="_token"]');
        if (token && $tokenInput.length) {
            $tokenInput.val(token);
        }
    });
});
</script>
@endpush
@endsection


