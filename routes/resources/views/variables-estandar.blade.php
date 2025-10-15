@extends('layouts.app')

@section('page_title', 'Variables Estándar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sliders-h mr-1"></i>
                    Variables Estándar
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearVariableModal">
                        <i class="fas fa-plus"></i> Crear Variable
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>25</h3>
                                <p>Total Variables</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>20</h3>
                                <p>Activas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>3</h3>
                                <p>En Revisión</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>2</h3>
                                <p>Inactivas</p>
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
                        <select class="form-control" id="filtroCategoria">
                            <option value="">Todas las categorías</option>
                            <option value="temperatura">Temperatura</option>
                            <option value="humedad">Humedad</option>
                            <option value="presion">Presión</option>
                            <option value="tiempo">Tiempo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="activa">Activa</option>
                            <option value="inactiva">Inactiva</option>
                            <option value="revision">En Revisión</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar..." id="buscarVariable">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Variables -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Valor Mínimo</th>
                                <th>Valor Máximo</th>
                                <th>Unidad</th>
                                <th>Estado</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#V001</td>
                                <td>Temperatura de Cocción</td>
                                <td><span class="badge badge-primary">Temperatura</span></td>
                                <td>180°C</td>
                                <td>220°C</td>
                                <td>°C</td>
                                <td><span class="badge badge-success">Activa</span></td>
                                <td>2024-01-15</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Desactivar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#V002</td>
                                <td>Humedad Relativa</td>
                                <td><span class="badge badge-info">Humedad</span></td>
                                <td>45%</td>
                                <td>65%</td>
                                <td>%</td>
                                <td><span class="badge badge-warning">En Revisión</span></td>
                                <td>2024-01-14</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Aprobar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#V003</td>
                                <td>Tiempo de Mezclado</td>
                                <td><span class="badge badge-success">Tiempo</span></td>
                                <td>5 min</td>
                                <td>15 min</td>
                                <td>min</td>
                                <td><span class="badge badge-danger">Inactiva</span></td>
                                <td>2024-01-13</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Activar">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando 1 a 10 de 25 registros
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

<!-- Modal Crear Variable -->
<div class="modal fade" id="crearVariableModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nueva Variable</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="crearVariableForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreVariable">Nombre de la Variable</label>
                                <input type="text" class="form-control" id="nombreVariable" placeholder="Ej: Temperatura de Cocción">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoriaVariable">Categoría</label>
                                <select class="form-control" id="categoriaVariable">
                                    <option value="">Seleccionar categoría...</option>
                                    <option value="temperatura">Temperatura</option>
                                    <option value="humedad">Humedad</option>
                                    <option value="presion">Presión</option>
                                    <option value="tiempo">Tiempo</option>
                                    <option value="peso">Peso</option>
                                    <option value="volumen">Volumen</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="valorMinimo">Valor Mínimo</label>
                                <input type="number" class="form-control" id="valorMinimo" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="valorMaximo">Valor Máximo</label>
                                <input type="number" class="form-control" id="valorMaximo" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unidadVariable">Unidad</label>
                                <input type="text" class="form-control" id="unidadVariable" placeholder="Ej: °C, %, min">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="procesoVariable">Proceso Asociado</label>
                                <select class="form-control" id="procesoVariable">
                                    <option value="">Seleccionar proceso...</option>
                                    <option value="1">Mezclado</option>
                                    <option value="2">Cocción</option>
                                    <option value="3">Enfriamiento</option>
                                    <option value="4">Empaque</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="criticidadVariable">Criticidad</label>
                                <select class="form-control" id="criticidadVariable">
                                    <option value="baja">Baja</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                    <option value="critica">Crítica</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcionVariable">Descripción</label>
                        <textarea class="form-control" id="descripcionVariable" rows="3" placeholder="Descripción de la variable estándar..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="crearVariable()">Crear Variable</button>
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

function crearVariable() {
    // Aquí iría la lógica para crear la variable
    alert('Variable creada exitosamente');
    $('#crearVariableModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

