@extends('layouts.app')

@section('page_title', 'Mis Pedidos')

@section('content')
<!-- Estadísticas de Mis Pedidos -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>8</h3>
                <p>Mis Pedidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>3</h3>
                <p>Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>2</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
        <div class="inner">
                <h3>3</h3>
                <p>Completados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check"></i>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Mis Pedidos -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Mis Pedidos
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNuevoPedido">
                <i class="fas fa-plus"></i> Nuevo Pedido
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Progreso</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#P001</td>
                        <td>Producto de alta calidad</td>
                        <td>15/01/2024</td>
                        <td><span class="badge badge-primary">En Proceso</span></td>
                        <td>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" style="width: 60%"></div>
                            </div>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#P002</td>
                        <td>Producto estándar</td>
                        <td>10/01/2024</td>
                        <td><span class="badge badge-success">Completado</span></td>
                        <td>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" title="Certificado">
                                <i class="fas fa-certificate"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#P003</td>
                        <td>Producto premium</td>
                        <td>05/01/2024</td>
                        <td><span class="badge badge-warning">Pendiente</span></td>
                        <td>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-warning" style="width: 20%"></div>
                            </div>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
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

<!-- Modal para Nuevo Pedido -->
<div class="modal fade" id="modalNuevoPedido" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo Pedido</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="descripcionPedido">Descripción del Pedido</label>
                        <textarea class="form-control" id="descripcionPedido" rows="3" placeholder="Describe detalladamente tu pedido..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidadPedido">Cantidad</label>
                                <input type="number" class="form-control" id="cantidadPedido" placeholder="0" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaEntrega">Fecha de Entrega Deseada</label>
                                <input type="date" class="form-control" id="fechaEntrega">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="prioridadPedido">Prioridad</label>
                        <select class="form-control" id="prioridadPedido">
                            <option value="normal">Normal</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacionesPedido">Observaciones Adicionales</label>
                        <textarea class="form-control" id="observacionesPedido" rows="2" placeholder="Cualquier observación especial..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Crear Pedido</button>
            </div>
        </div>
    </div>
</div>
@endsection
