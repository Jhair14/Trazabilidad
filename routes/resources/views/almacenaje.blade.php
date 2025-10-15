@extends('layouts.app')

@section('page_title', 'Gestión de Almacenaje')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-warehouse mr-1"></i>
                    Gestión de Almacenaje
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#registrarAlmacenajeModal">
                        <i class="fas fa-plus"></i> Registrar Almacenaje
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>85</h3>
                                <p>Total Almacenados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-warehouse"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>70</h3>
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

                <!-- Tabla de Almacenaje -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Lote</th>
                                <th>Producto</th>
                                <th>Zona</th>
                                <th>Posición</th>
                                <th>Cantidad</th>
                                <th>Fecha Ingreso</th>
                                <th>Fecha Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#AL001</td>
                                <td>#L001</td>
                                <td>Producto A</td>
                                <td>A-01</td>
                                <td>Estante 1, Nivel 2</td>
                                <td>100 unidades</td>
                                <td>2024-01-15</td>
                                <td>2024-07-15</td>
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
                                    <button class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#AL002</td>
                                <td>#L002</td>
                                <td>Producto B</td>
                                <td>B-02</td>
                                <td>Estante 3, Nivel 1</td>
                                <td>75 unidades</td>
                                <td>2024-01-14</td>
                                <td>2024-07-14</td>
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
                                <td>#AL003</td>
                                <td>#L003</td>
                                <td>Producto C</td>
                                <td>C-01</td>
                                <td>Estante 2, Nivel 3</td>
                                <td>50 unidades</td>
                                <td>2024-01-13</td>
                                <td>2024-07-13</td>
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
                        Mostrando 1 a 10 de 85 registros
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

<!-- Modal Registrar Almacenaje -->
<div class="modal fade" id="registrarAlmacenajeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registrar Almacenaje</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="registrarAlmacenajeForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loteAlmacenaje">Lote</label>
                                <select class="form-control" id="loteAlmacenaje">
                                    <option value="">Seleccionar lote...</option>
                                    <option value="1">#L001 - Lote Producción A</option>
                                    <option value="2">#L002 - Lote Producción B</option>
                                    <option value="3">#L003 - Lote Producción C</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="productoAlmacenaje">Producto</label>
                                <input type="text" class="form-control" id="productoAlmacenaje" placeholder="Nombre del producto">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="zonaAlmacenaje">Zona</label>
                                <select class="form-control" id="zonaAlmacenaje">
                                    <option value="">Seleccionar zona...</option>
                                    <option value="A">Zona A</option>
                                    <option value="B">Zona B</option>
                                    <option value="C">Zona C</option>
                                    <option value="D">Zona D</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="posicionAlmacenaje">Posición</label>
                                <input type="text" class="form-control" id="posicionAlmacenaje" placeholder="Ej: Estante 1, Nivel 2">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cantidadAlmacenaje">Cantidad</label>
                                <input type="number" class="form-control" id="cantidadAlmacenaje" placeholder="0" min="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaIngreso">Fecha de Ingreso</label>
                                <input type="date" class="form-control" id="fechaIngreso">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaVencimiento">Fecha de Vencimiento</label>
                                <input type="date" class="form-control" id="fechaVencimiento">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacionesAlmacenaje">Observaciones</label>
                        <textarea class="form-control" id="observacionesAlmacenaje" rows="3" placeholder="Observaciones sobre el almacenaje..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="registrarAlmacenaje()">Registrar Almacenaje</button>
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

function registrarAlmacenaje() {
    // Aquí iría la lógica para registrar el almacenaje
    alert('Almacenaje registrado exitosamente');
    $('#registrarAlmacenajeModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

