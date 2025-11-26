@extends('layouts.app')

@section('page_title', 'Lotes Almacenados')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes mr-1"></i>
                    Lotes Almacenados
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#buscarLoteModal">
                        <i class="fas fa-search"></i> Buscar Lote
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

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $stats['total'] }}</h3>
                                <p>Total Lotes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $stats['buen_estado'] }}</h3>
                                <p>Buen Estado</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $stats['regular'] }}</h3>
                                <p>Estado Regular</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ number_format($stats['total_cantidad'], 2) }}</h3>
                                <p>Total Cantidad</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cubes"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mapa de Almacén -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Mapa del Almacén</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-center">
                                        <thead>
                                            <tr>
                                                <th>Zona</th>
                                                <th>Estante 1</th>
                                                <th>Estante 2</th>
                                                <th>Estante 3</th>
                                                <th>Estante 4</th>
                                                <th>Estante 5</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Nivel 3</strong></td>
                                                <td><span class="badge badge-success">L001</span></td>
                                                <td><span class="badge badge-warning">L002</span></td>
                                                <td><span class="badge badge-success">L003</span></td>
                                                <td><span class="badge badge-danger">L004</span></td>
                                                <td><span class="badge badge-success">L005</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nivel 2</strong></td>
                                                <td><span class="badge badge-success">L006</span></td>
                                                <td><span class="badge badge-success">L007</span></td>
                                                <td><span class="badge badge-warning">L008</span></td>
                                                <td><span class="badge badge-success">L009</span></td>
                                                <td><span class="badge badge-success">L010</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Nivel 1</strong></td>
                                                <td><span class="badge badge-success">L011</span></td>
                                                <td><span class="badge badge-warning">L012</span></td>
                                                <td><span class="badge badge-success">L013</span></td>
                                                <td><span class="badge badge-danger">L014</span></td>
                                                <td><span class="badge badge-success">L015</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <span class="badge badge-success mr-2">Disponible</span>
                                        <span class="badge badge-warning mr-2">Por Vencer</span>
                                        <span class="badge badge-danger mr-2">Vencido</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="disponible">Disponible</option>
                            <option value="por_vencer">Por Vencer</option>
                            <option value="vencido">Vencido</option>
                            <option value="retirado">Retirado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroZona">
                            <option value="">Todas las zonas</option>
                            <option value="A">Zona A</option>
                            <option value="B">Zona B</option>
                            <option value="C">Zona C</option>
                            <option value="D">Zona D</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por lote..." id="buscarLote">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Lotes Almacenados -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID Lote</th>
                                <th>Producto</th>
                                <th>Zona</th>
                                <th>Posición</th>
                                <th>Cantidad</th>
                                <th>Fecha Ingreso</th>
                                <th>Fecha Vencimiento</th>
                                <th>Días Restantes</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotes_almacenados as $almacen)
                            <tr>
                                <td>#{{ $almacen->batch->batch_code ?? $almacen->batch->batch_id }}</td>
                                <td>{{ $almacen->batch->name ?? 'Sin nombre' }}</td>
                                <td>{{ $almacen->location ?? 'N/A' }}</td>
                                <td>{{ $almacen->location ?? 'N/A' }}</td>
                                <td>{{ number_format($almacen->quantity, 2) }} unidades</td>
                                <td>{{ \Carbon\Carbon::parse($almacen->storage_date)->format('Y-m-d') }}</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>
                                    <span class="badge badge-info">{{ $almacen->condition ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver Almacenajes" onclick="verAlmacenajes({{ $almacen->batch->batch_id }}, '{{ $almacen->batch->batch_code ?? $almacen->batch->batch_id }}')">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                    <a href="{{ route('gestion-lotes.show', $almacen->batch->batch_id) }}" class="btn btn-secondary btn-sm" title="Ver Lote">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No hay lotes almacenados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($lotes_almacenados->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando {{ $lotes_almacenados->firstItem() }} a {{ $lotes_almacenados->lastItem() }} de {{ $lotes_almacenados->total() }} registros
                    </div>
                    <nav>
                        {{ $lotes_almacenados->links() }}
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Buscar Lote -->
<div class="modal fade" id="buscarLoteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Buscar Lote</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="buscarLoteForm">
                    <div class="form-group">
                        <label for="codigoLote">Código del Lote</label>
                        <input type="text" class="form-control" id="codigoLote" placeholder="Ej: L001">
                    </div>
                    <div class="form-group">
                        <label for="productoLote">Producto</label>
                        <input type="text" class="form-control" id="productoLote" placeholder="Nombre del producto">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="buscarLote()">Buscar</button>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Modal Ver Almacenajes -->
<div class="modal fade" id="verAlmacenajesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Historial de Almacenajes - Lote #<span id="modal_ver_batch_code"></span></h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="almacenajes_historial">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando historial...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const zona = document.getElementById('filtroZona').value;
    const buscar = document.getElementById('buscarLote').value;
    
    const url = new URL(window.location);
    if (estado) url.searchParams.set('estado', estado);
    if (zona) url.searchParams.set('zona', zona);
    if (buscar) url.searchParams.set('buscar', buscar);
    window.location = url;
}

function buscarLote() {
    const codigo = document.getElementById('codigoLote').value;
    const producto = document.getElementById('productoLote').value;
    
    const url = new URL(window.location);
    if (codigo) url.searchParams.set('codigo', codigo);
    if (producto) url.searchParams.set('producto', producto);
    window.location = url;
    $('#buscarLoteModal').modal('hide');
}

function verAlmacenajes(batchId, batchCode) {
    $('#modal_ver_batch_code').text(batchCode);
    $('#almacenajes_historial').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Cargando historial...</p></div>');
    
    fetch(`{{ url('lotes-almacenados/lote') }}/${batchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                $('#almacenajes_historial').html('<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>No hay registros de almacenaje para este lote.</div>');
            } else {
                let html = '<div class="table-responsive"><table class="table table-bordered table-striped">';
                html += '<thead><tr><th>Fecha</th><th>Ubicación</th><th>Condición</th><th>Cantidad</th><th>Observaciones</th></tr></thead>';
                html += '<tbody>';
                data.forEach(function(almacen) {
                    const fecha = new Date(almacen.storage_date).toLocaleDateString('es-ES', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    html += `<tr>
                        <td>${fecha}</td>
                        <td>${almacen.location || 'N/A'}</td>
                        <td><span class="badge badge-info">${almacen.condition || 'N/A'}</span></td>
                        <td>${almacen.quantity || 0}</td>
                        <td>${almacen.observations || '-'}</td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                $('#almacenajes_historial').html(html);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#almacenajes_historial').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Error al cargar el historial de almacenajes.</div>');
        });
    
    $('#verAlmacenajesModal').modal('show');
}
</script>
@endpush

