@extends('layouts.app')

@section('page_title', 'Gestión de Proveedores')

@section('content')
<!-- Estadísticas de Proveedores -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>25</h3>
                <p>Total Proveedores</p>
            </div>
            <div class="icon">
                <i class="fas fa-truck"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>20</h3>
                <p>Activos</p>
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
                <p>Pendientes</p>
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
                <p>Inactivos</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Proveedores -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Listado de Proveedores
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCrearProveedor">
                <i class="fas fa-plus"></i> Nuevo Proveedor
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
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#P001</td>
                        <td>Proveedor ABC</td>
                        <td>Juan Pérez</td>
                        <td>+1 234-567-8900</td>
                        <td>contacto@abc.com</td>
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
                        <td>#P002</td>
                        <td>Proveedor XYZ</td>
                        <td>María García</td>
                        <td>+1 234-567-8901</td>
                        <td>contacto@xyz.com</td>
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
                        <td>#P003</td>
                        <td>Proveedor DEF</td>
                        <td>Carlos López</td>
                        <td>+1 234-567-8902</td>
                        <td>contacto@def.com</td>
                        <td><span class="badge badge-warning">Pendiente</span></td>
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

<!-- Modal para Crear Proveedor -->
<div class="modal fade" id="modalCrearProveedor" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo Proveedor</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreProveedor">Nombre del Proveedor</label>
                                <input type="text" class="form-control" id="nombreProveedor" placeholder="Ej: Proveedor ABC">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactoProveedor">Persona de Contacto</label>
                                <input type="text" class="form-control" id="contactoProveedor" placeholder="Ej: Juan Pérez">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefonoProveedor">Teléfono</label>
                                <input type="tel" class="form-control" id="telefonoProveedor" placeholder="+1 234-567-8900">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emailProveedor">Email</label>
                                <input type="email" class="form-control" id="emailProveedor" placeholder="contacto@proveedor.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccionProveedor">Dirección</label>
                        <textarea class="form-control" id="direccionProveedor" rows="2" placeholder="Dirección completa del proveedor..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Guardar Proveedor</button>
            </div>
        </div>
    </div>
</div>
@endsection
