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
                                <h3>{{ $stats['pendientes'] }}</h3>
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
                                <h3>{{ $stats['aprobados'] }}</h3>
                                <p>Aprobados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $stats['en_produccion'] }}</h3>
                                <p>En Producción</p>
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
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                            <option value="en_produccion">En Producción</option>
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
                                <th>Nombre del Pedido</th>
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
                                <td><strong>{{ $pedido->name ?? 'Sin nombre' }}</strong></td>
                                <td>{{ $pedido->customer->business_name ?? 'N/A' }}</td>
                                <td>{{ $pedido->description ?? 'Sin descripción' }}</td>
                                <td>
                                    @if($pedido->status == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($pedido->status == 'aprobado')
                                        <span class="badge badge-success">Aprobado</span>
                                    @elseif($pedido->status == 'rechazado')
                                        <span class="badge badge-danger">Rechazado</span>
                                    @elseif($pedido->status == 'en_produccion')
                                        <span class="badge badge-info">En Producción</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($pedido->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($pedido->creation_date)->format('Y-m-d') }}</td>
                                <td>{{ $pedido->delivery_date ? \Carbon\Carbon::parse($pedido->delivery_date)->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('gestion-pedidos.show', $pedido->order_id) }}" class="btn btn-info btn-sm" title="Ver Detalles">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
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

