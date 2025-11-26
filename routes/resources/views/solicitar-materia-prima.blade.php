@extends('layouts.app')

@section('page_title', 'Solicitar Materia Prima')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Solicitar Materia Prima
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearSolicitudModal">
                        <i class="fas fa-plus"></i> Nueva Solicitud
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
                                <h3>{{ $solicitudes->total() }}</h3>
                                <p>Total Solicitudes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $solicitudes->where('priority', '>', 0)->count() }}</h3>
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
                                <h3>{{ $solicitudes->where('priority', 0)->count() }}</h3>
                                <p>Completadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $solicitudes->where('priority', '>', 5)->count() }}</h3>
                                <p>Urgentes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobada">Aprobada</option>
                            <option value="rechazada">Rechazada</option>
                            <option value="entregada">Entregada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filtroFecha">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por solicitante..." id="buscarSolicitante">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Solicitudes -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Solicitante</th>
                                <th>Materia Prima</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Fecha Solicitud</th>
                                <th>Fecha Entrega</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudes as $solicitud)
                            <tr>
                                <td>#{{ $solicitud->request_number ?? $solicitud->request_id }}</td>
                                <td>{{ $solicitud->order->customer->business_name ?? 'N/A' }}</td>
                                <td>
                                    @foreach($solicitud->details as $detail)
                                        {{ $detail->material->name ?? 'N/A' }}<br>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($solicitud->details as $detail)
                                        {{ number_format($detail->requested_quantity, 2) }} {{ $detail->material->unit->code ?? '' }}<br>
                                    @endforeach
                                </td>
                                <td>
                                    @if($solicitud->priority > 0)
                                        <span class="badge badge-warning">Pendiente</span>
                                    @else
                                        <span class="badge badge-success">Completada</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($solicitud->request_date)->format('Y-m-d') }}</td>
                                <td>{{ $solicitud->required_date ? \Carbon\Carbon::parse($solicitud->required_date)->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    <button class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay solicitudes registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($solicitudes->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando {{ $solicitudes->firstItem() }} a {{ $solicitudes->lastItem() }} de {{ $solicitudes->total() }} registros
                    </div>
                    <nav>
                        {{ $solicitudes->links() }}
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Solicitud -->
<div class="modal fade" id="crearSolicitudModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nueva Solicitud de Materia Prima</h4>
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
                <form method="POST" action="{{ route('solicitar-materia-prima') }}" id="crearSolicitudForm">
                    @csrf
                    
                    <!-- Pedido Asociado -->
                    <div class="form-group">
                        <label for="order_id">
                            <i class="fas fa-shopping-cart mr-1"></i>
                            Pedido Asociado <span class="text-danger">*</span>
                        </label>
                        <select class="form-control @error('order_id') is-invalid @enderror" 
                                id="order_id" name="order_id" required>
                            <option value="">Seleccionar pedido...</option>
                            @foreach($pedidos as $pedido)
                                <option value="{{ $pedido->order_id }}" {{ old('order_id') == $pedido->order_id ? 'selected' : '' }}>
                                    Pedido #{{ $pedido->order_number ?? $pedido->order_id }} - {{ $pedido->customer->business_name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('order_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Seleccione el pedido al que pertenece esta solicitud</small>
                    </div>
                    
                    <!-- Fecha y Prioridad -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="required_date">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Fecha Requerida <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('required_date') is-invalid @enderror" 
                                       id="required_date" name="required_date" 
                                       value="{{ old('required_date') }}" required>
                                @error('required_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Prioridad
                                </label>
                                <select class="form-control @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority">
                                    <option value="1" {{ old('priority', 1) == 1 ? 'selected' : '' }}>Normal</option>
                                    <option value="5" {{ old('priority') == 5 ? 'selected' : '' }}>Alta</option>
                                    <option value="10" {{ old('priority') == 10 ? 'selected' : '' }}>Urgente</option>
                                </select>
                                @error('priority')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Materias Primas -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-boxes mr-1"></i>
                            Materias Primas <span class="text-danger">*</span>
                        </label>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 60%;">Materia Prima</th>
                                        <th style="width: 30%;">Cantidad</th>
                                        <th style="width: 10%;" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="materialsTable">
                                    <tr>
                                        <td>
                                            <select class="form-control form-control-sm" name="materials[0][material_id]" required>
                                                <option value="">Seleccionar materia prima...</option>
                                                @foreach($materias_primas as $mp)
                                                    <option value="{{ $mp->material_id }}">
                                                        {{ $mp->name }} ({{ $mp->unit->code ?? 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" 
                                                       name="materials[0][requested_quantity]" 
                                                       placeholder="0.00" step="0.01" min="0" required>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterial(this)" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2" onclick="addMaterial()">
                            <i class="fas fa-plus"></i> Agregar Materia Prima
                        </button>
                        <small class="form-text text-muted d-block mt-1">Agregue al menos una materia prima a la solicitud</small>
                    </div>
                    
                    <!-- Observaciones -->
                    <div class="form-group">
                        <label for="observations">
                            <i class="fas fa-comment-alt mr-1"></i>
                            Observaciones
                        </label>
                        <textarea class="form-control @error('observations') is-invalid @enderror" 
                                  id="observations" name="observations" 
                                  rows="3" placeholder="Ingrese observaciones adicionales sobre la solicitud...">{{ old('observations') }}</textarea>
                        @error('observations')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="submit" form="crearSolicitudForm" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Crear Solicitud
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let materialIndex = 1;
const materiasPrimas = @json($materias_primas);

function addMaterial() {
    const table = document.getElementById('materialsTable');
    const row = table.insertRow();
    
    let optionsHtml = '<option value="">Seleccionar materia prima...</option>';
    materiasPrimas.forEach(function(mp) {
        optionsHtml += `<option value="${mp.material_id}">${mp.name} (${mp.unit ? mp.unit.code : 'N/A'})</option>`;
    });
    
    row.innerHTML = `
        <td>
            <select class="form-control form-control-sm" name="materials[${materialIndex}][material_id]" required>
                ${optionsHtml}
            </select>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" class="form-control" 
                       name="materials[${materialIndex}][requested_quantity]" 
                       placeholder="0.00" step="0.01" min="0" required>
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterial(this)" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    materialIndex++;
}

function removeMaterial(button) {
    const row = button.closest('tr');
    const table = document.getElementById('materialsTable');
    if (table.rows.length > 1) {
        row.remove();
        // Reindexar los nombres de los campos
        reindexMaterials();
    } else {
        alert('Debe tener al menos una materia prima en la solicitud');
    }
}

function reindexMaterials() {
    const table = document.getElementById('materialsTable');
    const rows = table.querySelectorAll('tr');
    rows.forEach(function(row, index) {
        const materialSelect = row.querySelector('select[name*="[material_id]"]');
        const quantityInput = row.querySelector('input[name*="[requested_quantity]"]');
        
        if (materialSelect) {
            materialSelect.name = `materials[${index}][material_id]`;
        }
        if (quantityInput) {
            quantityInput.name = `materials[${index}][requested_quantity]`;
        }
    });
    materialIndex = rows.length;
}

function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const fecha = document.getElementById('filtroFecha').value;
    const buscar = document.getElementById('buscarSolicitante').value;
    
    const url = new URL(window.location);
    if (estado) url.searchParams.set('estado', estado);
    if (fecha) url.searchParams.set('fecha', fecha);
    if (buscar) url.searchParams.set('buscar', buscar);
    window.location = url;
}

// Validar formulario antes de enviar
document.getElementById('crearSolicitudForm').addEventListener('submit', function(e) {
    const materialsTable = document.getElementById('materialsTable');
    const rows = materialsTable.querySelectorAll('tr');
    let hasValidMaterial = false;
    
    rows.forEach(function(row) {
        const materialSelect = row.querySelector('select[name*="[material_id]"]');
        const quantityInput = row.querySelector('input[name*="[requested_quantity]"]');
        
        if (materialSelect && materialSelect.value && quantityInput && quantityInput.value > 0) {
            hasValidMaterial = true;
        }
    });
    
    if (!hasValidMaterial) {
        e.preventDefault();
        alert('Por favor, agregue al menos una materia prima con cantidad válida');
        return false;
    }
});
</script>
@endpush

