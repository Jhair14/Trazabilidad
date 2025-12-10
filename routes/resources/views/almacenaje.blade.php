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
        height: 100%;
        width: 100%;
        position: relative !important;
        z-index: 1;
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
                                            <button class="btn btn-info btn-sm" title="Ver Detalles" onclick="verAlmacenaje({{ $lote->lote_id }})">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
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
                    
                    <div class="form-group">
                        <label for="pickup_address">Dirección de Recojo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('pickup_address') is-invalid @enderror" 
                               id="pickup_address" name="pickup_address" value="{{ old('pickup_address') }}" 
                               placeholder="Ej: Av. Ejemplo 123, Santa Cruz, Bolivia" required>
                        @error('pickup_address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Dirección completa donde se recogerá el producto</small>
                    </div>

                    <div class="form-group">
                        <label for="pickup_reference">Referencia (Opcional)</label>
                        <input type="text" class="form-control @error('pickup_reference') is-invalid @enderror" 
                               id="pickup_reference" name="pickup_reference" value="{{ old('pickup_reference') }}" 
                               placeholder="Ej: Frente al parque, Edificio azul">
                        @error('pickup_reference')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Seleccionar Ubicación en el Mapa <span class="text-danger">*</span></label>
                        <div style="position: relative; overflow: hidden; border: 1px solid #ddd; border-radius: 4px; height: 400px; width: 100%;">
                            <div id="map" style="height: 100%; width: 100%; position: relative;"></div>
                        </div>
                        <small class="form-text text-muted mt-2 d-block">Haz clic en el mapa para seleccionar la ubicación exacta de recojo</small>
                        <input type="hidden" id="pickup_latitude" name="pickup_latitude" value="{{ old('pickup_latitude') }}" required>
                        <input type="hidden" id="pickup_longitude" name="pickup_longitude" value="{{ old('pickup_longitude') }}" required>
                        @error('pickup_latitude')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                        @error('pickup_longitude')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Importante:</strong> La cantidad a almacenar se toma automáticamente del lote (cantidad producida u objetivo). Solo se puede almacenar una vez. Al almacenar, se creará automáticamente el envío en PlantaCruds con la ubicación de recojo seleccionada en el mapa.
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Almacenaje</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
    $('#pickup_address').val('');
    $('#pickup_reference').val('');
    $('#pickup_latitude').val('');
    $('#pickup_longitude').val('');
    
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
    
    // Verificar que el elemento tenga dimensiones visibles y correctas (400px)
    if (mapElement.offsetWidth === 0 || mapElement.offsetHeight === 0) {
        setTimeout(() => initMap(), 100);
        return;
    }
    
    // Si el mapa ya existe, removerlo y crear uno nuevo
    if (map) {
        try {
            map.remove();
        } catch(e) {
            console.log('Error removiendo mapa:', e);
        }
        map = null;
        marker = null;
    }
    
    // Coordenadas por defecto: Santa Cruz, Bolivia
    const defaultLat = -17.8146;
    const defaultLng = -63.1561;
    
    // Crear mapa - NO establecer vista todavía
    map = L.map('map', {
        zoomControl: true,
        attributionControl: true,
        preferCanvas: false
    });
    
    // Agregar capa de OpenStreetMap ANTES de establecer la vista
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Ahora establecer la vista DESPUÉS de agregar el tileLayer
    map.setView([defaultLat, defaultLng], 13);
    
    // Invalidar tamaño INMEDIATAMENTE después de establecer la vista
    map.invalidateSize(true);
    
    // Invalidar tamaño nuevamente después de un breve delay para asegurar que todos los tiles se carguen
    setTimeout(() => {
        if (map) {
            map.invalidateSize(true);
            // Forzar actualización de la vista para cargar todos los tiles
            map.setView(map.getCenter(), map.getZoom());
        }
    }, 100);
    
    // Si hay coordenadas guardadas, usarlas
    const savedLat = $('#pickup_latitude').val();
    const savedLng = $('#pickup_longitude').val();
    
    if (savedLat && savedLng) {
        setTimeout(() => {
            if (map) {
                map.setView([parseFloat(savedLat), parseFloat(savedLng)], 15);
                addMarker(parseFloat(savedLat), parseFloat(savedLng));
            }
        }, 200);
    }
    
    // Agregar marcador al hacer clic
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        addMarker(lat, lng);
        $('#pickup_latitude').val(lat);
        $('#pickup_longitude').val(lng);
        
        // Intentar obtener dirección usando geocodificación inversa
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
            headers: {
                'User-Agent': 'TrazabilidadApp/1.0'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.address) {
                    const addressParts = [];
                    if (data.address.road) addressParts.push(data.address.road);
                    if (data.address.house_number) addressParts.push(data.address.house_number);
                    if (data.address.suburb) addressParts.push(data.address.suburb);
                    if (data.address.city || data.address.town) addressParts.push(data.address.city || data.address.town);
                    if (data.address.state) addressParts.push(data.address.state);
                    if (data.address.country) addressParts.push(data.address.country);
                    
                    if (addressParts.length > 0) {
                        $('#pickup_address').val(addressParts.join(', '));
                    }
                }
            })
            .catch(err => console.log('Error obteniendo dirección:', err));
    });
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
    
    // Crear nuevo marcador con icono por defecto
    marker = L.marker([lat, lng], {
        draggable: false
    }).addTo(map);
    
    // Asegurar que el marcador sea visible
    map.setView([lat, lng], map.getZoom());
}

