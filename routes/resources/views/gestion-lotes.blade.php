@extends('layouts.app')

@section('page_title', 'Gestión de Lotes')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes mr-1"></i>
                    Gestión de Lotes
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearLoteModal">
                        <i class="fas fa-plus"></i> Crear Lote
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>53</h3>
                                <p>Total Lotes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>12</h3>
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
                                <h3>25</h3>
                                <p>Certificados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>8</h3>
                                <p>En Proceso</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Lotes -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Pedido Asociado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#L001</td>
                                <td>Lote Producción A</td>
                                <td><span class="badge badge-success">Certificado</span></td>
                                <td>#P001</td>
                                <td>2024-01-15</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#L002</td>
                                <td>Lote Producción B</td>
                                <td><span class="badge badge-warning">En Proceso</span></td>
                                <td>#P002</td>
                                <td>2024-01-14</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#L003</td>
                                <td>Lote Producción C</td>
                                <td><span class="badge badge-info">Pendiente</span></td>
                                <td>#P003</td>
                                <td>2024-01-13</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Lote -->
<div class="modal fade" id="crearLoteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nuevo Lote</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="crearLoteForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreLote">Nombre del Lote</label>
                                <input type="text" class="form-control" id="nombreLote" placeholder="Ej: Lote de producción #001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pedidoAsociado">Pedido Asociado</label>
                                <select class="form-control" id="pedidoAsociado">
                                    <option value="">Sin pedido asociado</option>
                                    <option value="1">Pedido #001 - Cliente A</option>
                                    <option value="2">Pedido #002 - Cliente B</option>
                                    <option value="3">Pedido #003 - Cliente C</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Materias Primas</label>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Materia Prima</th>
                                        <th>Cantidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="materiasPrimasTable">
                                    <tr>
                                        <td>
                                            <select class="form-control form-control-sm">
                                                <option value="">Seleccionar...</option>
                                                <option value="1">Harina (kg)</option>
                                                <option value="2">Azúcar (kg)</option>
                                                <option value="3">Sal (g)</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" placeholder="0.00" step="0.01">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeMateriaPrima(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" onclick="addMateriaPrima()">
                            <i class="fas fa-plus"></i> Agregar Materia Prima
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="crearLote()">Crear Lote</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function addMateriaPrima() {
    const table = document.getElementById('materiasPrimasTable');
    const row = table.insertRow();
    row.innerHTML = `
        <td>
            <select class="form-control form-control-sm">
                <option value="">Seleccionar...</option>
                <option value="1">Harina (kg)</option>
                <option value="2">Azúcar (kg)</option>
                <option value="3">Sal (g)</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" placeholder="0.00" step="0.01">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeMateriaPrima(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
}

function removeMateriaPrima(button) {
    button.closest('tr').remove();
}

function crearLote() {
    // Aquí iría la lógica para crear el lote
    alert('Lote creado exitosamente');
    $('#crearLoteModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

