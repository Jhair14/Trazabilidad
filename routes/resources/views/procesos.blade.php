@extends('layouts.app')

@section('page_title', 'Gestión de Procesos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-project-diagram mr-1"></i>
                    Gestión de Procesos
                </h3>
                <div class="card-tools">
                    <a href="{{ route('procesos.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nuevo Proceso
                    </a>
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
                                <h3>{{ $procesos->total() }}</h3>
                <p>Total Procesos</p>
            </div>
            <div class="icon">
                <i class="fas fa-project-diagram"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                                <h3>{{ $procesos->where('active', true)->count() }}</h3>
                <p>Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-play-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                                <h3>0</h3>
                <p>En Revisión</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                                <h3>{{ $procesos->where('active', false)->count() }}</h3>
                <p>Inactivos</p>
            </div>
            <div class="icon">
                <i class="fas fa-pause-circle"></i>
            </div>
        </div>
    </div>
</div>

                <!-- Tabla de Procesos -->
        <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                                <th>Máquinas</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                            @forelse($procesos as $proceso)
                    <tr>
                                <td>#{{ $proceso->process_id }}</td>
                                <td>{{ $proceso->name }}</td>
                                <td>{{ $proceso->description ?? 'Sin descripción' }}</td>
                                <td>
                                    @if($proceso->processMachines && $proceso->processMachines->count() > 0)
                                        <span class="badge badge-info">{{ $proceso->processMachines->count() }} máquina(s)</span>
                                    @else
                                        <span class="badge badge-secondary">Sin máquinas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($proceso->active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                        </td>
                        <td class="text-right">
                                    <button class="btn btn-sm btn-info" title="Ver" onclick="verProceso({{ $proceso->process_id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                                    <button class="btn btn-sm btn-warning" title="Editar" onclick="editarProceso({{ $proceso->process_id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                                    <form method="POST" action="{{ route('procesos.destroy', $proceso->process_id) }}" 
                                          style="display: inline;" 
                                          onsubmit="return confirm('¿Está seguro de eliminar este proceso?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                                    </form>
                        </td>
                    </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay procesos registrados</td>
                    </tr>
                            @endforelse
                </tbody>
            </table>
                </div>

                <!-- Paginación -->
                @if($procesos->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando {{ $procesos->firstItem() }} a {{ $procesos->lastItem() }} de {{ $procesos->total() }} registros
                    </div>
                    <nav>
                        {{ $procesos->links() }}
                    </nav>
                </div>
                @endif
        </div>
    </div>
    </div>
</div>

<!-- Modal Crear Proceso -->
<div class="modal fade" id="crearProcesoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-project-diagram mr-1"></i>
                    Crear Nuevo Proceso
                </h4>
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
                <form method="POST" action="{{ route('procesos.store') }}" id="crearProcesoForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre del Proceso <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="Ej: Mezclado" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                            <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Descripción detallada del proceso...">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Nota:</strong> Las máquinas y variables del proceso se pueden configurar después de crear el proceso básico.
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Crear Proceso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Proceso -->
<div class="modal fade" id="verProcesoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles del Proceso
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verProcesoContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                            </div>
                        </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
            </div>
                            </div>
                        </div>
                    </div>

<!-- Modal Editar Proceso -->
<div class="modal fade" id="editarProcesoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Proceso
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="" id="editarProcesoForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="edit_name">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre del Proceso <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="edit_name" name="name" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="active" id="edit_active" value="1">
                            Proceso Activo
                        </label>
                    </div>

                    <hr class="my-4">

                    <!-- Selección de máquinas -->
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-cogs mr-1"></i>
                            Máquinas del Proceso
                        </h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Selecciona las máquinas disponibles y luego agrega variables estándar a cada una.
                        </div>

                        <!-- Máquinas disponibles -->
                        <div class="mb-4">
                            <h6 class="mb-2">Máquinas Disponibles:</h6>
                            <div class="row" id="editMaquinasDisponibles">
                                @foreach($maquinas as $maquina)
                                <div class="col-md-3 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            @if($maquina->image_url)
                                            <img src="{{ $maquina->image_url }}" alt="{{ $maquina->name }}" 
                                                 class="img-fluid mb-2" style="max-height: 80px; object-fit: contain;">
                                            @else
                                            <div class="bg-light p-2 mb-2" style="height: 80px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-cog fa-2x text-muted"></i>
                                            </div>
                                            @endif
                                            <h6 class="card-title" style="font-size: 0.9rem;">{{ $maquina->name }}</h6>
                                            <button type="button" class="btn btn-sm btn-primary edit-agregar-maquina" 
                                                    data-machine-id="{{ $maquina->machine_id }}"
                                                    data-machine-name="{{ $maquina->name }}"
                                                    data-machine-image="{{ $maquina->image_url ?? '' }}">
                                                <i class="fas fa-plus mr-1"></i> Agregar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Máquinas seleccionadas -->
                        <div id="editMaquinasSeleccionadas" class="mb-4">
                            <h6 class="mb-3">Máquinas Seleccionadas:</h6>
                            <div id="editListaMaquinas">
                                <p class="text-muted">No hay máquinas seleccionadas. Haz clic en "Agregar" para seleccionar máquinas.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Proceso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function verProceso(id) {
    fetch(`{{ url('procesos') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            let machinesHtml = '';
            if (data.process_machines && data.process_machines.length > 0) {
                data.process_machines.forEach(function(pm) {
                    let variablesHtml = '';
                    if (pm.variables && pm.variables.length > 0) {
                        variablesHtml = '<ul class="mb-0">';
                        pm.variables.forEach(function(v) {
                            variablesHtml += `<li>${v.variable_name} (${v.unit}): ${v.min_value} - ${v.max_value}${v.target_value ? ' (Objetivo: ' + v.target_value + ')' : ''} ${v.mandatory ? '<span class="badge badge-warning">Obligatorio</span>' : ''}</li>`;
                        });
                        variablesHtml += '</ul>';
                    } else {
                        variablesHtml = '<p class="text-muted mb-0">Sin variables</p>';
                    }
                    
                    machinesHtml += `
                        <div class="card mb-2">
                            <div class="card-header">
                                <strong>${pm.name}</strong> - ${pm.machine_name} (Paso ${pm.step_order})
                            </div>
                            <div class="card-body">
                                <p><strong>Descripción:</strong> ${pm.description || 'Sin descripción'}</p>
                                <p><strong>Tiempo Estimado:</strong> ${pm.estimated_time || 'N/A'} minutos</p>
                                <p><strong>Variables:</strong></p>
                                ${variablesHtml}
                            </div>
                        </div>
                    `;
                });
            } else {
                machinesHtml = '<p class="text-muted">No hay máquinas configuradas</p>';
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID</th>
                                <td>#${data.process_id}</td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>${data.name}</td>
                            </tr>
                            <tr>
                                <th>Descripción</th>
                                <td>${data.description || 'Sin descripción'}</td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td>
                                    ${data.active 
                                        ? '<span class="badge badge-success">Activo</span>' 
                                        : '<span class="badge badge-danger">Inactivo</span>'}
                                </td>
                            </tr>
                            <tr>
                                <th>Máquinas del Proceso</th>
                                <td>${machinesHtml}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('verProcesoContent').innerHTML = content;
            $('#verProcesoModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del proceso');
        });
}

let editMaquinasSeleccionadas = [];
let variablesEstandar = @json($variables);

function editarProceso(id) {
    fetch(`{{ url('procesos') }}/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editarProcesoForm').action = `{{ url('procesos') }}/${id}`;
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_active').checked = data.active || false;
            
            // Cargar máquinas existentes
            editMaquinasSeleccionadas = [];
            if (data.process_machines && data.process_machines.length > 0) {
                data.process_machines.forEach(pm => {
                    const maquina = {
                        machine_id: pm.machine_id,
                        name: pm.name,
                        step_order: pm.step_order,
                        description: pm.description || '',
                        estimated_time: pm.estimated_time || '',
                        variables: []
                    };
                    
                    if (pm.variables && pm.variables.length > 0) {
                        pm.variables.forEach(v => {
                            maquina.variables.push({
                                standard_variable_id: v.standard_variable_id,
                                min_value: v.min_value,
                                max_value: v.max_value,
                                target_value: v.target_value || ''
                            });
                        });
                    }
                    
                    editMaquinasSeleccionadas.push(maquina);
                });
            }
            
            editRenderizarMaquinas();
            $('#editarProcesoModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del proceso para editar');
        });
}

// Agregar máquina al proceso (edición)
$(document).on('click', '.edit-agregar-maquina', function() {
    const machineId = $(this).data('machine-id');
    const machineName = $(this).data('machine-name');
    const machineImage = $(this).data('machine-image');
    
    // Verificar si ya está agregada
    if (editMaquinasSeleccionadas.find(m => m.machine_id === machineId)) {
        alert('Esta máquina ya está agregada al proceso');
        return;
    }
    
    const stepOrder = editMaquinasSeleccionadas.length + 1;
    const nuevaMaquina = {
        machine_id: machineId,
        name: machineName,
        image_url: machineImage,
        step_order: stepOrder,
        description: '',
        estimated_time: '',
        variables: []
    };
    
    editMaquinasSeleccionadas.push(nuevaMaquina);
    editRenderizarMaquinas();
});

// Eliminar máquina (edición)
function editEliminarMaquina(index) {
    editMaquinasSeleccionadas.splice(index, 1);
    // Reordenar step_order
    editMaquinasSeleccionadas.forEach((m, i) => {
        m.step_order = i + 1;
    });
    editRenderizarMaquinas();
}

// Agregar variable a una máquina (edición)
function editAgregarVariable(maquinaIndex) {
    if (!editMaquinasSeleccionadas[maquinaIndex].variables) {
        editMaquinasSeleccionadas[maquinaIndex].variables = [];
    }
    editMaquinasSeleccionadas[maquinaIndex].variables.push({
        standard_variable_id: '',
        min_value: '',
        max_value: ''
    });
    editRenderizarMaquinas();
}

// Eliminar variable (edición)
function editEliminarVariable(maquinaIndex, variableIndex) {
    editMaquinasSeleccionadas[maquinaIndex].variables.splice(variableIndex, 1);
    editRenderizarMaquinas();
}

// Actualizar variable (edición)
function editActualizarVariable(maquinaIndex, variableIndex, campo, valor) {
    if (!editMaquinasSeleccionadas[maquinaIndex] || !editMaquinasSeleccionadas[maquinaIndex].variables || !editMaquinasSeleccionadas[maquinaIndex].variables[variableIndex]) {
        return;
    }
    
    if (campo === 'standard_variable_id') {
        const variable = variablesEstandar.find(v => v.variable_id == valor);
        editMaquinasSeleccionadas[maquinaIndex].variables[variableIndex].standard_variable_id = valor;
        if (variable) {
            $('#edit_unidad_' + maquinaIndex + '_' + variableIndex).val(variable.unit || '');
        }
    } else {
        editMaquinasSeleccionadas[maquinaIndex].variables[variableIndex][campo] = valor;
    }
}

// Sincronizar cuando se cambian los valores en los inputs (edición)
$(document).on('change', '#editarProcesoModal input[name*="[min_value]"], #editarProcesoModal input[name*="[max_value]"]', function() {
    const name = $(this).attr('name');
    const matches = name.match(/maquinas\[(\d+)\]\[variables\]\[(\d+)\]/);
    if (matches) {
        const mIndex = parseInt(matches[1]);
        const vIndex = parseInt(matches[2]);
        const campo = name.includes('min_value') ? 'min_value' : 'max_value';
        editActualizarVariable(mIndex, vIndex, campo, $(this).val());
    }
});

// Renderizar máquinas seleccionadas (edición)
function editRenderizarMaquinas() {
    const container = $('#editListaMaquinas');
    
    if (editMaquinasSeleccionadas.length === 0) {
        container.html('<p class="text-muted">No hay máquinas seleccionadas. Haz clic en "Agregar" para seleccionar máquinas.</p>');
        return;
    }
    
    let html = '';
    editMaquinasSeleccionadas.forEach((maquina, mIndex) => {
        html += `
            <div class="card mb-3 maquina-item" data-index="${mIndex}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Paso ${maquina.step_order}: ${maquina.name}</strong>
                        <input type="hidden" name="maquinas[${mIndex}][machine_id]" value="${maquina.machine_id}">
                        <input type="hidden" name="maquinas[${mIndex}][step_order]" value="${maquina.step_order}">
                        <input type="hidden" name="maquinas[${mIndex}][name]" value="${maquina.name}">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="editEliminarMaquina(${mIndex})">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Descripción (opcional)</label>
                            <input type="text" class="form-control" name="maquinas[${mIndex}][description]" 
                                   value="${maquina.description || ''}" placeholder="Descripción de esta máquina en el proceso">
                        </div>
                        <div class="col-md-6">
                            <label>Tiempo Estimado (minutos, opcional)</label>
                            <input type="number" class="form-control" name="maquinas[${mIndex}][estimated_time]" 
                                   value="${maquina.estimated_time || ''}" placeholder="Ej: 30" min="0">
                        </div>
                    </div>
                    
                    <h6 class="mb-2">Variables Estándar:</h6>
                    <div id="edit_variables_${mIndex}">
        `;
        
        if (maquina.variables && maquina.variables.length > 0) {
            maquina.variables.forEach((variable, vIndex) => {
                const varEstandar = variablesEstandar.find(v => v.variable_id == variable.standard_variable_id);
                html += `
                    <div class="row mb-2 variable-item">
                        <div class="col-md-4">
                            <label>Variable <span class="text-danger">*</span></label>
                            <select class="form-control" name="maquinas[${mIndex}][variables][${vIndex}][standard_variable_id]" 
                                    onchange="editActualizarVariable(${mIndex}, ${vIndex}, 'standard_variable_id', this.value)" required>
                                <option value="">Seleccionar...</option>
                                ${variablesEstandar.map(v => `
                                    <option value="${v.variable_id}" ${variable.standard_variable_id == v.variable_id ? 'selected' : ''}>
                                        ${v.name} ${v.unit ? '(' + v.unit + ')' : ''}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Unidad</label>
                            <input type="text" class="form-control" id="edit_unidad_${mIndex}_${vIndex}" 
                                   value="${varEstandar ? (varEstandar.unit || '') : ''}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label>Valor Mínimo <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" 
                                   name="maquinas[${mIndex}][variables][${vIndex}][min_value]" 
                                   value="${variable.min_value || ''}" required>
                        </div>
                        <div class="col-md-2">
                            <label>Valor Máximo <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" 
                                   name="maquinas[${mIndex}][variables][${vIndex}][max_value]" 
                                   value="${variable.max_value || ''}" required>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger btn-block" 
                                    onclick="editEliminarVariable(${mIndex}, ${vIndex})">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                `;
            });
        } else {
            html += '<p class="text-muted">No hay variables agregadas. Haz clic en "Agregar Variable" para agregar una.</p>';
        }
        
        html += `
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="editAgregarVariable(${mIndex})">
                        <i class="fas fa-plus mr-1"></i> Agregar Variable
                    </button>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

// Limpiar al cerrar el modal de edición
$('#editarProcesoModal').on('hidden.bs.modal', function () {
    editMaquinasSeleccionadas = [];
    $('#editListaMaquinas').html('<p class="text-muted">No hay máquinas seleccionadas. Haz clic en "Agregar" para seleccionar máquinas.</p>');
});
</script>
@endpush
