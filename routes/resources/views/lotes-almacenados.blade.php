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
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>70</h3>
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
                                <h3>55</h3>
                                <p>Disponibles</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>10</h3>
                                <p>Por Vencer</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>5</h3>
                                <p>Vencidos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
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
                            <tr>
                                <td>#L001</td>
                                <td>Producto A</td>
                                <td>A-01</td>
                                <td>Estante 1, Nivel 3</td>
                                <td>100 unidades</td>
                                <td>2024-01-15</td>
                                <td>2024-07-15</td>
                                <td>180 días</td>
                                <td><span class="badge badge-success">Disponible</span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Mover">
                                        <i class="fas fa-arrows-alt"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Retirar">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#L002</td>
                                <td>Producto B</td>
                                <td>A-02</td>
                                <td>Estante 2, Nivel 3</td>
                                <td>75 unidades</td>
                                <td>2024-01-14</td>
                                <td>2024-07-14</td>
                                <td>15 días</td>
                                <td><span class="badge badge-warning">Por Vencer</span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Mover">
                                        <i class="fas fa-arrows-alt"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Retirar">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Priorizar">
                                        <i class="fas fa-exclamation"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#L003</td>
                                <td>Producto C</td>
                                <td>A-03</td>
                                <td>Estante 3, Nivel 3</td>
                                <td>50 unidades</td>
                                <td>2024-01-13</td>
                                <td>2024-07-13</td>
                                <td>-5 días</td>
                                <td><span class="badge badge-danger">Vencido</span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Mover">
                                        <i class="fas fa-arrows-alt"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Descartar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-secondary btn-sm" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando 1 a 10 de 70 registros
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm">
                            <li class="page-item disabled">
                                <span class="page-link">Anterior</span>
                            </li>
                            <li class="page-item active">
                                <span class="page-link">1</span>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">2</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">3</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
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

@push('scripts')
<script>
function aplicarFiltros() {
    // Aquí iría la lógica para aplicar filtros
    alert('Filtros aplicados');
}

function buscarLote() {
    // Aquí iría la lógica para buscar el lote
    alert('Búsqueda realizada');
    $('#buscarLoteModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

