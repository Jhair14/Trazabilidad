@extends('layouts.app')

@section('page_title', 'Gestión de Almacenaje')

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
                                <h3>{{ $lotes->count() }}</h3>
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
                                <h3>{{ $lotes->where('latestFinalEvaluation', '!=', null)->filter(function($l) { 
                                    $eval = $l->latestFinalEvaluation;
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
                                <h3>{{ $lotes->where('latestFinalEvaluation', null)->count() }}</h3>
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
                                <h3>{{ $lotes->filter(function($l) { return $l->storage->isNotEmpty(); })->count() }}</h3>
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
                                <th>Fecha Creación</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotes as $lote)
                            @php
                                $eval = $lote->latestFinalEvaluation;
                                $esCertificado = $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló');
                            @endphp
                            <tr>
                                <td>#{{ $lote->batch_code ?? $lote->batch_id }}</td>
                                <td>{{ $lote->name ?? 'Sin nombre' }}</td>
                                <td>{{ $lote->order->customer->business_name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($lote->creation_date)->format('d/m/Y') }}</td>
                                <td>
                                    @if($esCertificado)
                                        <span class="badge badge-success">Certificado</span>
                                    @else
                                        <span class="badge badge-warning">Sin Certificar</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($esCertificado)
                                    <button class="btn btn-primary btn-sm" title="Almacenar" onclick="almacenarLote({{ $lote->batch_id }}, '{{ $lote->batch_code ?? $lote->batch_id }}', '{{ $lote->name ?? 'Sin nombre' }}')">
                                        <i class="fas fa-warehouse"></i> Almacenar
                                    </button>
                                    @if($lote->storage->isNotEmpty())
                                        <span class="badge badge-info ml-2" title="Ya tiene {{ $lote->storage->count() }} almacenaje(s)">
                                            <i class="fas fa-check"></i> {{ $lote->storage->count() }} almacenaje(s)
                                        </span>
                                    @endif
                                    @else
                                    <span class="text-muted">Requiere certificación</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay lotes certificados disponibles para almacenar</td>
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
                    <input type="hidden" id="batch_id" name="batch_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p><strong>Lote:</strong> <span id="modal_batch_name"></span></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Ubicación <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" value="{{ old('location') }}" 
                                       placeholder="Ej: Zona A, Estante 1, Nivel 2" required>
                                @error('location')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Ejemplos: A-01, B-02, Depósito Central</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="condition">Condición <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('condition') is-invalid @enderror" 
                                       id="condition" name="condition" value="{{ old('condition') }}" 
                                       placeholder="Ej: Buen estado, Seco y ventilado" required>
                                @error('condition')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Estado físico del producto</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Cantidad <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" value="{{ old('quantity') }}" 
                               placeholder="0" step="0.01" min="0" required>
                        @error('quantity')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="observations">Observaciones</label>
                        <textarea class="form-control" id="observations" name="observations" 
                                  rows="3" placeholder="Observaciones sobre el almacenaje...">{{ old('observations') }}</textarea>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Almacenajes previos</strong>
                        <div id="almacenajes_previos" class="mt-2">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin"></i> Cargando...
                            </div>
                        </div>
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
<script>
let currentBatchId = null;

function almacenarLote(batchId, batchCode, batchName) {
    currentBatchId = batchId;
    $('#batch_id').val(batchId);
    $('#modal_batch_code').text(batchCode);
    $('#modal_batch_name').text(batchName);
    $('#location').val('');
    $('#condition').val('');
    $('#quantity').val('');
    $('#observations').val('');
    
    // Cargar almacenajes previos
    cargarAlmacenajesPrevios(batchId);
    
    $('#registrarAlmacenajeModal').modal('show');
}

function cargarAlmacenajesPrevios(batchId) {
    $('#almacenajes_previos').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
    
    fetch(`{{ url('almacenaje/lote') }}/${batchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                $('#almacenajes_previos').html('<p class="mb-0 text-muted">No hay registros previos.</p>');
            } else {
                let html = '<ul class="mb-0">';
                data.forEach(function(almacen) {
                    const fecha = new Date(almacen.storage_date).toLocaleDateString('es-ES', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    html += `<li><strong>${fecha}</strong> - ${almacen.location} (${almacen.condition}) - Cantidad: ${almacen.quantity}</li>`;
                });
                html += '</ul>';
                $('#almacenajes_previos').html(html);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#almacenajes_previos').html('<p class="mb-0 text-danger">Error al cargar almacenajes previos.</p>');
        });
}

// Limpiar modal al cerrar
$('#registrarAlmacenajeModal').on('hidden.bs.modal', function () {
    currentBatchId = null;
    $('#registrarAlmacenajeForm')[0].reset();
    $('#almacenajes_previos').html('');
});
</script>
@endpush
