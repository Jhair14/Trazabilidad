@extends('layouts.app')

@section('page_title', 'Gestión de Almacenaje')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Estilos del modal */
    .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 20px;
        position: relative;
    }
    
    .modal-dialog.modal-lg {
        max-width: 900px;
    }
    
    /* Contenedor del mapa - crítico para contener Leaflet */
    #registrarAlmacenajeModal .form-group {
        position: relative;
        overflow: visible;
    }
    
    /* Contenedor interno del mapa con overflow hidden */
    #registrarAlmacenajeModal .form-group > div[style*="overflow: hidden"] {
        position: relative !important;
        overflow: hidden !important;
        z-index: 1;
    }
    
    /* Estilos del mapa */
    #map {
        height: 100% !important;
        width: 100% !important;
        position: relative !important;
        z-index: 1;
        min-height: 400px;
    }
    
    /* Asegurar que el contenedor del mapa tenga dimensiones correctas */
    #registrarAlmacenajeModal .form-group > div[style*="overflow: hidden"] {
        width: 100% !important;
        height: 400px !important;
        min-height: 400px !important;
        position: relative !important;
    }
    
    /* Forzar que los tiles se rendericen correctamente */
    #registrarAlmacenajeModal .leaflet-tile-container {
        width: 100% !important;
        height: 100% !important;
    }
    
    /* Asegurar que los tiles no se corten */
    #registrarAlmacenajeModal .leaflet-tile {
        width: 256px !important;
        height: 256px !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Controlar z-index de todos los elementos de Leaflet para que no escapen del modal */
    #registrarAlmacenajeModal #map .leaflet-container {
        height: 100% !important;
        width: 100% !important;
        position: relative !important;
        z-index: 1 !important;
    }
    
    #registrarAlmacenajeModal #map .leaflet-pane {
        z-index: 2 !important;
        position: absolute !important;
    }
    
    #registrarAlmacenajeModal #map .leaflet-tile-pane {
        z-index: 1 !important;
    }
    
    #registrarAlmacenajeModal #map .leaflet-overlay-pane {
        z-index: 2 !important;
    }
    
    #registrarAlmacenajeModal #map .leaflet-shadow-pane {
        z-index: 3 !important;
    }
    
    #registrarAlmacenajeModal #map .leaflet-marker-pane {
        z-index: 4 !important;
    }
    
    #registrarAlmacenajeModal #map .leaflet-tooltip-pane {
        z-index: 5 !important;
    }
    
    #registrarAlmacenajeModal #map .leaflet-popup-pane {
        z-index: 6 !important;
    }
    
    #registrarAlmacenajeModal #map .leaflet-control-container {
        z-index: 7 !important;
    }
    
    /* Prevenir que Leaflet escape del contenedor */
    #registrarAlmacenajeModal .leaflet-pane,
    #registrarAlmacenajeModal .leaflet-tile-container,
    #registrarAlmacenajeModal .leaflet-marker-container {
        position: absolute !important;
    }
    
    /* Asegurar que los marcadores sean visibles */
    #registrarAlmacenajeModal .leaflet-marker-icon {
        z-index: 4 !important;
        position: absolute !important;
    }
    
    /* Asegurar que los controles de zoom sean visibles */
    #registrarAlmacenajeModal .leaflet-control-zoom {
        z-index: 7 !important;
        position: relative !important;
    }
    
    /* Asegurar que las imágenes de los tiles se carguen */
    #registrarAlmacenajeModal .leaflet-tile {
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Estilos para botón deshabilitado durante el envío */
    #submitBtn.disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    #submitBtn:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .btn-spinner {
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-warehouse mr-1"></i>
                    Almacenaje de Lotes Certificados
                </h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $stats['disponibles'] ?? 0 }}</h3>
                                <p>Lotes Disponibles</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-warehouse"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $stats['certificados'] ?? 0 }}</h3>
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
                                <h3>{{ $stats['sin_certificar'] ?? 0 }}</h3>
                                <p>Sin Certificar</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $stats['almacenados'] ?? 0 }}</h3>
                                <p>Ya Almacenados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Lotes Disponibles -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID Lote</th>
                                <th>Nombre</th>
                                <th>Cliente</th>
                                <th>Cantidad Producida</th>
                                <th>Fecha Creación</th>
                                <th>Estado Almacenaje</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotes as $lote)
                            @php
                                $eval = $lote->latestFinalEvaluation;
                                $esCertificado = $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló');
                                // Usar cantidad producida, si es 0 o NULL usar cantidad objetivo del lote
                                $cantidadMostrar = $lote->cantidad_producida ?? 0;
                                $esCantidadObjetivo = false;
                                if ($cantidadMostrar == 0 || $cantidadMostrar == null) {
                                    $cantidadMostrar = $lote->cantidad_objetivo ?? 0;
                                    $esCantidadObjetivo = ($cantidadMostrar > 0);
                                }
                                // Para el botón, usar la cantidad que se mostrará
                                $cantidadParaAlmacenar = $cantidadMostrar;
                            @endphp
                            <tr>
                                <td>#{{ $lote->codigo_lote ?? $lote->lote_id }}</td>
                                <td>{{ $lote->nombre ?? 'Sin nombre' }}</td>
                                <td>{{ $lote->order->customer->razon_social ?? 'N/A' }}</td>
                                <td>
                                    {{ number_format($cantidadMostrar, 2) }}
                                    @if(($lote->cantidad_producida ?? 0) == 0 && ($lote->cantidad_objetivo ?? 0) > 0)
                                        <small class="text-muted d-block">(Objetivo)</small>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($lote->fecha_creacion)->format('d/m/Y') }}</td>
                                <td>
                                    @if($lote->storage->isNotEmpty())
                                        @php
                                            $almacenaje = $lote->storage->first();
                                        @endphp
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Almacenado
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            Ubicación: {{ $almacenaje->ubicacion ?? 'N/A' }}<br>
                                            Fecha: {{ \Carbon\Carbon::parse($almacenaje->fecha_almacenaje)->format('d/m/Y') }}
                                        </small>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($esCertificado)
                                        <span class="badge badge-success">Certificado</span>
                                    @else
                                        <span class="badge badge-warning">Sin Certificar</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($esCertificado)
                                        @if($lote->storage->isEmpty())
                                            <button class="btn btn-primary btn-sm" title="Almacenar" onclick="almacenarLote({{ $lote->lote_id }}, '{{ $lote->codigo_lote ?? $lote->lote_id }}', '{{ $lote->nombre ?? 'Sin nombre' }}', {{ $cantidadParaAlmacenar }}, {{ $esCantidadObjetivo ? 'true' : 'false' }}, {{ $lote->pedido_id }})">
                                                <i class="fas fa-warehouse"></i> Almacenar
                                            </button>
                                        @else
                                            @php
                                                $pedido = $lote->order;
                                                $envioId = $pedido ? $pedido->getPlantaCrudsEnvioId() : null;
                                                $propuestaPdfUrl = $pedido ? $pedido->getPropuestaVehiculosPdfUrl() : null;
                                                $aprobarRechazarUrl = $pedido ? $pedido->getAprobarRechazarUrl() : null;
                                                
                                                // Solo mostrar los botones si el estado es realmente "pendiente_aprobacion_trazabilidad"
                                                $mostrarAprobarRechazar = false;
                                                if ($envioId && $aprobarRechazarUrl && $pedido) {
                                                    $mostrarAprobarRechazar = $pedido->isEnvioPendienteAprobacionTrazabilidad();
                                                }
                                            @endphp
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                <button class="btn btn-info btn-sm" title="Ver Detalles" onclick="verAlmacenaje({{ $lote->lote_id }})">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                                {{-- Ocultado: PDF Propuesta
                                                @if($envioId && $propuestaPdfUrl)
                                                    <a href="{{ $propuestaPdfUrl }}" target="_blank" class="btn btn-danger btn-sm" title="Descargar PDF Propuesta">
                                                        <i class="fas fa-file-pdf"></i> PDF Propuesta
                                                    </a>
                                                @endif
                                                --}}
                                                @if($envioId && $aprobarRechazarUrl && $mostrarAprobarRechazar)
                                                    <button class="btn btn-success btn-sm" title="Aprobar Propuesta" onclick="abrirModalAprobarAlmacenaje('{{ $aprobarRechazarUrl }}', {{ $envioId }})">
                                                        <i class="fas fa-check"></i> Aprobar
                                                    </button>
                                                    <button class="btn btn-warning btn-sm" title="Rechazar Propuesta" onclick="abrirModalRechazarAlmacenaje('{{ $aprobarRechazarUrl }}', {{ $envioId }})">
                                                        <i class="fas fa-times"></i> Rechazar
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">Requiere certificación</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay lotes certificados disponibles</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Almacenaje -->
<div class="modal fade" id="registrarAlmacenajeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registrar Almacenaje para Lote #<span id="modal_batch_code"></span></h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('almacenaje.store') }}" id="registrarAlmacenajeForm">
                    @csrf
                    <input type="hidden" id="lote_id" name="lote_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p><strong>Lote:</strong> <span id="modal_batch_name"></span></p>
                        </div>
                    </div>
                    
                    <!-- Información del Pedido -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart mr-2"></i>Información del Pedido</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Cantidad a Almacenar:</strong>
                                    <span id="modal_quantity" class="ml-2"></span>
                                </div>
                        <div class="col-md-6">
                                    <strong>Pedido:</strong>
                                    <span id="modal_order_number" class="ml-2"></span>
                                </div>
                            </div>
                            
                            <!-- Tabla de Destinos -->
                            <div id="destinations_table_container" style="display: none;">
                                <h6 class="mb-2"><strong>Destinos del Pedido:</strong></h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Dirección</th>
                                                <th>Referencia</th>
                                                <th>Contacto</th>
                                                <th>Teléfono</th>
                                                <th>Instrucciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="destinations_tbody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                            <div class="form-group">
                                <label for="condicion">Condición <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('condicion') is-invalid @enderror" 
                                       id="condicion" name="condicion" value="{{ old('condicion') }}" 
                                       placeholder="Ej: Buen estado, Seco y ventilado" required>
                        @error('condicion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                                <small class="form-text text-muted">Estado físico del producto</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" 
                                  rows="3" placeholder="Observaciones sobre el almacenaje...">{{ old('observaciones') }}</textarea>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Ubicación de Recojo</h5>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Ubicación fija de la planta:</strong> La ubicación de recojo está configurada y no puede ser modificada desde aquí. 
                        Para cambiar la ubicación de la planta, ve a <strong>"Mi Ubicación"</strong> en el menú lateral.
                    </div>
                    
                    <div class="form-group">
                        <label>Dirección de Recojo</label>
                        <input type="text" class="form-control" 
                               id="pickup_address" name="pickup_address" 
                               value="{{ config('services.planta.direccion', 'Av. Ejemplo 123, Santa Cruz, Bolivia') }}" 
                               readonly style="background-color: #e9ecef;">
                        <small class="form-text text-muted">Dirección completa donde se recogerá el producto</small>
                    </div>

                    <div class="form-group">
                        <label>Ubicación en el Mapa</label>
                        <div id="map-container" style="position: relative; overflow: hidden; border: 1px solid #ddd; border-radius: 4px; height: 400px; width: 100%; background-color: #f0f0f0;">
                            <div id="map" style="height: 100%; width: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0;"></div>
                        </div>
                        <small class="form-text text-muted mt-2 d-block">Ubicación fija de la planta (solo visualización)</small>
                        <input type="hidden" id="pickup_latitude" name="pickup_latitude" value="{{ config('services.planta.latitud', '-17.8146') }}">
                        <input type="hidden" id="pickup_longitude" name="pickup_longitude" value="{{ config('services.planta.longitud', '-63.1561') }}">
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Importante:</strong> La cantidad a almacenar se toma automáticamente del lote (cantidad producida u objetivo). Solo se puede almacenar una vez. Al almacenar, se creará automáticamente el envío en PlantaCruds con la ubicación de recojo seleccionada en el mapa.
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelBtn">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="btn-text">Registrar Almacenaje</span>
                            <span class="btn-spinner" style="display: none;">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modales para aprobar/rechazar propuesta desde almacenaje -->
<!-- Modal Aprobar Propuesta -->
<div class="modal fade" id="aprobarPropuestaAlmacenajeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="aprobarPropuestaAlmacenajeForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle"></i> Aprobar Propuesta de Vehículos
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de aprobar la propuesta de vehículos para el envío <strong id="envioIdAprobarAlmacenaje"></strong>?</p>
                    <p class="text-muted">Al aprobar, el envío cambiará su estado a "pendiente" y podrá proceder con la asignación del transportista.</p>
                    <div class="form-group">
                        <label>Observaciones (opcional)</label>
                        <textarea class="form-control" name="observaciones" id="aprobarObservacionesAlmacenaje" rows="3" placeholder="Comentarios sobre la aprobación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Aprobar Propuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rechazar Propuesta -->
<div class="modal fade" id="rechazarPropuestaAlmacenajeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rechazarPropuestaAlmacenajeForm">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle"></i> Rechazar Propuesta de Vehículos
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de rechazar la propuesta de vehículos para el envío <strong id="envioIdRechazarAlmacenaje"></strong>?</p>
                    <p class="text-muted">Al rechazar, el envío será cancelado.</p>
                    <div class="form-group">
                        <label>Observaciones <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="observaciones" id="rechazarObservacionesAlmacenaje" rows="3" required placeholder="Razón del rechazo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Rechazar Propuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Mensaje de Éxito -->
<div class="modal fade" id="mensajeExitoAlmacenajeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Éxito
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Mensaje de Error -->
<div class="modal fade" id="mensajeErrorAlmacenajeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-circle"></i> Error
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Mensaje de Advertencia -->
<div class="modal fade" id="mensajeAdvertenciaAlmacenajeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Advertencia
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
let currentBatchId = null;
let map = null;
let marker = null;

// Datos de pedidos cargados desde el backend
const ordersData = @json($ordersData ?? []);

function almacenarLote(loteId, codigoLote, nombreLote, quantity, isTargetQuantity, pedidoId) {
    currentBatchId = loteId;
    $('#lote_id').val(loteId);
    $('#modal_batch_code').text(codigoLote);
    $('#modal_batch_name').text(nombreLote);
    $('#condicion').val('');
    
    // Establecer la cantidad automáticamente y mostrar referencia
    const qty = parseFloat(quantity) || 0;
    $('#modal_quantity').text(qty.toFixed(2) + ' ' + (isTargetQuantity ? '(Objetivo)' : '(Producida)'));
    
    $('#observaciones').val('');
    // NO limpiar los valores de ubicación - siempre usar la configuración de la planta
    $('#pickup_address').val('{{ config('services.planta.direccion', 'Av. Ejemplo 123, Santa Cruz, Bolivia') }}');
    $('#pickup_latitude').val('{{ config('services.planta.latitud', '-17.8146') }}');
    $('#pickup_longitude').val('{{ config('services.planta.longitud', '-63.1561') }}');
    
    // Cargar información del pedido
    if (pedidoId) {
        loadOrderInfo(pedidoId);
    } else {
        $('#modal_order_number').text('N/A');
        $('#destinations_table_container').hide();
    }
    
    $('#registrarAlmacenajeModal').modal('show');
}

function loadOrderInfo(pedidoId) {
    // Obtener información del pedido desde los datos cargados
    const orderData = ordersData[pedidoId];
    
    if (orderData) {
        $('#modal_order_number').text(orderData.numero_pedido || 'N/A');
        
        // Mostrar destinos si existen
        if (orderData.destinations && orderData.destinations.length > 0) {
            const tbody = $('#destinations_tbody');
            tbody.empty();
            
            orderData.destinations.forEach(function(dest) {
                const row = `
                    <tr>
                        <td>${dest.address || 'N/A'}</td>
                        <td>${dest.reference || '-'}</td>
                        <td>${dest.contact_name || '-'}</td>
                        <td>${dest.contact_phone || '-'}</td>
                        <td>${dest.delivery_instructions || '-'}</td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            $('#destinations_table_container').show();
        } else {
            $('#destinations_table_container').hide();
        }
    } else {
        $('#modal_order_number').text('N/A');
        $('#destinations_table_container').hide();
    }
}

function initMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.error('Elemento del mapa no encontrado');
        return;
    }
    
    // Verificar que el elemento tenga dimensiones visibles y correctas
    const containerWidth = mapElement.offsetWidth || mapElement.clientWidth;
    const containerHeight = mapElement.offsetHeight || mapElement.clientHeight;
    
    if (containerWidth === 0 || containerHeight === 0) {
        // Esperar un poco más si el contenedor aún no tiene dimensiones
        setTimeout(() => initMap(), 300);
        return;
    }
    
    // Si el mapa ya existe, removerlo completamente
    if (map) {
        try {
            map.remove();
            map = null;
            marker = null;
        } catch(e) {
            console.log('Error removiendo mapa:', e);
            map = null;
            marker = null;
        }
    }
    
    // Obtener coordenadas de la configuración de la planta (solo lectura)
    const plantaLat = parseFloat($('#pickup_latitude').val()) || {{ config('services.planta.latitud', '-17.8146') }};
    const plantaLng = parseFloat($('#pickup_longitude').val()) || {{ config('services.planta.longitud', '-63.1561') }};
    
    // Crear mapa en modo solo lectura (sin interacción)
    map = L.map('map', {
        zoomControl: true,
        attributionControl: true,
        preferCanvas: true, // Usar canvas para mejor rendimiento
        dragging: false,
        touchZoom: false,
        doubleClickZoom: false,
        scrollWheelZoom: false,
        boxZoom: false,
        keyboard: false,
        zoomAnimation: true,
        fadeAnimation: true,
        markerZoomAnimation: true
    });
    
    // Agregar capa de OpenStreetMap con opciones mejoradas
    const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
        minZoom: 10,
        tileSize: 256,
        zoomOffset: 0,
        updateWhenZooming: true,
        updateWhenIdle: true,
        keepBuffer: 2
    });
    
    tileLayer.addTo(map);
    
    // Establecer la vista
    map.setView([plantaLat, plantaLng], 15);
    
    // Invalidar tamaño múltiples veces para asegurar renderizado correcto
    setTimeout(() => {
        if (!map) return;
        
        map.invalidateSize(true);
        map.setView([plantaLat, plantaLng], 15);
        
        // Forzar actualización de los tiles
        setTimeout(() => {
            if (!map) return;
            
            map.invalidateSize(true);
            map.setView([plantaLat, plantaLng], 15);
            
            // Forzar redraw de todas las capas
            map.eachLayer(function(layer) {
                if (layer instanceof L.TileLayer) {
                    layer.redraw();
                }
            });
            
            // Agregar marcador después de que el mapa esté completamente renderizado
            setTimeout(() => {
                if (map) {
                    addMarker(plantaLat, plantaLng);
                    // Una última invalidación para asegurar que todo esté correcto
                    map.invalidateSize(true);
                }
            }, 300);
        }, 200);
    }, 500);
}

function addMarker(lat, lng) {
    if (!map) {
        console.error('Mapa no inicializado');
        return;
    }
    
    // Remover marcador anterior si existe
    if (marker) {
        map.removeLayer(marker);
        marker = null;
    }
    
    // Crear nuevo marcador fijo (no arrastrable) con icono personalizado
    const icon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    marker = L.marker([lat, lng], {
        draggable: false,
        icon: icon
    }).addTo(map);
    
    // Agregar popup con información
    const direccion = $('#pickup_address').val() || 'Ubicación de la Planta';
    marker.bindPopup(`<strong>Ubicación de la Planta</strong><br>${direccion}`).openPopup();
    
    // Asegurar que el marcador sea visible
    map.setView([lat, lng], map.getZoom());
}

// Inicializar mapa cuando se abre el modal completamente
$('#registrarAlmacenajeModal').on('shown.bs.modal', function () {
    // Limpiar mapa anterior completamente
    if (map) {
        try {
            map.remove();
        } catch(e) {
            console.log('Error removiendo mapa:', e);
        }
        map = null;
        marker = null;
    }
    
    // Esperar a que el modal esté completamente renderizado y visible
    // Aumentar el delay para asegurar que el contenedor tenga dimensiones correctas
    setTimeout(() => {
        // Verificar que el contenedor del mapa tenga dimensiones válidas
        const mapContainer = document.getElementById('map');
        if (mapContainer && mapContainer.offsetWidth > 0 && mapContainer.offsetHeight > 0) {
            initMap();
        } else {
            // Si aún no tiene dimensiones, esperar un poco más
            setTimeout(() => {
                if (mapContainer && mapContainer.offsetWidth > 0 && mapContainer.offsetHeight > 0) {
                    initMap();
                }
            }, 200);
        }
    }, 500);
});

// Redimensionar mapa cuando la ventana cambia de tamaño
$(window).on('resize', function() {
    if (map && $('#registrarAlmacenajeModal').hasClass('show')) {
        setTimeout(function() {
            if (map) {
                map.invalidateSize(true);
            }
        }, 100);
    }
});

// Limpiar modal al cerrar
$('#registrarAlmacenajeModal').on('hidden.bs.modal', function () {
    currentBatchId = null;
    $('#registrarAlmacenajeForm')[0].reset();
    // Limpiar marcador pero mantener el mapa para reutilización
    if (marker && map) {
        map.removeLayer(marker);
        marker = null;
    }
    // Restaurar valores de ubicación de la planta (no limpiarlos)
    $('#pickup_latitude').val('{{ config('services.planta.latitud', '-17.8146') }}');
    $('#pickup_longitude').val('{{ config('services.planta.longitud', '-63.1561') }}');
    $('#pickup_address').val('{{ config('services.planta.direccion', 'Av. Ejemplo 123, Santa Cruz, Bolivia') }}');
});

let almacenajeDataGlobal = null;

function verAlmacenaje(batchId) {
    fetch(`{{ url('almacenaje') }}/lote/${batchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                almacenajeDataGlobal = data[0];
                const almacenaje = almacenajeDataGlobal;
                
                // Formatear fechas
                const fechaAlmacenaje = almacenaje.fecha_almacenaje 
                    ? new Date(almacenaje.fecha_almacenaje).toLocaleDateString('es-ES', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })
                    : 'N/A';
                
                const fechaRetiro = almacenaje.fecha_retiro 
                    ? new Date(almacenaje.fecha_retiro).toLocaleDateString('es-ES', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })
                    : null;
                
                const fechaCreacionPedido = almacenaje.fecha_creacion_pedido 
                    ? new Date(almacenaje.fecha_creacion_pedido).toLocaleDateString('es-ES')
                    : 'N/A';
                
                const fechaEntregaPedido = almacenaje.fecha_entrega_pedido 
                    ? new Date(almacenaje.fecha_entrega_pedido).toLocaleDateString('es-ES')
                    : 'N/A';
                
                // Construir tabla de productos
                let productosHtml = '<p class="text-muted">No hay productos registrados</p>';
                if (almacenaje.productos && almacenaje.productos.length > 0) {
                    productosHtml = `
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Código</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <th>Precio</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${almacenaje.productos.map(prod => `
                                        <tr>
                                            <td>${prod.nombre || 'N/A'}</td>
                                            <td>${prod.codigo || '-'}</td>
                                            <td>${parseFloat(prod.cantidad || 0).toFixed(2)}</td>
                                            <td>${prod.unidad || 'N/A'}</td>
                                            <td>${parseFloat(prod.precio || 0).toFixed(2)}</td>
                                            <td><span class="badge badge-info">${prod.estado || 'N/A'}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                // Construir tabla de destinos
                let destinosHtml = '<p class="text-muted">No hay destinos registrados</p>';
                if (almacenaje.destinos && almacenaje.destinos.length > 0) {
                    destinosHtml = `
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Dirección</th>
                                        <th>Referencia</th>
                                        <th>Contacto</th>
                                        <th>Teléfono</th>
                                        <th>Instrucciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${almacenaje.destinos.map(dest => `
                                        <tr>
                                            <td>${dest.direccion || 'N/A'}</td>
                                            <td>${dest.referencia || '-'}</td>
                                            <td>${dest.nombre_contacto || '-'}</td>
                                            <td>${dest.telefono_contacto || '-'}</td>
                                            <td>${dest.instrucciones_entrega || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                const content = `
                    <div id="almacenaje-pdf-content">
                        <div class="row mb-3">
                            <div class="col-md-12 text-center">
                                <h4 class="mb-0"><i class="fas fa-warehouse mr-2"></i>Información de Almacenaje</h4>
                            </div>
                        </div>
                        
                        <!-- Información del Lote -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-box mr-2"></i>Información del Lote</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Código de Lote:</strong> ${almacenaje.codigo_lote || almacenaje.lote_id || 'N/A'}</p>
                                        <p><strong>Nombre del Lote:</strong> ${almacenaje.nombre_lote || 'N/A'}</p>
                                        <p><strong>Cantidad Objetivo:</strong> ${parseFloat(almacenaje.cantidad_objetivo || 0).toFixed(2)}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Ubicación:</strong> ${almacenaje.ubicacion || 'N/A'}</p>
                                        <p><strong>Condición:</strong> ${almacenaje.condicion || 'N/A'}</p>
                                        <p><strong>Cantidad Almacenada:</strong> <strong class="text-success">${parseFloat(almacenaje.cantidad || 0).toFixed(2)}</strong></p>
                                        <p><strong>Fecha de Almacenaje:</strong> ${fechaAlmacenaje}</p>
                                        ${fechaRetiro ? `<p><strong>Fecha de Retiro:</strong> ${fechaRetiro}</p>` : ''}
                                    </div>
                                </div>
                                ${almacenaje.observaciones ? `
                                <div class="row">
                                    <div class="col-md-12">
                                        <p><strong>Observaciones:</strong></p>
                                        <p class="text-muted">${almacenaje.observaciones}</p>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        
                        <!-- Información de Recojo -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-map-marker-alt mr-2"></i>Ubicación de Recojo</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Dirección:</strong> ${almacenaje.direccion_recojo || 'N/A'}</p>
                                ${almacenaje.referencia_recojo ? `<p><strong>Referencia:</strong> ${almacenaje.referencia_recojo}</p>` : ''}
                                ${almacenaje.latitud_recojo && almacenaje.longitud_recojo ? `
                                <p><strong>Coordenadas:</strong> ${parseFloat(almacenaje.latitud_recojo).toFixed(6)}, ${parseFloat(almacenaje.longitud_recojo).toFixed(6)}</p>
                                ` : ''}
                            </div>
                        </div>
                        
                        <!-- Información del Pedido -->
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-shopping-cart mr-2"></i>Información del Pedido</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Número de Pedido:</strong> ${almacenaje.numero_pedido || 'N/A'}</p>
                                        <p><strong>Nombre del Pedido:</strong> ${almacenaje.nombre_pedido || 'N/A'}</p>
                                        <p><strong>Estado:</strong> <span class="badge badge-info">${almacenaje.estado_pedido || 'N/A'}</span></p>
                                        <p><strong>Fecha de Creación:</strong> ${fechaCreacionPedido}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Fecha de Entrega:</strong> ${fechaEntregaPedido}</p>
                                        ${almacenaje.descripcion_pedido ? `<p><strong>Descripción:</strong> ${almacenaje.descripcion_pedido}</p>` : ''}
                                        ${almacenaje.observaciones_pedido ? `<p><strong>Observaciones:</strong> ${almacenaje.observaciones_pedido}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información del Cliente -->
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-user mr-2"></i>Información del Cliente</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Razón Social:</strong> ${almacenaje.cliente_razon_social || 'N/A'}</p>
                                        <p><strong>Nombre Comercial:</strong> ${almacenaje.cliente_nombre_comercial || 'N/A'}</p>
                                        ${almacenaje.cliente_nit ? `<p><strong>NIT:</strong> ${almacenaje.cliente_nit}</p>` : ''}
                                        <p><strong>Contacto:</strong> ${almacenaje.cliente_contacto || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        ${almacenaje.cliente_direccion ? `<p><strong>Dirección:</strong> ${almacenaje.cliente_direccion}</p>` : ''}
                                        ${almacenaje.cliente_telefono ? `<p><strong>Teléfono:</strong> ${almacenaje.cliente_telefono}</p>` : ''}
                                        ${almacenaje.cliente_email ? `<p><strong>Email:</strong> ${almacenaje.cliente_email}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Productos del Pedido -->
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-boxes mr-2"></i>Productos del Pedido</h5>
                            </div>
                            <div class="card-body">
                                ${productosHtml}
                            </div>
                        </div>
                        
                        <!-- Destinos del Pedido -->
                        <div class="card mb-3">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-truck mr-2"></i>Destinos del Pedido</h5>
                            </div>
                            <div class="card-body">
                                ${destinosHtml}
                            </div>
                        </div>
                        
                        ${almacenaje.envios && almacenaje.envios.length > 0 ? `
                        <!-- Información de Envíos -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-shipping-fast mr-2"></i>Envíos Creados</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Código de Envío</th>
                                                <th>ID Envío</th>
                                                <th>ID Destino</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${almacenaje.envios.map(envio => `
                                                <tr>
                                                    <td>${envio.codigo_envio || 'N/A'}</td>
                                                    <td>${envio.envio_id || 'N/A'}</td>
                                                    <td>${envio.destino_id || 'N/A'}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                // Crear o actualizar modal de detalles
                if ($('#verAlmacenajeModal').length === 0) {
                    $('body').append(`
                        <div class="modal fade" id="verAlmacenajeModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">
                                            <i class="fas fa-warehouse mr-1"></i>
                                            Detalles del Almacenaje
                                        </h4>
                                        <button type="button" class="close" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body" id="verAlmacenajeContent" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" onclick="descargarPDFAlmacenaje()">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            Descargar PDF
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                            <i class="fas fa-times mr-1"></i>
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                }
                
                $('#verAlmacenajeContent').html(content);
                $('#verAlmacenajeModal').modal('show');
            } else {
                alert('No se encontró información de almacenaje para este lote');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles del almacenaje');
        });
}

function descargarPDFAlmacenaje() {
    if (!almacenajeDataGlobal) {
        alert('No hay datos de almacenaje para descargar');
        return;
    }
    
    try {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const margin = 15;
        let yPos = margin;
        
        // Función para agregar nueva página si es necesario
        const checkPageBreak = (requiredHeight) => {
            if (yPos + requiredHeight > pageHeight - margin) {
                pdf.addPage();
                yPos = margin;
                return true;
            }
            return false;
        };
        
        // Título
        pdf.setFontSize(18);
        pdf.setFont(undefined, 'bold');
        pdf.text('Información de Almacenaje', pageWidth / 2, yPos, { align: 'center' });
        yPos += 10;
        
        // Línea separadora
        pdf.setDrawColor(200, 200, 200);
        pdf.line(margin, yPos, pageWidth - margin, yPos);
        yPos += 8;
        
        const almacenaje = almacenajeDataGlobal;
        
        // Formatear fechas
        const fechaAlmacenaje = almacenaje.fecha_almacenaje 
            ? new Date(almacenaje.fecha_almacenaje).toLocaleDateString('es-ES', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
            : 'N/A';
        
        const fechaRetiro = almacenaje.fecha_retiro 
            ? new Date(almacenaje.fecha_retiro).toLocaleDateString('es-ES', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
            : null;
        
        const fechaCreacionPedido = almacenaje.fecha_creacion_pedido 
            ? new Date(almacenaje.fecha_creacion_pedido).toLocaleDateString('es-ES')
            : 'N/A';
        
        const fechaEntregaPedido = almacenaje.fecha_entrega_pedido 
            ? new Date(almacenaje.fecha_entrega_pedido).toLocaleDateString('es-ES')
            : 'N/A';
        
        // Función para agregar sección
        const addSection = (title, data) => {
            checkPageBreak(20);
            pdf.setFontSize(14);
            pdf.setFont(undefined, 'bold');
            pdf.setTextColor(0, 0, 0);
            pdf.text(title, margin, yPos);
            yPos += 8;
            
            pdf.setFontSize(10);
            pdf.setFont(undefined, 'normal');
            data.forEach(item => {
                checkPageBreak(6);
                pdf.text(`${item.label}: ${item.value}`, margin + 5, yPos);
                yPos += 5;
            });
            yPos += 3;
        };
        
        // Información del Lote
        addSection('Información del Lote', [
            { label: 'Código de Lote', value: almacenaje.codigo_lote || almacenaje.lote_id || 'N/A' },
            { label: 'Nombre del Lote', value: almacenaje.nombre_lote || 'N/A' },
            { label: 'Cantidad Objetivo', value: parseFloat(almacenaje.cantidad_objetivo || 0).toFixed(2) },
            { label: 'Ubicación', value: almacenaje.ubicacion || 'N/A' },
            { label: 'Condición', value: almacenaje.condicion || 'N/A' },
            { label: 'Cantidad Almacenada', value: parseFloat(almacenaje.cantidad || 0).toFixed(2) },
            { label: 'Fecha de Almacenaje', value: fechaAlmacenaje },
            ...(fechaRetiro ? [{ label: 'Fecha de Retiro', value: fechaRetiro }] : []),
            ...(almacenaje.observaciones ? [{ label: 'Observaciones', value: almacenaje.observaciones }] : [])
        ]);
        
        // Información de Recojo
        addSection('Ubicación de Recojo', [
            { label: 'Dirección', value: almacenaje.direccion_recojo || 'N/A' },
            ...(almacenaje.referencia_recojo ? [{ label: 'Referencia', value: almacenaje.referencia_recojo }] : []),
            ...(almacenaje.latitud_recojo && almacenaje.longitud_recojo ? [{
                label: 'Coordenadas',
                value: `${parseFloat(almacenaje.latitud_recojo).toFixed(6)}, ${parseFloat(almacenaje.longitud_recojo).toFixed(6)}`
            }] : [])
        ]);
        
        // Información del Pedido
        addSection('Información del Pedido', [
            { label: 'Número de Pedido', value: almacenaje.numero_pedido || 'N/A' },
            { label: 'Nombre del Pedido', value: almacenaje.nombre_pedido || 'N/A' },
            { label: 'Estado', value: almacenaje.estado_pedido || 'N/A' },
            { label: 'Fecha de Creación', value: fechaCreacionPedido },
            { label: 'Fecha de Entrega', value: fechaEntregaPedido },
            ...(almacenaje.descripcion_pedido ? [{ label: 'Descripción', value: almacenaje.descripcion_pedido }] : []),
            ...(almacenaje.observaciones_pedido ? [{ label: 'Observaciones', value: almacenaje.observaciones_pedido }] : [])
        ]);
        
        // Información del Cliente
        addSection('Información del Cliente', [
            { label: 'Razón Social', value: almacenaje.cliente_razon_social || 'N/A' },
            { label: 'Nombre Comercial', value: almacenaje.cliente_nombre_comercial || 'N/A' },
            ...(almacenaje.cliente_nit ? [{ label: 'NIT', value: almacenaje.cliente_nit }] : []),
            { label: 'Contacto', value: almacenaje.cliente_contacto || 'N/A' },
            ...(almacenaje.cliente_direccion ? [{ label: 'Dirección', value: almacenaje.cliente_direccion }] : []),
            ...(almacenaje.cliente_telefono ? [{ label: 'Teléfono', value: almacenaje.cliente_telefono }] : []),
            ...(almacenaje.cliente_email ? [{ label: 'Email', value: almacenaje.cliente_email }] : [])
        ]);
        
        // Productos del Pedido
        if (almacenaje.productos && almacenaje.productos.length > 0) {
            checkPageBreak(25);
            pdf.setFontSize(14);
            pdf.setFont(undefined, 'bold');
            pdf.text('Productos del Pedido', margin, yPos);
            yPos += 8;
            
            // Encabezados de tabla
            pdf.setFontSize(9);
            pdf.setFont(undefined, 'bold');
            const colWidths = [60, 30, 25, 25, 25, 20];
            const headers = ['Producto', 'Código', 'Cantidad', 'Unidad', 'Precio', 'Estado'];
            let xPos = margin;
            headers.forEach((header, i) => {
                pdf.text(header, xPos, yPos);
                xPos += colWidths[i];
            });
            yPos += 5;
            
            // Línea bajo encabezados
            pdf.line(margin, yPos, pageWidth - margin, yPos);
            yPos += 3;
            
            // Datos de productos
            pdf.setFont(undefined, 'normal');
            almacenaje.productos.forEach(prod => {
                checkPageBreak(8);
                xPos = margin;
                const rowData = [
                    (prod.nombre || 'N/A').substring(0, 25),
                    (prod.codigo || '-').substring(0, 10),
                    parseFloat(prod.cantidad || 0).toFixed(2),
                    (prod.unidad || 'N/A').substring(0, 8),
                    parseFloat(prod.precio || 0).toFixed(2),
                    (prod.estado || 'N/A').substring(0, 8)
                ];
                rowData.forEach((data, i) => {
                    pdf.text(String(data), xPos, yPos);
                    xPos += colWidths[i];
                });
                yPos += 5;
            });
            yPos += 5;
        }
        
        // Destinos del Pedido
        if (almacenaje.destinos && almacenaje.destinos.length > 0) {
            checkPageBreak(25);
            pdf.setFontSize(14);
            pdf.setFont(undefined, 'bold');
            pdf.text('Destinos del Pedido', margin, yPos);
            yPos += 8;
            
            almacenaje.destinos.forEach((dest, index) => {
                checkPageBreak(30);
                pdf.setFontSize(11);
                pdf.setFont(undefined, 'bold');
                pdf.text(`Destino ${index + 1}`, margin, yPos);
                yPos += 6;
                
                pdf.setFontSize(9);
                pdf.setFont(undefined, 'normal');
                pdf.text(`Dirección: ${dest.direccion || 'N/A'}`, margin + 5, yPos);
                yPos += 5;
                if (dest.referencia) {
                    pdf.text(`Referencia: ${dest.referencia}`, margin + 5, yPos);
                    yPos += 5;
                }
                if (dest.nombre_contacto) {
                    pdf.text(`Contacto: ${dest.nombre_contacto}`, margin + 5, yPos);
                    yPos += 5;
                }
                if (dest.telefono_contacto) {
                    pdf.text(`Teléfono: ${dest.telefono_contacto}`, margin + 5, yPos);
                    yPos += 5;
                }
                if (dest.instrucciones_entrega) {
                    pdf.text(`Instrucciones: ${dest.instrucciones_entrega}`, margin + 5, yPos);
                    yPos += 5;
                }
                yPos += 3;
            });
        }
        
        // Envíos
        if (almacenaje.envios && almacenaje.envios.length > 0) {
            checkPageBreak(20);
            pdf.setFontSize(14);
            pdf.setFont(undefined, 'bold');
            pdf.text('Envíos Creados', margin, yPos);
            yPos += 8;
            
            almacenaje.envios.forEach(envio => {
                checkPageBreak(8);
                pdf.setFontSize(9);
                pdf.setFont(undefined, 'normal');
                pdf.text(`Código: ${envio.codigo_envio || 'N/A'} | ID: ${envio.envio_id || 'N/A'} | Destino ID: ${envio.destino_id || 'N/A'}`, margin + 5, yPos);
                yPos += 5;
            });
        }
        
        // Guardar PDF
        const fileName = `almacenaje-lote-${almacenaje.codigo_lote || almacenaje.lote_id || 'N/A'}-${new Date().getTime()}.pdf`;
        pdf.save(fileName);
    } catch (error) {
        console.error('Error al generar PDF:', error);
        alert('Error al generar el PDF. Por favor, intente nuevamente.');
    }
}

// Manejar envío del formulario de almacenaje
$(document).ready(function() {
    $('#registrarAlmacenajeForm').on('submit', function(e) {
        const $form = $(this);
        const $submitBtn = $('#submitBtn');
        const $cancelBtn = $('#cancelBtn');
        const $btnText = $submitBtn.find('.btn-text');
        const $btnSpinner = $submitBtn.find('.btn-spinner');
        
        // Los valores de ubicación siempre están presentes (vienen de la configuración de la planta)
        // No se requiere validación ya que el mapa es solo de visualización
        
        // Mostrar spinner y deshabilitar botones
        $btnText.hide();
        $btnSpinner.show();
        $submitBtn.prop('disabled', true).addClass('disabled');
        $cancelBtn.prop('disabled', true).addClass('disabled');
        
        // El formulario se enviará normalmente
        // Si hay un error, se restaurará el estado en el callback de error
        // Si es exitoso, Laravel redirigirá y recargará la página automáticamente
    });
    
    // Restaurar estado del botón si hay errores de validación (cuando el modal se vuelve a abrir)
    $('#registrarAlmacenajeModal').on('show.bs.modal', function() {
        const $submitBtn = $('#submitBtn');
        const $cancelBtn = $('#cancelBtn');
        const $btnText = $submitBtn.find('.btn-text');
        const $btnSpinner = $submitBtn.find('.btn-spinner');
        
        // Restaurar estado inicial
        $btnText.show();
        $btnSpinner.hide();
        $submitBtn.prop('disabled', false).removeClass('disabled');
        $cancelBtn.prop('disabled', false).removeClass('disabled');
    });
});

// Variables globales para evitar múltiples envíos
let procesandoAprobacionAlmacenaje = false;
let procesandoRechazoAlmacenaje = false;
let urlAprobarActual = null;
let urlRechazarActual = null;
let envioIdActual = null;

// Funciones para abrir modales desde almacenaje
function abrirModalAprobarAlmacenaje(url, envioId) {
    if (procesandoAprobacionAlmacenaje) {
        return; // Ya hay una solicitud en proceso
    }
    urlAprobarActual = url;
    envioIdActual = envioId;
    $('#envioIdAprobarAlmacenaje').text('#' + envioId);
    $('#aprobarPropuestaAlmacenajeModal').modal('show');
}

function abrirModalRechazarAlmacenaje(url, envioId) {
    if (procesandoRechazoAlmacenaje) {
        return; // Ya hay una solicitud en proceso
    }
    urlRechazarActual = url;
    envioIdActual = envioId;
    $('#envioIdRechazarAlmacenaje').text('#' + envioId);
    $('#rechazarPropuestaAlmacenajeModal').modal('show');
}

// Manejar aprobación desde almacenaje
$(document).ready(function() {
    $('#aprobarPropuestaAlmacenajeForm').on('submit', function(e) {
        e.preventDefault();
        
        if (procesandoAprobacionAlmacenaje || !urlAprobarActual) {
            return false;
        }
        
        procesandoAprobacionAlmacenaje = true;
        const observaciones = $('#aprobarObservacionesAlmacenaje').val();
        const btnSubmit = $(this).find('button[type="submit"]');
        const btnCancel = $(this).closest('.modal').find('button[data-dismiss="modal"]');
        const originalText = btnSubmit.html();
        
        // Deshabilitar todos los botones del modal
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        btnCancel.prop('disabled', true);
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        $.ajax({
            url: urlAprobarActual,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                accion: 'aprobar',
                observaciones: observaciones || null
            }),
            success: function(response) {
                if (response.success) {
                    // Cerrar modal de aprobación
                    $('#aprobarPropuestaAlmacenajeModal').modal('hide');
                    
                    // Deshabilitar botones de aprobar/rechazar en la fila correspondiente
                    $('button[onclick*="abrirModalAprobarAlmacenaje(\'' + urlAprobarActual + '\'"]').prop('disabled', true).addClass('disabled');
                    $('button[onclick*="abrirModalRechazarAlmacenaje(\'' + urlRechazarActual + '\'"]').prop('disabled', true).addClass('disabled');
                    
                    // Mostrar modal de éxito
                    $('#mensajeExitoAlmacenajeModal .modal-body p').text(response.message);
                    $('#mensajeExitoAlmacenajeModal').modal('show');
                    
                    // Recargar después de 1.5 segundos
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    procesandoAprobacionAlmacenaje = false;
                    btnSubmit.prop('disabled', false).html(originalText);
                    btnCancel.prop('disabled', false);
                    
                    // Mostrar modal de error
                    $('#mensajeErrorAlmacenajeModal .modal-body p').text(response.message || 'Error desconocido');
                    $('#mensajeErrorAlmacenajeModal').modal('show');
                }
            },
            error: function(xhr) {
                procesandoAprobacionAlmacenaje = false;
                btnSubmit.prop('disabled', false).html(originalText);
                btnCancel.prop('disabled', false);
                
                const error = xhr.responseJSON?.message || 'Error al procesar la solicitud';
                
                // Mostrar modal de error
                $('#mensajeErrorAlmacenajeModal .modal-body p').text(error);
                $('#mensajeErrorAlmacenajeModal').modal('show');
            }
        });
    });
    
    // Manejar rechazo desde almacenaje
    $('#rechazarPropuestaAlmacenajeForm').on('submit', function(e) {
        e.preventDefault();
        
        if (procesandoRechazoAlmacenaje || !urlRechazarActual) {
            return false;
        }
        
        const observaciones = $('#rechazarObservacionesAlmacenaje').val();
        
        if (!observaciones || observaciones.trim() === '') {
            $('#mensajeAdvertenciaAlmacenajeModal .modal-body p').text('Por favor, ingrese las observaciones del rechazo.');
            $('#mensajeAdvertenciaAlmacenajeModal').modal('show');
            return;
        }
        
        procesandoRechazoAlmacenaje = true;
        const btnSubmit = $(this).find('button[type="submit"]');
        const btnCancel = $(this).closest('.modal').find('button[data-dismiss="modal"]');
        const originalText = btnSubmit.html();
        
        // Deshabilitar todos los botones del modal
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        btnCancel.prop('disabled', true);
        
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        $.ajax({
            url: urlRechazarActual,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                accion: 'rechazar',
                observaciones: observaciones
            }),
            success: function(response) {
                if (response.success) {
                    // Cerrar modal de rechazo
                    $('#rechazarPropuestaAlmacenajeModal').modal('hide');
                    
                    // Deshabilitar botones de aprobar/rechazar en la fila correspondiente
                    $('button[onclick*="abrirModalAprobarAlmacenaje(\'' + urlAprobarActual + '\'"]').prop('disabled', true).addClass('disabled');
                    $('button[onclick*="abrirModalRechazarAlmacenaje(\'' + urlRechazarActual + '\'"]').prop('disabled', true).addClass('disabled');
                    
                    // Mostrar modal de éxito
                    $('#mensajeExitoAlmacenajeModal .modal-body p').text(response.message);
                    $('#mensajeExitoAlmacenajeModal').modal('show');
                    
                    // Recargar después de 1.5 segundos
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    procesandoRechazoAlmacenaje = false;
                    btnSubmit.prop('disabled', false).html(originalText);
                    btnCancel.prop('disabled', false);
                    
                    // Mostrar modal de error
                    $('#mensajeErrorAlmacenajeModal .modal-body p').text(response.message || 'Error desconocido');
                    $('#mensajeErrorAlmacenajeModal').modal('show');
                }
            },
            error: function(xhr) {
                procesandoRechazoAlmacenaje = false;
                btnSubmit.prop('disabled', false).html(originalText);
                btnCancel.prop('disabled', false);
                
                const error = xhr.responseJSON?.message || 'Error al procesar la solicitud';
                
                // Mostrar modal de error
                $('#mensajeErrorAlmacenajeModal .modal-body p').text(error);
                $('#mensajeErrorAlmacenajeModal').modal('show');
            }
        });
    });
    
    // Resetear flags cuando se cierran los modales
    $('#aprobarPropuestaAlmacenajeModal').on('hidden.bs.modal', function() {
        procesandoAprobacionAlmacenaje = false;
        $('#aprobarPropuestaAlmacenajeForm')[0].reset();
        $(this).find('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-check"></i> Aprobar Propuesta');
        $(this).find('button[data-dismiss="modal"]').prop('disabled', false);
    });
    
    $('#rechazarPropuestaAlmacenajeModal').on('hidden.bs.modal', function() {
        procesandoRechazoAlmacenaje = false;
        $('#rechazarPropuestaAlmacenajeForm')[0].reset();
        $(this).find('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-times"></i> Rechazar Propuesta');
        $(this).find('button[data-dismiss="modal"]').prop('disabled', false);
    });
});
</script>
@endpush