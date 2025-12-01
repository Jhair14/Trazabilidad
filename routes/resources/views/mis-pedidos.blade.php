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
                        <th>Cantidad</th>
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
                        <td>
                            @if($pedido->quantity)
                                @php
                                    $quantity = floatval($pedido->quantity);
                                    $formatted = $quantity == intval($quantity) 
                                        ? number_format($quantity, 0) 
                                        : rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');
                                @endphp
                                <span class="badge badge-info">{{ $formatted }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
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
                            <button class="btn btn-sm btn-info" title="Ver Detalles" onclick="verPedido({{ $pedido->order_id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No tienes pedidos registrados</td>
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
                        <label for="quantity">
                            <i class="fas fa-cubes mr-1"></i>
                            Cantidad <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" 
                               value="{{ old('quantity') }}" 
                               placeholder="Ej: 100" 
                               step="0.0001" 
                               min="0.0001" 
                               required>
                        @error('quantity')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Cantidad de producto requerida para este pedido</small>
                    </div>
                    
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
                                       name="delivery_date" 
                                       value="{{ old('delivery_date') }}"
                                       min="{{ date('Y-m-d') }}"
                                       title="No se pueden seleccionar fechas anteriores a hoy">
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

<!-- Modal Ver Pedido -->
<div class="modal fade" id="verPedidoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles del Pedido
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verPedidoContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
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

function formatQuantity(quantity) {
    const num = parseFloat(quantity);
    if (isNaN(num)) return 'N/A';
    // Si es un número entero, mostrar sin decimales
    if (num % 1 === 0) {
        return num.toString();
    }
    // Si tiene decimales, eliminar ceros innecesarios
    return num.toString().replace(/\.?0+$/, '');
}

function verPedido(id) {
    fetch(`{{ url('mis-pedidos') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            // Determinar estado del pedido
            let estado = '';
            let estadoClass = '';
            if (data.priority == 0) {
                estado = 'Completado';
                estadoClass = 'badge-success';
            } else if (data.priority > 5) {
                estado = 'Urgente';
                estadoClass = 'badge-danger';
            } else if (data.priority > 0) {
                estado = 'Pendiente';
                estadoClass = 'badge-warning';
            } else {
                estado = 'En Proceso';
                estadoClass = 'badge-primary';
            }
            
            // Determinar prioridad en texto
            let prioridadTexto = '';
            if (data.priority == 1) {
                prioridadTexto = 'Normal';
            } else if (data.priority == 5) {
                prioridadTexto = 'Alta';
            } else if (data.priority == 10) {
                prioridadTexto = 'Urgente';
            } else {
                prioridadTexto = data.priority.toString();
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID Pedido</th>
                                <td>#${data.order_number || data.order_id}</td>
                            </tr>
                            <tr>
                                <th>Cantidad</th>
                                <td><span class="badge badge-info">${data.quantity ? formatQuantity(data.quantity) : 'N/A'}</span></td>
                            </tr>
                            <tr>
                                <th>Fecha de Creación</th>
                                <td>${data.creation_date ? new Date(data.creation_date).toLocaleDateString('es-ES') : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Entrega</th>
                                <td>${data.delivery_date ? new Date(data.delivery_date).toLocaleDateString('es-ES') : 'No especificada'}</td>
                            </tr>
                            <tr>
                                <th>Prioridad</th>
                                <td><span class="badge badge-secondary">${prioridadTexto}</span></td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td><span class="badge ${estadoClass}">${estado}</span></td>
                            </tr>
                            <tr>
                                <th>Descripción</th>
                                <td>${data.description || 'Sin descripción'}</td>
                            </tr>
                            ${data.observations ? `
                            <tr>
                                <th>Observaciones</th>
                                <td>${data.observations}</td>
                            </tr>
                            ` : ''}
                            ${data.customer ? `
                            <tr>
                                <th>Cliente</th>
                                <td>${data.customer.business_name || data.customer.trading_name || 'N/A'}</td>
                            </tr>
                            ` : ''}
                            <tr>
                                <th>Lotes Asociados</th>
                                <td><span class="badge badge-info">${data.batches_count || 0} lote(s)</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('verPedidoContent').innerHTML = content;
            $('#verPedidoModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del pedido');
        });
}
</script>
@endpush
@endsection


