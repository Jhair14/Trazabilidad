@extends('layouts.app')

@section('page_title', 'Recepción de Materia Prima')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck mr-1"></i>
                    Recepción de Materia Prima
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#registrarRecepcionModal">
                        <i class="fas fa-plus"></i> Registrar Recepción
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>35</h3>
                                <p>Total Recepciones</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-truck"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>28</h3>
                                <p>Completadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>5</h3>
                                <p>En Proceso</p>
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
                                <p>Pendientes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation"></i>
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
                            <option value="en_proceso">En Proceso</option>
                            <option value="completada">Completada</option>
                            <option value="rechazada">Rechazada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFecha">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por proveedor..." id="buscarProveedor">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Recepciones -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Proveedor</th>
                                <th>Materia Prima</th>
                                <th>Cantidad Recibida</th>
                                <th>Cantidad Esperada</th>
                                <th>Estado</th>
                                <th>Fecha Recepción</th>
                                <th>Responsable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#R001</td>
                                <td>Proveedor A</td>
                                <td>Harina de Trigo</td>
                                <td>50.00 kg</td>
                                <td>50.00 kg</td>
                                <td><span class="badge badge-success">Completada</span></td>
                                <td>2024-01-15</td>
                                <td>Juan Pérez</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Certificado">
                                        <i class="fas fa-certificate"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#R002</td>
                                <td>Proveedor B</td>
                                <td>Azúcar Blanca</td>
                                <td>24.50 kg</td>
                                <td>25.00 kg</td>
                                <td><span class="badge badge-warning">En Proceso</span></td>
                                <td>2024-01-14</td>
                                <td>María García</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Completar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Ajustar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#R003</td>
                                <td>Proveedor C</td>
                                <td>Sal Marina</td>
                                <td>0.00 kg</td>
                                <td>10.00 kg</td>
                                <td><span class="badge badge-danger">Pendiente</span></td>
                                <td>2024-01-13</td>
                                <td>Carlos López</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Procesar">
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
                        Mostrando 1 a 10 de 35 registros
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

<!-- Modal Registrar Recepción -->
<div class="modal fade" id="registrarRecepcionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registrar Nueva Recepción</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="registrarRecepcionForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="proveedorRecepcion">Proveedor</label>
                                <select class="form-control" id="proveedorRecepcion">
                                    <option value="">Seleccionar proveedor...</option>
                                    <option value="1">Proveedor A</option>
                                    <option value="2">Proveedor B</option>
                                    <option value="3">Proveedor C</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="materiaPrimaRecepcion">Materia Prima</label>
                                <select class="form-control" id="materiaPrimaRecepcion">
                                    <option value="">Seleccionar materia prima...</option>
                                    <option value="1">Harina de Trigo</option>
                                    <option value="2">Azúcar Blanca</option>
                                    <option value="3">Sal Marina</option>
                                    <option value="4">Aceite de Oliva</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidadEsperada">Cantidad Esperada</label>
                                <input type="number" class="form-control" id="cantidadEsperada" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidadRecibida">Cantidad Recibida</label>
                                <input type="number" class="form-control" id="cantidadRecibida" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaRecepcion">Fecha de Recepción</label>
                                <input type="date" class="form-control" id="fechaRecepcion">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loteProveedor">Lote del Proveedor</label>
                                <input type="text" class="form-control" id="loteProveedor" placeholder="Ej: LOTE-2024-001">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacionesRecepcion">Observaciones</label>
                        <textarea class="form-control" id="observacionesRecepcion" rows="3" placeholder="Observaciones sobre la recepción..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="registrarRecepcion()">Registrar Recepción</button>
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

function registrarRecepcion() {
    // Aquí iría la lógica para registrar la recepción
    alert('Recepción registrada exitosamente');
    $('#registrarRecepcionModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

