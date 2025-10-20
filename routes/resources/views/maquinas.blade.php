@extends('layouts.app')

@section('page_title', 'Gestión de Máquinas')

@section('content')
<!-- Estadísticas de Máquinas -->
<div class="row">
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
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Imagen</th>
                        <th>Tipo</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Último Mantenimiento</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#M001</td>
                        <td>Mezcladora Industrial</td>
                        <td><img class="w-30" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPhH7sMQFO0IYgFvtbpf7PDe39VSIwHc0F_w&s" alt="Mezcladora Industrial"></td>
                        <td><span class="badge badge-primary">Mezclado</span></td>
                        <td>Línea A - Estación 1</td>
                        <td><span class="badge badge-success">Operativa</span></td>
                        <td>15/01/2024</td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary" title="Mantenimiento">
                                <i class="fas fa-tools"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#M002</td>
                        <td>Horno Convectivo</td>
                        <td><img class="w-30" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPhH7sMQFO0IYgFvtbpf7PDe39VSIwHc0F_w&s" alt="Horno Convectivo"></td>
                        <td><span class="badge badge-info">Horneado</span></td>
                        <td>Línea A - Estación 2</td>
                        <td><span class="badge badge-success">Operativa</span></td>
                        <td>10/01/2024</td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary" title="Mantenimiento">
                                <i class="fas fa-tools"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#M003</td>
                        <td>Enfriador Industrial</td>
                        <td><img class="w-30" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPhH7sMQFO0IYgFvtbpf7PDe39VSIwHc0F_w&s" alt="Enfriador Industrial"></td>
                        <td><span class="badge badge-secondary">Enfriamiento</span></td>
                        <td>Línea B - Estación 1</td>
                        <td><span class="badge badge-warning">Mantenimiento</span></td>
                        <td>20/01/2024</td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary" title="Mantenimiento">
                                <i class="fas fa-tools"></i>
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
