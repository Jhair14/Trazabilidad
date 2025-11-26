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
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $certificados->total() }}</h3>
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
                                <h3>{{ $certificados->getCollection()->filter(function($c) { 
                                    $eval = $c->latestFinalEvaluation;
                                    return $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló'); 
                                })->count() }}</h3>
                                <p>Certificados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $certificados->getCollection()->filter(function($c) { 
                                    $eval = $c->latestFinalEvaluation;
                                    return $eval && str_contains(strtolower($eval->reason ?? ''), 'falló'); 
                                })->count() }}</h3>
                                <p>No Certificados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $certificados->where('storage', '!=', null)->count() }}</h3>
                                <p>Almacenados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-warehouse"></i>
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

                <!-- Lista de Certificados (similar al proyecto antiguo) -->
                @if($certificados->count() === 0)
                <div class="alert alert-info text-center">
                    <p class="mb-0">No hay lotes certificados.</p>
                </div>
                @else
                <div class="row">
                    @foreach($certificados as $certificado)
                    @php
                        $finalEval = $certificado->latestFinalEvaluation;
                        $esFallido = $finalEval && str_contains(strtolower($finalEval->reason ?? ''), 'falló');
                    @endphp
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border {{ $esFallido ? 'border-danger' : 'border-success' }}">
                            <div class="card-header {{ $esFallido ? 'bg-danger text-white' : 'bg-success text-white' }}">
                                <h5 class="mb-0">
                                    Lote #{{ $certificado->batch_code ?? $certificado->batch_id }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-gray-600 mb-1">
                                    <strong>Nombre:</strong> {{ $certificado->name ?? 'Sin nombre' }}
                                </p>
                                <p class="text-gray-500 text-sm mb-2">
                                    <strong>Fecha de Creación:</strong> {{ \Carbon\Carbon::parse($certificado->creation_date)->format('d/m/Y') }}
                                </p>
                                @if($finalEval)
                                <p class="text-sm mb-2">
                                    <strong>Fecha de Evaluación:</strong> {{ \Carbon\Carbon::parse($finalEval->evaluation_date)->format('d/m/Y') }}
                                </p>
                                @endif
                            </div>
                            <div class="card-footer">
                                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                    <a href="{{ route('certificado.show', $certificado->batch_id) }}" 
                                       class="btn btn-sm {{ $esFallido ? 'btn-danger' : 'btn-success' }}">
                                        <i class="fas fa-certificate mr-1"></i> Ver Certificado
                                    </a>
                                    <a href="{{ route('certificado.qr', $certificado->batch_id) }}" 
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-qrcode mr-1"></i> Generar QR
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Paginación -->
                @if($certificados->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando {{ $certificados->firstItem() }} a {{ $certificados->lastItem() }} de {{ $certificados->total() }} registros
                    </div>
                    <nav>
                        {{ $certificados->links() }}
                    </nav>
                </div>
                @endif
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
    const estado = document.getElementById('filtroEstado').value;
    const fecha = document.getElementById('filtroFecha').value;
    const buscar = document.getElementById('buscarLote').value;
    
    const url = new URL(window.location);
    if (estado) url.searchParams.set('estado', estado);
    if (fecha) url.searchParams.set('fecha', fecha);
    if (buscar) url.searchParams.set('buscar', buscar);
    window.location = url;
}
</script>
@endpush

