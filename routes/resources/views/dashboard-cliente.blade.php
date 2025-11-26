@extends('layouts.app')

@section('page_title', 'Panel de Cliente')

@section('content')
<div class="row">
    <!-- KPIs del Cliente -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_pedidos'] }}</h3>
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
                <h3>{{ $stats['pedidos_pendientes'] }}</h3>
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
                <h3>{{ $stats['pedidos_completados'] }}</h3>
                <p>Completados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['pedidos_en_proceso'] }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
        </div>
    </div>
</div>

@if($ultimoPedido)
<div class="row">
    <!-- Seguimiento del Último Pedido -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-timeline mr-1"></i>
                    Seguimiento de tu Último Pedido
                </h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5>Pedido #{{ $ultimoPedido->order_number ?? $ultimoPedido->order_id }}</h5>
                        <p><strong>Descripción:</strong> {{ $ultimoPedido->description ?? 'Sin descripción' }}</p>
                        <p><strong>Fecha de creación:</strong> {{ \Carbon\Carbon::parse($ultimoPedido->creation_date)->format('d/m/Y') }}</p>
                        @if($ultimoPedido->delivery_date)
                        <p><strong>Fecha de entrega:</strong> {{ \Carbon\Carbon::parse($ultimoPedido->delivery_date)->format('d/m/Y') }}</p>
                        @endif
                        <p><strong>Prioridad:</strong> 
                            @if($ultimoPedido->priority >= 8)
                                <span class="badge badge-danger">Alta</span>
                            @elseif($ultimoPedido->priority >= 5)
                                <span class="badge badge-warning">Media</span>
                            @else
                                <span class="badge badge-info">Normal</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        @php
                            $ultimoLote = $ultimoPedido->batches->first();
                            $estadoPedido = 'Pendiente';
                            if ($ultimoLote) {
                                if ($ultimoLote->latestFinalEvaluation) {
                                    $eval = $ultimoLote->latestFinalEvaluation;
                                    if (str_contains(strtolower($eval->reason ?? ''), 'falló')) {
                                        $estadoPedido = 'No Certificado';
                                    } else {
                                        $estadoPedido = 'Certificado';
                                    }
                                } elseif ($ultimoLote->processMachineRecords->isNotEmpty()) {
                                    $estadoPedido = 'En Proceso';
                                } else {
                                    $estadoPedido = 'Lote Creado';
                                }
                            }
                        @endphp
                        <p><strong>Estado actual:</strong> 
                            @if($estadoPedido === 'Certificado')
                                <span class="badge badge-success">{{ $estadoPedido }}</span>
                            @elseif($estadoPedido === 'No Certificado')
                                <span class="badge badge-danger">{{ $estadoPedido }}</span>
                            @elseif($estadoPedido === 'En Proceso')
                                <span class="badge badge-primary">{{ $estadoPedido }}</span>
                            @else
                                <span class="badge badge-info">{{ $estadoPedido }}</span>
                            @endif
                        </p>
                        @if($ultimoLote)
                        <p><strong>Lote asociado:</strong> #{{ $ultimoLote->batch_code ?? $ultimoLote->batch_id }}</p>
                        <p><strong>Nombre del lote:</strong> {{ $ultimoLote->name ?? 'N/A' }}</p>
                        @endif
                    </div>
                </div>
                
                <!-- Timeline de Estados -->
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-primary">Progreso del Pedido</span>
                    </div>
                    
                    <!-- 1. Pedido Creado -->
                    <div>
                        <i class="fas fa-check bg-green"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($ultimoPedido->creation_date)->format('d/m/Y') }}</span>
                            <h3 class="timeline-header">Pedido Creado</h3>
                            <div class="timeline-body">
                                Tu pedido ha sido registrado exitosamente.
                            </div>
                        </div>
                    </div>
                    
                    <!-- 2. Solicitud de Materia Prima -->
                    @if($ultimoPedido->materialRequests->isNotEmpty())
                    @php $primeraSolicitud = $ultimoPedido->materialRequests->first(); @endphp
                    <div>
                        <i class="fas fa-check bg-green"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($primeraSolicitud->request_date)->format('d/m/Y') }}</span>
                            <h3 class="timeline-header">Materia Prima Solicitada</h3>
                            <div class="timeline-body">
                                Solicitud #{{ $primeraSolicitud->request_number ?? $primeraSolicitud->request_id }} - 
                                @if($primeraSolicitud->priority == 0)
                                    <span class="badge badge-success">Completada</span>
                                @else
                                    <span class="badge badge-warning">Pendiente</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                        <div class="timeline-item">
                            <span class="time">Pendiente</span>
                            <h3 class="timeline-header">Materia Prima Solicitada</h3>
                            <div class="timeline-body">
                                Esperando solicitud de materia prima.
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- 3. Lote Creado -->
                    @if($ultimoLote)
                    <div>
                        <i class="fas fa-check bg-green"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($ultimoLote->creation_date)->format('d/m/Y') }}</span>
                            <h3 class="timeline-header">Lote de Producción Creado</h3>
                            <div class="timeline-body">
                                Lote #{{ $ultimoLote->batch_code ?? $ultimoLote->batch_id }} - {{ $ultimoLote->name ?? 'Sin nombre' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- 4. Proceso de Transformación -->
                    @if($ultimoLote->processMachineRecords->isNotEmpty())
                    @php 
                        $totalMaquinas = $ultimoLote->processMachineRecords->count();
                        $maquinasCompletadas = $ultimoLote->processMachineRecords->where('meets_standard', true)->count();
                        $ultimoRegistro = $ultimoLote->processMachineRecords->sortByDesc('record_date')->first();
                    @endphp
                    <div>
                        <i class="fas fa-cog bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ $ultimoRegistro->record_date ? \Carbon\Carbon::parse($ultimoRegistro->record_date)->format('d/m/Y H:i') : 'En proceso' }}</span>
                            <h3 class="timeline-header">Proceso de Transformación</h3>
                            <div class="timeline-body">
                                Progreso: {{ $maquinasCompletadas }} de {{ $totalMaquinas }} máquinas completadas
                                @if($maquinasCompletadas == $totalMaquinas)
                                    <span class="badge badge-success">Completado</span>
                                @else
                                    <span class="badge badge-warning">En Proceso</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                        <div class="timeline-item">
                            <span class="time">Pendiente</span>
                            <h3 class="timeline-header">Proceso de Transformación</h3>
                            <div class="timeline-body">
                                Esperando inicio del proceso de transformación.
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- 5. Certificación -->
                    @if($ultimoLote && $ultimoLote->latestFinalEvaluation)
                    @php $eval = $ultimoLote->latestFinalEvaluation; @endphp
                    <div>
                        <i class="fas {{ str_contains(strtolower($eval->reason ?? ''), 'falló') ? 'fa-times' : 'fa-check' }} bg-{{ str_contains(strtolower($eval->reason ?? ''), 'falló') ? 'red' : 'green' }}"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($eval->evaluation_date)->format('d/m/Y H:i') }}</span>
                            <h3 class="timeline-header">Certificación</h3>
                            <div class="timeline-body">
                                @if(str_contains(strtolower($eval->reason ?? ''), 'falló'))
                                    <span class="badge badge-danger">No Certificado</span> - {{ $eval->reason }}
                                @else
                                    <span class="badge badge-success">Certificado</span> - {{ $eval->reason }}
                                @endif
                                @if($eval->inspector)
                                    <br><small>Inspector: {{ $eval->inspector->first_name }} {{ $eval->inspector->last_name }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @elseif($ultimoLote && $ultimoLote->processMachineRecords->isNotEmpty())
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                        <div class="timeline-item">
                            <span class="time">Pendiente</span>
                            <h3 class="timeline-header">Certificación</h3>
                            <div class="timeline-body">
                                Esperando certificación del lote.
                            </div>
                        </div>
                    </div>
                    @else
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                        <div class="timeline-item">
                            <span class="time">Pendiente</span>
                            <h3 class="timeline-header">Certificación</h3>
                            <div class="timeline-body">
                                El lote debe completar el proceso de transformación primero.
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- 6. Almacenamiento -->
                    @if($ultimoLote && $ultimoLote->storage->isNotEmpty())
                    @php $almacen = $ultimoLote->storage->first(); @endphp
                    <div>
                        <i class="fas fa-check bg-green"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($almacen->storage_date)->format('d/m/Y H:i') }}</span>
                            <h3 class="timeline-header">Almacenado</h3>
                            <div class="timeline-body">
                                Ubicación: {{ $almacen->location }} - Condición: {{ $almacen->condition }}
                                <br>Cantidad: {{ number_format($almacen->quantity, 2) }}
                            </div>
                        </div>
                    </div>
                    @else
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                        <div class="timeline-item">
                            <span class="time">Pendiente</span>
                            <h3 class="timeline-header">Almacenado</h3>
                            <div class="timeline-body">
                                El producto será almacenado una vez certificado.
                            </div>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Mis Pedidos Recientes -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Mis Pedidos
                </h3>
            </div>
            <div class="card-body">
                @if($pedidos->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    No tienes pedidos registrados aún.
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Entrega</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Lotes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidos as $pedido)
                            @php
                                $estadoPedido = 'Pendiente';
                                $tieneLotes = $pedido->batches->isNotEmpty();
                                $loteCertificado = false;
                                
                                if ($tieneLotes) {
                                    $loteCertificado = $pedido->batches->some(function($batch) {
                                        $eval = $batch->latestFinalEvaluation;
                                        return $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló');
                                    });
                                    
                                    $loteEnProceso = $pedido->batches->some(function($batch) {
                                        return $batch->processMachineRecords->isNotEmpty() && !$batch->latestFinalEvaluation;
                                    });
                                    
                                    if ($loteCertificado) {
                                        $estadoPedido = 'Certificado';
                                    } elseif ($loteEnProceso) {
                                        $estadoPedido = 'En Proceso';
                                    } elseif ($tieneLotes) {
                                        $estadoPedido = 'Lote Creado';
                                    }
                                }
                            @endphp
                            <tr>
                                <td>#{{ $pedido->order_number ?? $pedido->order_id }}</td>
                                <td>{{ $pedido->description ?? 'Sin descripción' }}</td>
                                <td>{{ \Carbon\Carbon::parse($pedido->creation_date)->format('d/m/Y') }}</td>
                                <td>{{ $pedido->delivery_date ? \Carbon\Carbon::parse($pedido->delivery_date)->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if($pedido->priority >= 8)
                                        <span class="badge badge-danger">Alta</span>
                                    @elseif($pedido->priority >= 5)
                                        <span class="badge badge-warning">Media</span>
                                    @else
                                        <span class="badge badge-info">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    @if($estadoPedido === 'Certificado')
                                        <span class="badge badge-success">{{ $estadoPedido }}</span>
                                    @elseif($estadoPedido === 'En Proceso')
                                        <span class="badge badge-primary">{{ $estadoPedido }}</span>
                                    @elseif($estadoPedido === 'Lote Creado')
                                        <span class="badge badge-info">{{ $estadoPedido }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ $estadoPedido }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tieneLotes)
                                        <span class="badge badge-info">{{ $pedido->batches->count() }} lote(s)</span>
                                    @else
                                        <span class="badge badge-secondary">Sin lotes</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="verDetallesPedido({{ $pedido->order_id }})" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($loteCertificado)
                                        @php
                                            $loteCert = $pedido->batches->first(function($batch) {
                                                $eval = $batch->latestFinalEvaluation;
                                                return $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló');
                                            });
                                        @endphp
                                        @if($loteCert)
                                        <a href="{{ route('certificado.show', $loteCert->batch_id) }}" class="btn btn-primary btn-sm" title="Ver Certificado" target="_blank">
                                            <i class="fas fa-certificate"></i>
                                        </a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalles Pedido -->
<div class="modal fade" id="detallesPedidoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detalles del Pedido</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detallesPedidoContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando detalles...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > div {
    position: relative;
    margin-bottom: 15px;
    margin-right: 10px;
    margin-left: 60px;
}

