@extends('layouts.app')

@section('page_title', 'Certificados')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-certificate mr-1"></i>
                    Gestión de Certificados
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#generarCertificadoModal">
                        <i class="fas fa-plus"></i> Generar Certificado
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>65</h3>
                                <p>Total Certificados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>58</h3>
                                <p>Válidos</p>
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
                                <p>Por Vencer</p>
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
                                <p>Vencidos</p>
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
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="valido">Válido</option>
                            <option value="por_vencer">Por Vencer</option>
                            <option value="vencido">Vencido</option>
                            <option value="revocado">Revocado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFecha">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por lote..." id="buscarLote">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Certificados -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Lote</th>
                                <th>Producto</th>
                                <th>Fecha Emisión</th>
                                <th>Fecha Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#C001</td>
                                <td>#L001</td>
                                <td>Producto A</td>
                                <td>2024-01-15</td>
                                <td>2024-07-15</td>
                                <td><span class="badge badge-success">Válido</span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Descargar PDF">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="QR Code">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Revocar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#C002</td>
                                <td>#L002</td>
                                <td>Producto B</td>
                                <td>2024-01-14</td>
                                <td>2024-07-14</td>
                                <td><span class="badge badge-warning">Por Vencer</span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Descargar PDF">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="QR Code">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Renovar">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#C003</td>
                                <td>#L003</td>
                                <td>Producto C</td>
                                <td>2024-01-13</td>
                                <td>2024-07-13</td>
                                <td><span class="badge badge-danger">Vencido</span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-secondary btn-sm" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Renovar">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando 1 a 10 de 65 registros
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

<!-- Modal Generar Certificado -->
<div class="modal fade" id="generarCertificadoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Generar Nuevo Certificado</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="generarCertificadoForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loteCertificado">Lote</label>
                                <select class="form-control" id="loteCertificado">
                                    <option value="">Seleccionar lote...</option>
                                    <option value="1">#L001 - Lote Producción A</option>
                                    <option value="2">#L002 - Lote Producción B</option>
                                    <option value="3">#L003 - Lote Producción C</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="productoCertificado">Producto</label>
                                <input type="text" class="form-control" id="productoCertificado" placeholder="Nombre del producto">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaEmision">Fecha de Emisión</label>
                                <input type="date" class="form-control" id="fechaEmision">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechaVencimiento">Fecha de Vencimiento</label>
                                <input type="date" class="form-control" id="fechaVencimiento">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipoCertificado">Tipo de Certificado</label>
                                <select class="form-control" id="tipoCertificado">
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="calidad">Certificado de Calidad</option>
                                    <option value="trazabilidad">Certificado de Trazabilidad</option>
                                    <option value="origen">Certificado de Origen</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="autoridadCertificadora">Autoridad Certificadora</label>
                                <input type="text" class="form-control" id="autoridadCertificadora" placeholder="Nombre de la autoridad">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacionesCertificado">Observaciones</label>
                        <textarea class="form-control" id="observacionesCertificado" rows="3" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="generarCertificado()">Generar Certificado</button>
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

function generarCertificado() {
    // Aquí iría la lógica para generar el certificado
    alert('Certificado generado exitosamente');
    $('#generarCertificadoModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

