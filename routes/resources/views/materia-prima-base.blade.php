@extends('layouts.app')

@section('page_title', 'Materia Prima Base')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-seedling mr-1"></i>
                    Materia Prima Base
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearMateriaPrimaModal">
                        <i class="fas fa-plus"></i> Crear Materia Prima
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
                                <h3>{{ $materias_primas->total() }}</h3>
                                <p>Total Materias</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $materias_primas->filter(function($mp) { 
                                    $available = $mp->calculated_available_quantity ?? ($mp->available_quantity ?? 0);
                                    $minimum = $mp->minimum_stock ?? 0;
                                    return $available > 0 && ($minimum == 0 || $available > $minimum);
                                })->count() }}</h3>
                                <p>Disponibles</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $materias_primas->filter(function($mp) { 
                                    $available = $mp->calculated_available_quantity ?? ($mp->available_quantity ?? 0);
                                    $minimum = $mp->minimum_stock ?? 0;
                                    return $available > 0 && $minimum > 0 && $available <= $minimum;
                                })->count() }}</h3>
                                <p>Bajo Stock</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $materias_primas->filter(function($mp) { 
                                    $available = $mp->calculated_available_quantity ?? ($mp->available_quantity ?? 0);
                                    return $available <= 0;
                                })->count() }}</h3>
                                <p>Agotadas</p>
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
                            <option value="harina">Harinas</option>
                            <option value="azucar">Azúcares</option>
                            <option value="sal">Sales</option>
                            <option value="especias">Especias</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="disponible">Disponible</option>
                            <option value="bajo_stock">Bajo Stock</option>
                            <option value="agotado">Agotado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por nombre..." id="buscarMateria">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Materia Prima -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Unidad</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Stock Máximo</th>
                                <th>Estado</th>
                                <th>Código</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($materias_primas as $mp)
                            @php
                                $available = $mp->calculated_available_quantity ?? ($mp->available_quantity ?? 0);
                                $minimum = $mp->minimum_stock ?? 0;
                                $maximum = $mp->maximum_stock ?? 0;
                            @endphp
                            <tr>
                                <td>
                                    @if($mp->image_url)
                                        <img src="{{ $mp->image_url }}" alt="{{ $mp->name }}" 
                                             class="img-thumbnail" style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>#{{ $mp->material_id }}</td>
                                <td>{{ $mp->name }}</td>
                                <td>{{ $mp->category->name ?? 'N/A' }}</td>
                                <td>{{ $mp->unit->code ?? 'N/A' }}</td>
                                <td>
                                    <strong class="{{ $available <= 0 ? 'text-danger' : ($minimum > 0 && $available <= $minimum ? 'text-warning' : 'text-success') }}">
                                        {{ number_format($available, 2) }}
                                    </strong>
                                    <small class="text-muted"> {{ $mp->unit->code ?? '' }}</small>
                                </td>
                                <td>{{ number_format($minimum, 2) }}</td>
                                <td>{{ $maximum > 0 ? number_format($maximum, 2) : 'N/A' }}</td>
                                <td>
                                    @if($available <= 0)
                                        <span class="badge badge-danger">Agotado</span>
                                    @elseif($minimum > 0 && $available <= $minimum)
                                        <span class="badge badge-warning">Bajo Stock</span>
                                    @else
                                        <span class="badge badge-success">Disponible</span>
                                    @endif
                                </td>
                                <td>{{ $mp->code }}</td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-info" title="Ver" onclick="verMateriaPrima({{ $mp->material_id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Editar" onclick="editarMateriaPrima({{ $mp->material_id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No hay materias primas registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($materias_primas->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando {{ $materias_primas->firstItem() }} a {{ $materias_primas->lastItem() }} de {{ $materias_primas->total() }} registros
                    </div>
                    <nav>
                        {{ $materias_primas->links() }}
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Materia Prima -->
<div class="modal fade" id="crearMateriaPrimaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nueva Materia Prima</h4>
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
                <form method="POST" action="{{ route('materia-prima-base') }}" id="crearMateriaPrimaForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Ej: Harina de Trigo" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->category_id }}" {{ old('category_id') == $cat->category_id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit_id">Unidad de Medida <span class="text-danger">*</span></label>
                                <select class="form-control @error('unit_id') is-invalid @enderror" 
                                        id="unit_id" name="unit_id" required>
                                    <option value="">Seleccionar unidad...</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->unit_id }}" {{ old('unit_id') == $unidad->unit_id ? 'selected' : '' }}>
                                            {{ $unidad->name }} ({{ $unidad->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="minimum_stock">Stock Mínimo</label>
                                <input type="number" class="form-control" id="minimum_stock" 
                                       name="minimum_stock" value="{{ old('minimum_stock', 0) }}" 
                                       placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maximum_stock">Stock Máximo</label>
                                <input type="number" class="form-control" id="maximum_stock" 
                                       name="maximum_stock" value="{{ old('maximum_stock') }}" 
                                       placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3" placeholder="Descripción de la materia prima...">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_file">
                            <i class="fas fa-image mr-1"></i>
                            Imagen de la Materia Prima
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('image_file') is-invalid @enderror" 
                                   id="image_file" name="image_file" accept="image/jpeg,image/jpg,image/png" 
                                   onchange="previewImage(this, 'mp_image_preview')">
                            <label class="custom-file-label" for="image_file">Seleccionar imagen...</label>
                            @error('image_file')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG (máx. 5MB)</small>
                        
                        <!-- Previsualización de imagen -->
                        <div id="mp_image_preview_container" class="mt-3" style="display: none;">
                            <img id="mp_image_preview" src="" alt="Vista previa" 
                                 class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearImagePreview('mp_image_preview')">
                                <i class="fas fa-times"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="crearMateriaPrimaBtn" onclick="submitCrearMateriaPrima()">
                    <i class="fas fa-save mr-1"></i> Crear Materia Prima
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Modal Ver Materia Prima -->
<div class="modal fade" id="verMateriaPrimaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles de la Materia Prima
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verMateriaPrimaContent">
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

<!-- Modal Editar Materia Prima -->
<div class="modal fade" id="editarMateriaPrimaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Materia Prima
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
                <form method="POST" action="" id="editarMateriaPrimaForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="edit_name">
                            <i class="fas fa-tag mr-1"></i>
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="edit_name" name="name" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_category_id">
                                    <i class="fas fa-folder mr-1"></i>
                                    Categoría <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('category_id') is-invalid @enderror" 
                                        id="edit_category_id" name="category_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->category_id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_unit_id">
                                    <i class="fas fa-ruler mr-1"></i>
                                    Unidad de Medida <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('unit_id') is-invalid @enderror" 
                                        id="edit_unit_id" name="unit_id" required>
                                    <option value="">Seleccionar unidad...</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->unit_id }}">{{ $unidad->name }} ({{ $unidad->code }})</option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_minimum_stock">
                                    <i class="fas fa-arrow-down mr-1"></i>
                                    Stock Mínimo
                                </label>
                                <input type="number" class="form-control" id="edit_minimum_stock" 
                                       name="minimum_stock" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_maximum_stock">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    Stock Máximo
                                </label>
                                <input type="number" class="form-control" id="edit_maximum_stock" 
                                       name="maximum_stock" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">
                            <i class="fas fa-align-left mr-1"></i>
                            Descripción
                        </label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_image_file">
                            <i class="fas fa-image mr-1"></i>
                            Imagen de la Materia Prima
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" 
                                   id="edit_image_file" name="image_file" accept="image/jpeg,image/jpg,image/png" 
                                   onchange="previewImage(this, 'edit_mp_image_preview')">
                            <label class="custom-file-label" for="edit_image_file">Seleccionar nueva imagen...</label>
                        </div>
                        <small class="form-text text-muted">Dejar vacío para mantener la imagen actual</small>
                        
                        <!-- Previsualización de nueva imagen -->
                        <div id="edit_mp_image_preview_container" class="mt-3" style="display: none;">
                            <p class="text-muted small">Nueva imagen:</p>
                            <img id="edit_mp_image_preview" src="" alt="Vista previa" 
                                 class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearImagePreview('edit_mp_image_preview')">
                                <i class="fas fa-times"></i> Eliminar
                            </button>
                        </div>
                        
                        <!-- Imagen actual -->
                        <input type="hidden" id="edit_current_image_url" name="current_image_url">
                        <div id="edit_current_mp_image_container" class="mt-2" style="display: none;">
                            <p class="text-muted small">Imagen actual:</p>
                            <img id="edit_current_mp_image" src="" alt="Imagen actual" 
                                 class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="active" id="edit_active" value="1">
                            Materia Prima Activa
                        </label>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Materia Prima
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const categorias = @json($categorias);
const unidades = @json($unidades);

function verMateriaPrima(id) {
    fetch(`{{ url('materia-prima-base') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            const categoria = categorias.find(c => c.category_id == data.category_id);
            const unidad = unidades.find(u => u.unit_id == data.unit_id);
            const stockActual = data.available_quantity || '0.00';
            
            const imageHtml = data.image_url 
                ? `<div class="text-center mb-3">
                    <img src="${data.image_url}" alt="${data.name}" 
                         class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                   </div>`
                : '<p class="text-muted text-center">Sin imagen</p>';
            
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        ${imageHtml}
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID</th>
                                <td>#${data.material_id}</td>
                            </tr>
                            <tr>
                                <th>Código</th>
                                <td><span class="badge badge-primary">${data.code}</span></td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>${data.name}</td>
                            </tr>
                            <tr>
                                <th>Categoría</th>
                                <td>${categoria ? categoria.name : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Unidad</th>
                                <td>${unidad ? unidad.name + ' (' + unidad.code + ')' : 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Stock Actual</th>
                                <td>
                                    <strong>${stockActual}</strong>
                                    <small class="text-muted"> ${unidad ? '(' + unidad.code + ')' : ''}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Stock Mínimo</th>
                                <td>${data.minimum_stock || 0}</td>
                            </tr>
                            <tr>
                                <th>Stock Máximo</th>
                                <td>${data.maximum_stock || 'N/A'}</td>
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
            document.getElementById('verMateriaPrimaContent').innerHTML = content;
            $('#verMateriaPrimaModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la materia prima');
        });
}

function editarMateriaPrima(id) {
    fetch(`{{ url('materia-prima-base') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editarMateriaPrimaForm').action = `{{ url('materia-prima-base') }}/${id}`;
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_category_id').value = data.category_id || '';
            document.getElementById('edit_unit_id').value = data.unit_id || '';
            document.getElementById('edit_minimum_stock').value = data.minimum_stock || 0;
            document.getElementById('edit_maximum_stock').value = data.maximum_stock || '';
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_active').checked = data.active || false;
            
            // Mostrar imagen actual si existe
            if (data.image_url) {
                document.getElementById('edit_current_image_url').value = data.image_url;
                document.getElementById('edit_current_mp_image').src = data.image_url;
                document.getElementById('edit_current_mp_image_container').style.display = 'block';
            } else {
                document.getElementById('edit_current_image_url').value = '';
                document.getElementById('edit_current_mp_image_container').style.display = 'none';
            }
            
            // Limpiar previsualización de nueva imagen
            document.getElementById('edit_image_file').value = '';
            document.getElementById('edit_mp_image_preview_container').style.display = 'none';
            
            $('#editarMateriaPrimaModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la materia prima');
        });
}

function previewImage(input, previewId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            const containerId = previewId === 'mp_image_preview' ? 'mp_image_preview_container' : 'edit_mp_image_preview_container';
            document.getElementById(containerId).style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function clearImagePreview(previewId) {
    const inputId = previewId === 'mp_image_preview' ? 'image_file' : 'edit_image_file';
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).src = '';
    const containerId = previewId === 'mp_image_preview' ? 'mp_image_preview_container' : 'edit_mp_image_preview_container';
    document.getElementById(containerId).style.display = 'none';
}

async function submitCrearMateriaPrima() {
    const form = document.getElementById('crearMateriaPrimaForm');
    const formData = new FormData(form);
    const imageFile = document.getElementById('image_file').files[0];
    const submitButton = document.getElementById('crearMateriaPrimaBtn');
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Creando...';
    
    try {
        // Si hay una imagen, subirla primero
        if (imageFile) {
            const uploadFormData = new FormData();
            uploadFormData.append('image', imageFile);
            uploadFormData.append('folder', 'materias-primas');
            
            const uploadResponse = await fetch('{{ route("upload-image") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: uploadFormData
            });
            
            const uploadResult = await uploadResponse.json();
            
            if (!uploadResult.success) {
                throw new Error(uploadResult.message || 'Error al subir la imagen');
            }
            
            formData.append('image_url', uploadResult.imageUrl);
        }
        
        formData.delete('image_file');
        
        // Enviar formulario
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        if (response.ok) {
            window.location.reload();
        } else {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error al crear la materia prima');
        }
    } catch (error) {
        alert('Error: ' + error.message);
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save mr-1"></i> Crear Materia Prima';
    }
}

// Manejar envío del formulario de edición con carga de imagen
document.getElementById('editarMateriaPrimaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const imageFile = document.getElementById('edit_image_file').files[0];
    const currentImageUrl = document.getElementById('edit_current_image_url').value;
    const submitButton = this.querySelector('button[type="submit"]');
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Actualizando...';
    
    try {
        // Si hay una imagen nueva, subirla primero
        if (imageFile) {
            const uploadFormData = new FormData();
            uploadFormData.append('image', imageFile);
            uploadFormData.append('folder', 'materias-primas');
            
            const uploadResponse = await fetch('{{ route("upload-image") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: uploadFormData
            });
            
            const uploadResult = await uploadResponse.json();
            
            if (!uploadResult.success) {
                throw new Error(uploadResult.message || 'Error al subir la imagen');
            }
            
            formData.append('image_url', uploadResult.imageUrl);
        } else {
            // Mantener la imagen actual
            formData.append('current_image_url', currentImageUrl);
        }
        
        formData.delete('edit_image_file');
        formData.append('_method', 'PUT');
        
        // Enviar formulario
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        if (response.ok) {
            window.location.reload();
        } else {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error al actualizar la materia prima');
        }
    } catch (error) {
        alert('Error: ' + error.message);
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save mr-1"></i> Actualizar Materia Prima';
    }
});

// Actualizar labels de inputs file
document.getElementById('image_file')?.addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Seleccionar imagen...';
    const label = this.nextElementSibling;
    if (label) label.textContent = fileName;
});

document.getElementById('edit_image_file')?.addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Seleccionar nueva imagen...';
    const label = this.nextElementSibling;
    if (label) label.textContent = fileName;
});

function aplicarFiltros() {
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    const buscar = document.getElementById('buscarMateria').value;
    
    const url = new URL(window.location);
    if (categoria) url.searchParams.set('categoria', categoria);
    if (estado) url.searchParams.set('estado', estado);
    if (buscar) url.searchParams.set('buscar', buscar);
    window.location = url;
}
</script>
@endpush