.timeline > div:before,
.timeline > div:after {
    content: "";
    display: table;
}

.timeline > div:after {
    clear: both;
}

.timeline > div > .timeline-item {
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 3px;
    background: #fff;
    color: #444;
    margin-left: 60px;
    padding: 0;
    position: relative;
}

.timeline > div > .timeline-item > .time {
    color: #999;
    float: right;
    font-size: 12px;
    padding: 10px;
}

.timeline > div > .timeline-item > .timeline-header {
    border-bottom: 1px solid #f4f4f4;
    color: #444;
    font-size: 16px;
    line-height: 1.1;
    margin: 0;
    padding: 10px;
}

.timeline > div > .timeline-item > .timeline-body,
.timeline > div > .timeline-item > .timeline-footer {
    padding: 10px;
}

.timeline > div > i {
    background: #6c757d;
    border-radius: 50%;
    color: #fff;
    font-size: 12px;
    height: 30px;
    left: 18px;
    line-height: 30px;
    position: absolute;
    text-align: center;
    top: 0;
    width: 30px;
}

.timeline > div > .bg-green {
    background-color: #28a745 !important;
}

.timeline > div > .bg-blue {
    background-color: #007bff !important;
}

.timeline > div > .bg-gray {
    background-color: #6c757d !important;
}

