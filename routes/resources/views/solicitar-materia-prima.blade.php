@extends('layouts.app')

@section('page_title', 'Solicitar Materia Prima')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Solicitar Materia Prima
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearSolicitudModal">
                        <i class="fas fa-plus"></i> Nueva Solicitud
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
                                <p>Total Solicitudes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>8</h3>
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
                                <h3>15</h3>
                                <p>Aprobadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>2</h3>
                                <p>Rechazadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times"></i>
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
                            <option value="aprobada">Aprobada</option>
                            <option value="rechazada">Rechazada</option>
                            <option value="entregada">Entregada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFecha">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por solicitante..." id="buscarSolicitante">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Solicitudes -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Solicitante</th>
                                <th>Materia Prima</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Fecha Solicitud</th>
                                <th>Fecha Entrega</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#S001</td>
                                <td>Juan Pérez</td>
                                <td>Harina de Trigo</td>
                                <td>50 kg</td>
                                <td><span class="badge badge-warning">Pendiente</span></td>
                                <td>2024-01-15</td>
                                <td>2024-01-20</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Aprobar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Rechazar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#S002</td>
                                <td>María García</td>
                                <td>Azúcar Blanca</td>
                                <td>25 kg</td>
                                <td><span class="badge badge-success">Aprobada</span></td>
                                <td>2024-01-14</td>
                                <td>2024-01-19</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Entregar">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#S003</td>
                                <td>Carlos López</td>
                                <td>Sal Marina</td>
                                <td>10 kg</td>
                                <td><span class="badge badge-danger">Rechazada</span></td>
                                <td>2024-01-13</td>
                                <td>2024-01-18</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Revisar">
                                        <i class="fas fa-edit"></i>
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

<!-- Modal Crear Solicitud -->
<div class="modal fade" id="crearSolicitudModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nueva Solicitud de Materia Prima</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="crearSolicitudForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="materiaPrima">Materia Prima</label>
                                <select class="form-control" id="materiaPrima">
                                    <option value="">Seleccionar materia prima...</option>
                                    <option value="1">Harina de Trigo</option>
                                    <option value="2">Azúcar Blanca</option>
                                    <option value="3">Sal Marina</option>
                                    <option value="4">Aceite de Oliva</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidadSolicitud">Cantidad</label>
                                <input type="number" class="form-control" id="cantidadSolicitud" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaNecesidad">Fecha de Necesidad</label>
                                <input type="date" class="form-control" id="fechaNecesidad">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prioridadSolicitud">Prioridad</label>
                                <select class="form-control" id="prioridadSolicitud">
                                    <option value="normal">Normal</option>
                                    <option value="alta">Alta</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="justificacionSolicitud">Justificación</label>
                        <textarea class="form-control" id="justificacionSolicitud" rows="3" placeholder="Explique por qué necesita esta materia prima..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="crearSolicitud()">Crear Solicitud</button>
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

function crearSolicitud() {
    // Aquí iría la lógica para crear la solicitud
    alert('Solicitud creada exitosamente');
    $('#crearSolicitudModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

