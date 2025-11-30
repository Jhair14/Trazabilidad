@extends('layouts.app')

@section('page_title', 'Variables Estándar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sliders-h mr-1"></i>
                    Variables Estándar
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearVariableModal">
                        <i class="fas fa-plus"></i> Crear Variable
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
                                <h3>{{ $variables->total() }}</h3>
                                <p>Total Variables</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $variables->where('active', true)->count() }}</h3>
                                <p>Activas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
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
                                <h3>{{ $variables->where('active', false)->count() }}</h3>
                                <p>Inactivas</p>
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
                        <select class="form-control" id="filtroCategoria">
                            <option value="">Todas las categorías</option>
                            <option value="temperatura">Temperatura</option>
                            <option value="humedad">Humedad</option>
                            <option value="presion">Presión</option>
                            <option value="tiempo">Tiempo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="activa">Activa</option>
                            <option value="inactiva">Inactiva</option>
                            <option value="revision">En Revisión</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar..." id="buscarVariable">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Variables -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Unidad</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($variables as $variable)
                            <tr>
                                <td>#{{ $variable->variable_id }}</td>
                                <td>{{ $variable->name }}</td>
                                <td>{{ $variable->unit ?? 'N/A' }}</td>
                                <td>{{ $variable->description ?? 'Sin descripción' }}</td>
                                <td>
                                    @if($variable->active)
                                        <span class="badge badge-success">Activa</span>
                                    @else
                                        <span class="badge badge-danger">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-info" title="Ver" onclick="verVariable({{ $variable->variable_id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Editar" onclick="editarVariable({{ $variable->variable_id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('variables-estandar.destroy', $variable->variable_id) }}" 
                                          style="display: inline;" 
                                          onsubmit="return confirm('¿Está seguro de eliminar esta variable?');">
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
                                <td colspan="7" class="text-center">No hay variables estándar registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($variables->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando {{ $variables->firstItem() }} a {{ $variables->lastItem() }} de {{ $variables->total() }} registros
                    </div>
                    <nav>
                        {{ $variables->links() }}
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Variable -->
<div class="modal fade" id="crearVariableModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-sliders-h mr-1"></i>
                    Crear Nueva Variable Estándar
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
                <form method="POST" action="{{ route('variables-estandar') }}" id="crearVariableForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre de la Variable <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="Ej: Temperatura de Cocción" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="unit">
                            <i class="fas fa-ruler mr-1"></i>
                            Unidad de Medida
                        </label>
                        <input type="text" class="form-control @error('unit') is-invalid @enderror" 
                               id="unit" name="unit" value="{{ old('unit') }}" 
                               placeholder="Ej: °C, %, min, kg, etc.">
                        @error('unit')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Unidad en la que se mide esta variable (opcional)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Descripción detallada de la variable estándar...">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Crear Variable
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Modal Ver Variable -->
<div class="modal fade" id="verVariableModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles de la Variable
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verVariableContent">
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

<!-- Modal Editar Variable -->
<div class="modal fade" id="editarVariableModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Variable
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
                <form method="POST" action="" id="editarVariableForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="edit_name">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre de la Variable <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="edit_name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_unit">
                                    <i class="fas fa-ruler mr-1"></i>
                                    Unidad
                                </label>
                                <input type="text" class="form-control @error('unit') is-invalid @enderror" 
                                       id="edit_unit" name="unit" value="{{ old('unit') }}" 
                                       placeholder="Ej: °C, %, min">
                                @error('unit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="edit_description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="active" id="edit_active" value="1">
                            Variable Activa
                        </label>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Variable
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function verVariable(id) {
    fetch(`{{ url('variables-estandar') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID</th>
                                <td>#${data.variable_id}</td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>${data.name}</td>
                            </tr>
                            <tr>
                                <th>Unidad</th>
                                <td>${data.unit || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Descripción</th>
                                <td>${data.description || 'Sin descripción'}</td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td>
                                    ${data.active 
                                        ? '<span class="badge badge-success">Activa</span>' 
                                        : '<span class="badge badge-danger">Inactiva</span>'}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('verVariableContent').innerHTML = content;
            $('#verVariableModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la variable');
        });
}

function editarVariable(id) {
    fetch(`{{ url('variables-estandar') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editarVariableForm').action = `{{ url('variables-estandar') }}/${id}`;
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_unit').value = data.unit || '';
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_active').checked = data.active || false;
            $('#editarVariableModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la variable');
        });
}

function aplicarFiltros() {
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    const buscar = document.getElementById('buscarVariable').value;
    
    const url = new URL(window.location);
    if (categoria) url.searchParams.set('categoria', categoria);
    if (estado) url.searchParams.set('estado', estado);
    if (buscar) url.searchParams.set('buscar', buscar);
    window.location = url;
}
</script>
@endpush

