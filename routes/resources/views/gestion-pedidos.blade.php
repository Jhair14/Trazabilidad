@extends('layouts.app')

@section('page_title', 'Gestión de Pedidos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Gestión de Pedidos
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearPedidoModal">
                        <i class="fas fa-plus"></i> Crear Pedido
                    </button>
                </div>
            </div>
            <div class="card-body">
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

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $pedidos->total() }}</h3>
                                <p>Total Pedidos</p>
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
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="materia_prima_solicitada">Materia Prima Solicitada</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="completado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por cliente..." id="buscarCliente">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFecha">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Pedidos -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Entrega</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedidos as $pedido)
                            <tr>
                                <td>#{{ $pedido->order_number ?? $pedido->order_id }}</td>
                                <td>{{ $pedido->customer->business_name ?? 'N/A' }}</td>
                                <td>{{ $pedido->description ?? 'Sin descripción' }}</td>
                                <td>
                                    @if($pedido->priority == 0)
                                        <span class="badge badge-success">Completado</span>
                                    @elseif($pedido->priority > 5)
                                        <span class="badge badge-danger">Urgente</span>
                                    @elseif($pedido->priority > 0)
                                        <span class="badge badge-warning">Pendiente</span>
                                    @else
                                        <span class="badge badge-info">En Proceso</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($pedido->creation_date)->format('Y-m-d') }}</td>
                                <td>{{ $pedido->delivery_date ? \Carbon\Carbon::parse($pedido->delivery_date)->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar" onclick="editarPedido({{ $pedido->order_id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay pedidos registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($pedidos->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando {{ $pedidos->firstItem() }} a {{ $pedidos->lastItem() }} de {{ $pedidos->total() }} registros
                    </div>
                    <nav>
                        {{ $pedidos->links() }}
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Pedido -->
<div class="modal fade" id="crearPedidoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nuevo Pedido</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="crearPedidoForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreCliente">Nombre del Cliente</label>
                                <input type="text" class="form-control" id="nombreCliente" placeholder="Ej: Cliente ABC">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaEntrega">Fecha de Entrega</label>
                                <input type="date" class="form-control" id="fechaEntrega">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcionPedido">Descripción del Pedido</label>
                        <textarea class="form-control" id="descripcionPedido" rows="3" placeholder="Descripción detallada del pedido..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidad">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad" placeholder="0" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prioridad">Prioridad</label>
                                <select class="form-control" id="prioridad">
                                    <option value="normal">Normal</option>
                                    <option value="alta">Alta</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="crearPedido()">Crear Pedido</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function editarPedido(id) {
    // Implementar edición
    window.location.href = '{{ route("gestion-pedidos") }}/' + id + '/edit';
}

function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const buscar = document.getElementById('buscarCliente').value;
    const fecha = document.getElementById('filtroFecha').value;
    
    const url = new URL(window.location);
    if (estado) url.searchParams.set('estado', estado);
    if (buscar) url.searchParams.set('buscar', buscar);
    if (fecha) url.searchParams.set('fecha', fecha);
    window.location = url;
}
</script>
@endpush

