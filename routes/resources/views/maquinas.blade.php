@extends('layouts.app')

@section('page_title', 'Gestión de Máquinas')

@section('content')

<!-- Estadísticas de Máquinas -->
<div class="row ">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>12</h3>
                <p>Total Máquinas</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>9</h3>
                <p>Operativas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>2</h3>
                <p>Mantenimiento</p>
            </div>
            <div class="icon">
                <i class="fas fa-tools"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>1</h3>
                <p>Fuera de Servicio</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Máquinas -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Listado de Máquinas
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCrearMaquina">
                <i class="fas fa-plus"></i> Nueva Máquina
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Tarjeta 1 -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                <div style="border-top: 3px solid #007bff !important;" class="card h-100 card shadow-sm">
                    <div class="card-body flex flex-column justify-center items-center">
                        <div class="text-center">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPhH7sMQFO0IYgFvtbpf7PDe39VSIwHc0F_w&s" class="img-fluid mb-3" alt="Mezcladora Industrial" style="max-height: 150px; object-fit: cover;">
                            <h5 class="card-title">Mezcladora Industrial</h5>
                        </div>
                        <p class="card-text">
                            <span class="badge badge-primary">Mezclado</span><br>
                            <strong>Ubicación:</strong> Línea A - Estación 1<br>
                            <strong>Estado:</strong> <span class="badge badge-success">Operativa</span><br>
                            <strong>Último Mantenimiento:</strong> 15/01/2024
                        </p>
                        <div class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-secondary" title="Mantenimiento">
                                    <i class="fas fa-tools"></i> Mantenimiento
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2 -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                <div style="border-top: 3px solid #007bff !important;" class="card h-100 shadow-sm">
                    <div class="card-body flex flex-column justify-center items-center">
                        <div class="text-center">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPhH7sMQFO0IYgFvtbpf7PDe39VSIwHc0F_w&s" class="img-fluid mb-3" alt="Horno Convectivo" style="max-height: 150px; object-fit: cover;">
                            <h5 class="card-title">Horno Convectivo</h5>
                        </div>
                        <p class="card-text">
                            <span class="badge badge-info">Horneado</span><br>
                            <strong>Ubicación:</strong> Línea A - Estación 2<br>
                            <strong>Estado:</strong> <span class="badge badge-success">Operativa</span><br>
                            <strong>Último Mantenimiento:</strong> 10/01/2024
                        </p>
                        <div class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-secondary" title="Mantenimiento">
                                    <i class="fas fa-tools"></i> Mantenimiento
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 3 -->
            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                <div style="border-top: 3px solid #007bff !important;" class="card h-100 shadow-sm">
                    <div class="card-body flex flex-column justify-center items-center">
                        <div class="text-center">
                            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPhH7sMQFO0IYgFvtbpf7PDe39VSIwHc0F_w&s" class="img-fluid mb-3" alt="Enfriador Industrial" style="max-height: 150px; object-fit: cover;">
                            <h5 class="card-title">Enfriador Industrial</h5>
                        </div>
                        <p class="card-text">
                            <span class="badge badge-secondary">Enfriamiento</span><br>
                            <strong>Ubicación:</strong> Línea B - Estación 1<br>
                            <strong>Estado:</strong> <span class="badge badge-warning">Mantenimiento</span><br>
                            <strong>Último Mantenimiento:</strong> 20/01/2024
                        </p>
                        <div class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-secondary" title="Mantenimiento">
                                    <i class="fas fa-tools"></i> Mantenimiento
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

<!-- Modal para Crear Máquina -->
<div class="modal fade" id="modalCrearMaquina" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nueva Máquina</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreMaquina">Nombre de la Máquina</label>
                                <input type="text" class="form-control" id="nombreMaquina" placeholder="Ej: Mezcladora Industrial">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipoMaquina">Tipo de Máquina</label>
                                <select class="form-control" id="tipoMaquina">
                                    <option value="mezclado">Mezclado</option>
                                    <option value="horneado">Horneado</option>
                                    <option value="enfriamiento">Enfriamiento</option>
                                    <option value="empaque">Empaque</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ubicacionMaquina">Ubicación</label>
                                <input type="text" class="form-control" id="ubicacionMaquina" placeholder="Ej: Línea A - Estación 1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modeloMaquina">Modelo</label>
                                <input type="text" class="form-control" id="modeloMaquina" placeholder="Ej: MX-2000">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaInstalacion">Fecha de Instalación</label>
                                <input type="date" class="form-control" id="fechaInstalacion">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="proximoMantenimiento">Próximo Mantenimiento</label>
                                <input type="date" class="form-control" id="proximoMantenimiento">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Guardar Máquina</button>
            </div>
        </div>
    </div>
</div>
@endsection
