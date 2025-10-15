@extends('layouts.app')

@section('page_title', 'Gestión de Usuarios')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-1"></i>
                    Gestión de Usuarios
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearUsuarioModal">
                        <i class="fas fa-plus"></i> Crear Usuario
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
                                <p>Total Usuarios</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
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
                                <i class="fas fa-user-check"></i>
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
                                <i class="fas fa-user-clock"></i>
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
                                <i class="fas fa-user-times"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroRol">
                            <option value="">Todos los roles</option>
                            <option value="admin">Administrador</option>
                            <option value="operador">Operador</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="cliente">Cliente</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por nombre..." id="buscarUsuario">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último Acceso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#U001</td>
                                <td>Juan Pérez</td>
                                <td>juan.perez@empresa.com</td>
                                <td><span class="badge badge-primary">Administrador</span></td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>2024-01-15 10:30</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Desactivar">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#U002</td>
                                <td>María García</td>
                                <td>maria.garcia@empresa.com</td>
                                <td><span class="badge badge-info">Operador</span></td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>2024-01-15 09:15</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Desactivar">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#U003</td>
                                <td>Carlos López</td>
                                <td>carlos.lopez@empresa.com</td>
                                <td><span class="badge badge-warning">Supervisor</span></td>
                                <td><span class="badge badge-warning">Pendiente</span></td>
                                <td>Nunca</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Activar">
                                        <i class="fas fa-user-check"></i>
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

<!-- Modal Crear Usuario -->
<div class="modal fade" id="crearUsuarioModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nuevo Usuario</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="crearUsuarioForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreUsuario">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombreUsuario" placeholder="Ej: Juan Pérez">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emailUsuario">Email</label>
                                <input type="email" class="form-control" id="emailUsuario" placeholder="juan.perez@empresa.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rolUsuario">Rol</label>
                                <select class="form-control" id="rolUsuario">
                                    <option value="">Seleccionar rol...</option>
                                    <option value="admin">Administrador</option>
                                    <option value="operador">Operador</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="cliente">Cliente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefonoUsuario">Teléfono</label>
                                <input type="tel" class="form-control" id="telefonoUsuario" placeholder="+1 234 567 8900">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="passwordUsuario">Contraseña</label>
                                <input type="password" class="form-control" id="passwordUsuario" placeholder="Mínimo 8 caracteres">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirmarPassword">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirmarPassword" placeholder="Repetir contraseña">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="departamentoUsuario">Departamento</label>
                                <select class="form-control" id="departamentoUsuario">
                                    <option value="">Seleccionar departamento...</option>
                                    <option value="produccion">Producción</option>
                                    <option value="calidad">Calidad</option>
                                    <option value="almacen">Almacén</option>
                                    <option value="administracion">Administración</option>
                                </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="crearUsuario()">Crear Usuario</button>
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

function crearUsuario() {
    // Aquí iría la lógica para crear el usuario
    alert('Usuario creado exitosamente');
    $('#crearUsuarioModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