// Inicializar mapa cuando se abre el modal completamente
$('#registrarAlmacenajeModal').on('shown.bs.modal', function () {
    // Limpiar marcador anterior
    if (marker && map) {
        map.removeLayer(marker);
        marker = null;
    }
    
    // Esperar a que el modal esté completamente renderizado antes de inicializar el mapa
    setTimeout(() => {
        initMap();
    }, 300);
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
    // Limpiar coordenadas
    $('#pickup_latitude').val('');
    $('#pickup_longitude').val('');
    $('#pickup_address').val('');
    $('#pickup_reference').val('');
});

function verAlmacenaje(batchId) {
    fetch(`{{ url('almacenaje') }}/lote/${batchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                const almacenaje = data[0];
                
                // Formatear fecha
                const fechaAlmacenaje = almacenaje.fecha_almacenaje 
                    ? new Date(almacenaje.fecha_almacenaje).toLocaleString('es-ES', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    })
                    : 'N/A';
                
                const fechaRetiro = almacenaje.fecha_retiro 
                    ? new Date(almacenaje.fecha_retiro).toLocaleString('es-ES', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    })
                    : null;
                
                // Información de envíos
                let enviosHtml = '';
                if (almacenaje.envios && almacenaje.envios.length > 0) {
                    enviosHtml = '<div class="mt-3"><h5>Envíos Creados en PlantaCruds</h5><ul class="list-group">';
                    almacenaje.envios.forEach(function(envio) {
                        enviosHtml += `<li class="list-group-item">
                            <strong>Código:</strong> ${envio.codigo_envio || 'N/A'}<br>
                            <strong>ID Envío:</strong> ${envio.envio_id || 'N/A'}<br>
                            <strong>Estado:</strong> <span class="badge badge-success">Creado</span>
                        </li>`;
                    });
                    enviosHtml += '</ul></div>';
                }
                
                const content = `
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-3">Información del Almacenaje</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%;">Lote</th>
                                    <td>#${almacenaje.codigo_lote || almacenaje.lote_id || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Nombre del Lote</th>
                                    <td>${almacenaje.nombre_lote || 'Sin nombre'}</td>
                                </tr>
                                <tr>
                                    <th>Ubicación</th>
                                    <td>${almacenaje.ubicacion || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Condición</th>
                                    <td>${almacenaje.condicion || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Cantidad Almacenada</th>
                                    <td><strong>${parseFloat(almacenaje.cantidad || 0).toFixed(2)}</strong></td>
                                </tr>
                                <tr>
                                    <th>Fecha de Almacenaje</th>
                                    <td>${fechaAlmacenaje}</td>
                                </tr>
                                ${fechaRetiro ? `
                                <tr>
                                    <th>Fecha de Retiro</th>
                                    <td>${fechaRetiro}</td>
                                </tr>
                                ` : ''}
                                ${almacenaje.observaciones ? `
                                <tr>
                                    <th>Observaciones</th>
                                    <td>${almacenaje.observaciones}</td>
                                </tr>
                                ` : ''}
                            </table>
                            
                            <h5 class="mt-4 mb-3">Información del Pedido</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%;">Número de Pedido</th>
                                    <td>#${almacenaje.numero_pedido || almacenaje.pedido_id || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Nombre del Pedido</th>
                                    <td>${almacenaje.nombre_pedido || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Cliente</th>
                                    <td>${almacenaje.cliente || 'N/A'}</td>
                                </tr>
                            </table>
                            
                            ${almacenaje.productos && almacenaje.productos.length > 0 ? `
                            <h5 class="mt-4 mb-3">Productos del Pedido</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 20%;">Código</th>
                                        <th style="width: 50%;">Producto</th>
                                        <th style="width: 25%;">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${almacenaje.productos.map((producto, index) => `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${producto.codigo || 'N/A'}</td>
                                        <td>${producto.nombre || 'N/A'}</td>
                                        <td><strong>${parseFloat(producto.cantidad || 0).toFixed(2)} ${producto.unidad || ''}</strong></td>
                                    </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                            ` : ''}
                            
                            <h5 class="mt-4 mb-3">Información de Transporte (Ubicación de Recojo)</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%;">Dirección de Recojo</th>
                                    <td>${almacenaje.direccion_recojo || 'N/A'}</td>
                                </tr>
                                ${almacenaje.referencia_recojo ? `
                                <tr>
                                    <th>Referencia</th>
                                    <td>${almacenaje.referencia_recojo}</td>
                                </tr>
                                ` : ''}
                                ${almacenaje.latitud_recojo && almacenaje.longitud_recojo ? `
                                <tr>
                                    <th>Coordenadas</th>
                                    <td>${almacenaje.latitud_recojo}, ${almacenaje.longitud_recojo}</td>
                                </tr>
                                ` : ''}
                            </table>
                            
                            ${enviosHtml}
                        </div>
                    </div>
                `;
                
                // Crear o actualizar modal de detalles
                if ($('#verAlmacenajeModal').length === 0) {
                    $('body').append(`
                        <div class="modal fade" id="verAlmacenajeModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
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
                                    <div class="modal-body" id="verAlmacenajeContent">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" id="btnDescargarPDF" onclick="descargarPDF()">
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
                
                // Guardar datos del almacenaje para el PDF
                window.almacenajeData = almacenaje;
            } else {
                alert('No se encontró información de almacenaje para este lote');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles del almacenaje');
        });
}

function descargarPDF() {
    if (!window.almacenajeData) {
        alert('No hay datos disponibles para generar el PDF');
        return;
    }
    
    const almacenaje = window.almacenajeData;
    
    // Formatear fecha
    const fechaAlmacenaje = almacenaje.fecha_almacenaje 
        ? new Date(almacenaje.fecha_almacenaje).toLocaleString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        })
        : 'N/A';
    
    const fechaRetiro = almacenaje.fecha_retiro 
        ? new Date(almacenaje.fecha_retiro).toLocaleString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        })
        : null;
    
    // Información de envíos
    let enviosHtml = '';
    if (almacenaje.envios && almacenaje.envios.length > 0) {
        enviosHtml = '<div style="margin-top: 20px;"><h4 style="color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px;">Envíos Creados en PlantaCruds</h4><ul style="list-style: none; padding: 0;">';
        almacenaje.envios.forEach(function(envio) {
            enviosHtml += `<li style="background: #f8f9fa; padding: 10px; margin-bottom: 10px; border-left: 4px solid #007bff;">
                <strong>Código:</strong> ${envio.codigo_envio || 'N/A'}<br>
                <strong>ID Envío:</strong> ${envio.envio_id || 'N/A'}
            </li>`;
        });
        enviosHtml += '</ul></div>';
    }
    
    // Crear contenido HTML para el PDF
    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    color: #333;
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #007bff;
                    padding-bottom: 15px;
                    margin-bottom: 30px;
                }
                .header h1 {
                    color: #007bff;
                    margin: 0;
                    font-size: 24px;
                }
                .header p {
                    margin: 5px 0;
                    color: #666;
                }
                .section {
                    margin-bottom: 25px;
                }
                .section h3 {
                    color: #007bff;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 5px;
                    margin-bottom: 15px;
                    font-size: 18px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table th, table td {
                    padding: 10px;
                    text-align: left;
                    border: 1px solid #ddd;
                }
                table th {
                    background-color: #007bff;
                    color: white;
                    font-weight: bold;
                    width: 30%;
                }
                table td {
                    background-color: #f8f9fa;
                }
                .footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 2px solid #ddd;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Detalles del Almacenaje</h1>
                <p>Sistema de Trazabilidad y Producción</p>
                <p>Fecha de generación: ${new Date().toLocaleString('es-ES')}</p>
            </div>
            
            <div class="section">
                <h3>Información del Almacenaje</h3>
                <table>
                    <tr>
                        <th>Lote</th>
                        <td>#${almacenaje.codigo_lote || almacenaje.lote_id || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Nombre del Lote</th>
                        <td>${almacenaje.nombre_lote || 'Sin nombre'}</td>
                    </tr>
                    <tr>
                        <th>Ubicación</th>
                        <td>${almacenaje.ubicacion || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Condición</th>
                        <td>${almacenaje.condicion || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Cantidad Almacenada</th>
                        <td><strong>${parseFloat(almacenaje.cantidad || 0).toFixed(2)}</strong></td>
                    </tr>
                    <tr>
                        <th>Fecha de Almacenaje</th>
                        <td>${fechaAlmacenaje}</td>
                    </tr>
                    ${fechaRetiro ? `
                    <tr>
                        <th>Fecha de Retiro</th>
                        <td>${fechaRetiro}</td>
                    </tr>
                    ` : ''}
                    ${almacenaje.observaciones ? `
                    <tr>
                        <th>Observaciones</th>
                        <td>${almacenaje.observaciones}</td>
                    </tr>
                    ` : ''}
                </table>
            </div>
            
            <div class="section">
                <h3>Información del Pedido</h3>
                <table>
                    <tr>
                        <th>Número de Pedido</th>
                        <td>#${almacenaje.numero_pedido || almacenaje.pedido_id || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Nombre del Pedido</th>
                        <td>${almacenaje.nombre_pedido || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Cliente</th>
                        <td>${almacenaje.cliente || 'N/A'}</td>
                    </tr>
                </table>
            </div>
            
            ${almacenaje.productos && almacenaje.productos.length > 0 ? `
            <div class="section">
                <h3>Productos del Pedido</h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">#</th>
                            <th style="width: 30%;">Código</th>
                            <th style="width: 40%;">Producto</th>
                            <th style="width: 20%;">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${almacenaje.productos.map((producto, index) => `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${producto.codigo || 'N/A'}</td>
                            <td>${producto.nombre || 'N/A'}</td>
                            <td><strong>${parseFloat(producto.cantidad || 0).toFixed(2)} ${producto.unidad || ''}</strong></td>
                        </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            ` : ''}
            
            <div class="section">
                <h3>Información de Transporte (Ubicación de Recojo)</h3>
                <table>
                    <tr>
                        <th>Dirección de Recojo</th>
                        <td>${almacenaje.direccion_recojo || 'N/A'}</td>
                    </tr>
                    ${almacenaje.referencia_recojo ? `
                    <tr>
                        <th>Referencia</th>
                        <td>${almacenaje.referencia_recojo}</td>
                    </tr>
                    ` : ''}
                    ${almacenaje.latitud_recojo && almacenaje.longitud_recojo ? `
                    <tr>
                        <th>Coordenadas</th>
                        <td>${almacenaje.latitud_recojo}, ${almacenaje.longitud_recojo}</td>
                    </tr>
                    ` : ''}
                </table>
            </div>
            
            ${enviosHtml}
            
            <div class="footer">
                <p>Documento generado automáticamente por el Sistema de Trazabilidad y Producción</p>
            </div>
        </body>
        </html>
    `;
    
    // Crear un elemento temporal para el contenido
    const element = document.createElement('div');
    element.innerHTML = htmlContent;
    
    // Configuración para html2pdf
    const opt = {
        margin: [10, 10, 10, 10],
        filename: `almacenaje_lote_${almacenaje.codigo_lote || almacenaje.lote_id}_${new Date().toISOString().split('T')[0]}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    
    // Generar y descargar el PDF
    html2pdf().set(opt).from(element).save();
}
</script>
@endpush