.timeline > div > .bg-red {
    background-color: #dc3545 !important;
}
</style>
@endpush

@push('scripts')
<script>
function verDetallesPedido(orderId) {
    $('#detallesPedidoModal').modal('show');
    $('#detallesPedidoContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Cargando detalles...</p>
        </div>
    `);
    
    fetch(`{{ url('dashboard-cliente/pedido') }}/${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                $('#detallesPedidoContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${data.error}
                    </div>
                `);
                return;
            }
            
            let lotesHtml = '';
            if (data.lotes && data.lotes.length > 0) {
                data.lotes.forEach(function(lote) {
                    let maquinasHtml = '';
                    if (lote.maquinas && lote.maquinas.length > 0) {
                        maquinasHtml = '<ul class="mb-0">';
                        lote.maquinas.forEach(function(maq) {
                            maquinasHtml += `<li>${maq.nombre} (${maq.maquina}) - ${maq.cumple_estandar ? '<span class="badge badge-success">Cumple</span>' : '<span class="badge badge-danger">No Cumple</span>'}</li>`;
                        });
                        maquinasHtml += '</ul>';
                    }
                    
                    let almacenHtml = '';
                    if (lote.almacenamiento && lote.almacenamiento.length > 0) {
                        almacenHtml = '<ul class="mb-0">';
                        lote.almacenamiento.forEach(function(alm) {
                            almacenHtml += `<li>Ubicación: ${alm.location} - Condición: ${alm.condition} - Cantidad: ${alm.quantity}</li>`;
                        });
                        almacenHtml += '</ul>';
                    }
                    
                    lotesHtml += `
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Lote #${lote.batch_code || lote.batch_id} - ${lote.name}</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Estado:</strong> <span class="badge badge-${lote.estado === 'Certificado' ? 'success' : lote.estado === 'En Proceso' ? 'primary' : 'warning'}">${lote.estado}</span></p>
                                <p><strong>Fecha Creación:</strong> ${lote.creation_date}</p>
                                ${lote.start_time ? `<p><strong>Inicio:</strong> ${lote.start_time}</p>` : ''}
                                ${lote.end_time ? `<p><strong>Fin:</strong> ${lote.end_time}</p>` : ''}
                                ${lote.certificacion ? `
                                    <div class="alert alert-${lote.estado === 'Certificado' ? 'success' : 'danger'}">
                                        <strong>Certificación:</strong><br>
                                        Fecha: ${lote.certificacion.evaluation_date}<br>
                                        Motivo: ${lote.certificacion.reason}<br>
                                        Inspector: ${lote.certificacion.inspector}
                                    </div>
                                ` : ''}
                                ${maquinasHtml ? `<p><strong>Máquinas Procesadas:</strong></p>${maquinasHtml}` : ''}
                                ${almacenHtml ? `<p><strong>Almacenamiento:</strong></p>${almacenHtml}` : ''}
                            </div>
                        </div>
                    `;
                });
            } else {
                lotesHtml = '<p class="text-muted">No hay lotes asociados a este pedido.</p>';
            }
            
            let solicitudesHtml = '';
            if (data.solicitudes_materia_prima && data.solicitudes_materia_prima.length > 0) {
                solicitudesHtml = '<ul class="list-group mb-3">';
                data.solicitudes_materia_prima.forEach(function(sol) {
                    let materialesHtml = '';
                    sol.materiales.forEach(function(mat) {
                        materialesHtml += `<li>${mat.material}: ${mat.cantidad_solicitada} (Aprobado: ${mat.cantidad_aprobada})</li>`;
                    });
                    solicitudesHtml += `
                        <li class="list-group-item">
                            <strong>Solicitud #${sol.request_number}</strong><br>
                            Fecha: ${sol.request_date} - Requerida: ${sol.required_date}<br>
                            Estado: <span class="badge badge-${sol.estado === 'Completada' ? 'success' : 'warning'}">${sol.estado}</span><br>
                            Materiales:<ul>${materialesHtml}</ul>
                        </li>
                    `;
                });
                solicitudesHtml += '</ul>';
            } else {
                solicitudesHtml = '<p class="text-muted">No hay solicitudes de materia prima para este pedido.</p>';
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <h5>Información del Pedido</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">Número de Pedido</th>
                                <td>#${data.pedido.order_number || data.pedido.order_id}</td>
                            </tr>
                            <tr>
                                <th>Descripción</th>
                                <td>${data.pedido.description || 'Sin descripción'}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Creación</th>
                                <td>${data.pedido.creation_date}</td>
                            </tr>
                            ${data.pedido.delivery_date ? `
                            <tr>
                                <th>Fecha de Entrega</th>
                                <td>${data.pedido.delivery_date}</td>
                            </tr>
                            ` : ''}
                            <tr>
                                <th>Prioridad</th>
                                <td>
                                    ${data.pedido.priority >= 8 ? '<span class="badge badge-danger">Alta</span>' : 
                                      data.pedido.priority >= 5 ? '<span class="badge badge-warning">Media</span>' : 
                                      '<span class="badge badge-info">Normal</span>'}
                                </td>
                            </tr>
                            ${data.pedido.observations ? `
                            <tr>
                                <th>Observaciones</th>
                                <td>${data.pedido.observations}</td>
                            </tr>
                            ` : ''}
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5>Lotes de Producción</h5>
                        ${lotesHtml}
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h5>Solicitudes de Materia Prima</h5>
                        ${solicitudesHtml}
                    </div>
                </div>
            `;
            
            $('#detallesPedidoContent').html(content);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#detallesPedidoContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los detalles del pedido.
                </div>
            `);
        });
}
</script>
@endpush

