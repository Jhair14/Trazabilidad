@extends('layouts.app')

@section('page_title', 'Materia Prima Base')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-seedling mr-1"></i>
                    Materia Prima Base
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearMateriaPrimaModal">
                        <i class="fas fa-plus"></i> Crear Materia Prima
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>45</h3>
                                <p>Total Materias</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>38</h3>
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
                                <h3>5</h3>
                                <p>Bajo Stock</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>2</h3>
                                <p>Agotadas</p>
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
                            <option value="harina">Harinas</option>
                            <option value="azucar">Azúcares</option>
                            <option value="sal">Sales</option>
                            <option value="especias">Especias</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="disponible">Disponible</option>
                            <option value="bajo_stock">Bajo Stock</option>
                            <option value="agotado">Agotado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por nombre..." id="buscarMateria">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Materia Prima -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Unidad</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Estado</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#MP001</td>
                                <td>Harina de Trigo</td>
                                <td>Harinas</td>
                                <td>kg</td>
                                <td>150.50</td>
                                <td>50.00</td>
                                <td><span class="badge badge-success">Disponible</span></td>
                                <td>2024-01-15</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Actualizar Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#MP002</td>
                                <td>Azúcar Blanca</td>
                                <td>Azúcares</td>
                                <td>kg</td>
                                <td>25.30</td>
                                <td>30.00</td>
                                <td><span class="badge badge-warning">Bajo Stock</span></td>
                                <td>2024-01-14</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Actualizar Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#MP003</td>
                                <td>Sal Marina</td>
                                <td>Sales</td>
                                <td>g</td>
                                <td>0.00</td>
                                <td>1000.00</td>
                                <td><span class="badge badge-danger">Agotado</span></td>
                                <td>2024-01-13</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Actualizar Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando 1 a 10 de 45 registros
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

<!-- Modal Crear Materia Prima -->
<div class="modal fade" id="crearMateriaPrimaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nueva Materia Prima</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="crearMateriaPrimaForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreMateria">Nombre</label>
                                <input type="text" class="form-control" id="nombreMateria" placeholder="Ej: Harina de Trigo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoriaMateria">Categoría</label>
                                <select class="form-control" id="categoriaMateria">
                                    <option value="">Seleccionar categoría...</option>
                                    <option value="harina">Harinas</option>
                                    <option value="azucar">Azúcares</option>
                                    <option value="sal">Sales</option>
                                    <option value="especias">Especias</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unidadMateria">Unidad de Medida</label>
                                <select class="form-control" id="unidadMateria">
                                    <option value="">Seleccionar unidad...</option>
                                    <option value="kg">Kilogramos (kg)</option>
                                    <option value="g">Gramos (g)</option>
                                    <option value="l">Litros (l)</option>
                                    <option value="ml">Mililitros (ml)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stockInicial">Stock Inicial</label>
                                <input type="number" class="form-control" id="stockInicial" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stockMinimo">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stockMinimo" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="proveedorMateria">Proveedor Principal</label>
                                <select class="form-control" id="proveedorMateria">
                                    <option value="">Seleccionar proveedor...</option>
                                    <option value="1">Proveedor A</option>
                                    <option value="2">Proveedor B</option>
                                    <option value="3">Proveedor C</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcionMateria">Descripción</label>
                        <textarea class="form-control" id="descripcionMateria" rows="3" placeholder="Descripción de la materia prima..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="crearMateriaPrima()">Crear Materia Prima</button>
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

function crearMateriaPrima() {
    // Aquí iría la lógica para crear la materia prima
    alert('Materia prima creada exitosamente');
    $('#crearMateriaPrimaModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

