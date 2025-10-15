@extends('layouts.app')

@section('page_title', 'Gestión de Procesos')

@section('content')
<!-- Estadísticas de Procesos -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>8</h3>
                <p>Total Procesos</p>
            </div>
            <div class="icon">
                <i class="fas fa-project-diagram"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>6</h3>
                <p>Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-play-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>1</h3>
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
                <h3>1</h3>
                <p>Inactivos</p>
            </div>
            <div class="icon">
                <i class="fas fa-pause-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Procesos -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Listado de Procesos
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCrearProceso">
                <i class="fas fa-plus"></i> Nuevo Proceso
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Duración (min)</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#PR001</td>
                        <td>Mezclado</td>
                        <td>Proceso de mezclado de ingredientes</td>
                        <td>30</td>
                        <td><span class="badge badge-success">Activo</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#PR002</td>
                        <td>Horneado</td>
                        <td>Proceso de horneado del producto</td>
                        <td>45</td>
                        <td><span class="badge badge-success">Activo</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#PR003</td>
                        <td>Enfriamiento</td>
                        <td>Proceso de enfriamiento controlado</td>
                        <td>20</td>
                        <td><span class="badge badge-warning">En Revisión</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">
        <ul class="pagination pagination-sm m-0 float-right">
            <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
            <li class="page-item"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
        </ul>
    </div>
</div>

<!-- Modal para Crear Proceso -->
<div class="modal fade" id="modalCrearProceso" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo Proceso</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreProceso">Nombre del Proceso</label>
                                <input type="text" class="form-control" id="nombreProceso" placeholder="Ej: Mezclado">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duracionProceso">Duración (minutos)</label>
                                <input type="number" class="form-control" id="duracionProceso" placeholder="30" min="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcionProceso">Descripción</label>
                        <textarea class="form-control" id="descripcionProceso" rows="3" placeholder="Descripción detallada del proceso..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="temperaturaProceso">Temperatura (°C)</label>
                                <input type="number" class="form-control" id="temperaturaProceso" placeholder="180" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="humedadProceso">Humedad (%)</label>
                                <input type="number" class="form-control" id="humedadProceso" placeholder="60" step="0.1">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Guardar Proceso</button>
            </div>
        </div>
    </div>
</div>
@endsection
