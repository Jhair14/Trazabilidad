@extends('layouts.app')

@section('page_title', 'Código QR')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-qrcode mr-1"></i>
                    Generador de Códigos QR
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#generarQRModal">
                        <i class="fas fa-plus"></i> Generar QR
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>120</h3>
                                <p>Total Códigos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-qrcode"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>95</h3>
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
                                <h3>20</h3>
                                <p>Escaneados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>5</h3>
                                <p>Inactivos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Generador Rápido -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Generador Rápido</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="textoQR">Texto o URL</label>
                                    <input type="text" class="form-control" id="textoQR" placeholder="Ingrese el texto o URL para el código QR">
                                </div>
                                <button class="btn btn-primary" onclick="generarQRRapido()">
                                    <i class="fas fa-qrcode"></i> Generar QR
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Vista Previa</h3>
                            </div>
                            <div class="card-body text-center">
                                <div id="qrPreview" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                    <p class="text-muted">El código QR aparecerá aquí</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroTipo">
                            <option value="">Todos los tipos</option>
                            <option value="lote">Lote</option>
                            <option value="producto">Producto</option>
                            <option value="certificado">Certificado</option>
                            <option value="url">URL</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="escaneado">Escaneado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar..." id="buscarQR">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Códigos QR -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Contenido</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Último Escaneo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#QR001</td>
                                <td><span class="badge badge-primary">Lote</span></td>
                                <td>#L001 - Lote Producción A</td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>2024-01-15</td>
                                <td>2024-01-15 14:30</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" title="Desactivar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#QR002</td>
                                <td><span class="badge badge-info">Producto</span></td>
                                <td>Producto A - Certificado</td>
                                <td><span class="badge badge-warning">Escaneado</span></td>
                                <td>2024-01-14</td>
                                <td>2024-01-14 16:45</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Reactivar">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#QR003</td>
                                <td><span class="badge badge-success">URL</span></td>
                                <td>https://empresa.com/certificado/123</td>
                                <td><span class="badge badge-danger">Inactivo</span></td>
                                <td>2024-01-13</td>
                                <td>Nunca</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" title="Activar">
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
                        Mostrando 1 a 10 de 120 registros
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

<!-- Modal Generar QR -->
<div class="modal fade" id="generarQRModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Generar Código QR</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="generarQRForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipoQR">Tipo de QR</label>
                                <select class="form-control" id="tipoQR">
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="lote">Lote</option>
                                    <option value="producto">Producto</option>
                                    <option value="certificado">Certificado</option>
                                    <option value="url">URL</option>
                                    <option value="texto">Texto</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contenidoQR">Contenido</label>
                                <input type="text" class="form-control" id="contenidoQR" placeholder="Contenido del QR">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tamanoQR">Tamaño</label>
                                <select class="form-control" id="tamanoQR">
                                    <option value="200">200x200 px</option>
                                    <option value="300">300x300 px</option>
                                    <option value="400">400x400 px</option>
                                    <option value="500">500x500 px</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formatoQR">Formato</label>
                                <select class="form-control" id="formatoQR">
                                    <option value="png">PNG</option>
                                    <option value="jpg">JPG</option>
                                    <option value="svg">SVG</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcionQR">Descripción</label>
                        <textarea class="form-control" id="descripcionQR" rows="3" placeholder="Descripción del código QR..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="generarQR()">Generar QR</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
function generarQRRapido() {
    const texto = document.getElementById('textoQR').value;
    if (!texto) {
        alert('Por favor ingrese un texto o URL');
        return;
    }
    
    const qrPreview = document.getElementById('qrPreview');
    qrPreview.innerHTML = '';
    
    QRCode.toCanvas(qrPreview, texto, {
        width: 200,
        height: 200,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        }
    }, function (error) {
        if (error) {
            console.error(error);
            qrPreview.innerHTML = '<p class="text-danger">Error al generar QR</p>';
        }
    });
}

function aplicarFiltros() {
    // Aquí iría la lógica para aplicar filtros
    alert('Filtros aplicados');
}

function generarQR() {
    // Aquí iría la lógica para generar el QR
    alert('Código QR generado exitosamente');
    $('#generarQRModal').modal('hide');
    // Recargar la página o actualizar la tabla
    location.reload();
}
</script>
@endpush

