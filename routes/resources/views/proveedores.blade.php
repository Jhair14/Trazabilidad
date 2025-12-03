@extends('layouts.app')

@section('page_title', 'Gestión de Proveedores')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck mr-1"></i>
                    Gestión de Proveedores
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#crearProveedorModal">
                        <i class="fas fa-plus"></i> Nuevo Proveedor
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
                                <h3>{{ $proveedores->total() }}</h3>
                                <p>Total Proveedores</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-truck"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $proveedores->where('active', true)->count() }}</h3>
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
                                <h3>0</h3>
                                <p>Pendientes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $proveedores->where('active', false)->count() }}</h3>
                                <p>Inactivos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Proveedores -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Comercial</th>
                                <th>Razón Social</th>
                                <th>Persona de Contacto</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proveedores as $proveedor)
                            <tr>
                                <td>#{{ $proveedor->supplier_id }}</td>
                                <td>{{ $proveedor->trading_name ?? $proveedor->business_name }}</td>
                                <td>{{ $proveedor->business_name }}</td>
                                <td>{{ $proveedor->contact_person ?? 'N/A' }}</td>
                                <td>{{ $proveedor->phone ?? 'N/A' }}</td>
                                <td>{{ $proveedor->email ?? 'N/A' }}</td>
                                <td>
                                    @if($proveedor->active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button onclick="verProveedor({{ $proveedor->supplier_id }})" 
                                            class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editarProveedor({{ $proveedor->supplier_id }})" 
                                            class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('proveedores.web.destroy', $proveedor->supplier_id) }}" 
                                          style="display: inline;" 
                                          onsubmit="return confirm('¿Está seguro de eliminar este proveedor?');">
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
                                <td colspan="8" class="text-center">No hay proveedores registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($proveedores->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Mostrando {{ $proveedores->firstItem() }} a {{ $proveedores->lastItem() }} de {{ $proveedores->total() }} registros
                    </div>
                    <nav>
                        {{ $proveedores->links() }}
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Proveedor -->
<div class="modal fade" id="crearProveedorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-truck mr-1"></i>
                    Crear Nuevo Proveedor
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
                <form method="POST" action="{{ route('proveedores.web.store') }}" id="crearProveedorForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="business_name">
                                    <i class="fas fa-building mr-1"></i>
                                    Razón Social <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('business_name') is-invalid @enderror" 
                                       id="business_name" name="business_name" value="{{ old('business_name') }}" 
                                       placeholder="Ej: Empresa ABC S.A." required>
                                @error('business_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="trading_name">
                                    <i class="fas fa-store mr-1"></i>
                                    Nombre Comercial
                                </label>
                                <input type="text" class="form-control @error('trading_name') is-invalid @enderror" 
                                       id="trading_name" name="trading_name" value="{{ old('trading_name') }}" 
                                       placeholder="Ej: ABC Comercial">
                                @error('trading_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tax_id">
                                    <i class="fas fa-id-card mr-1"></i>
                                    RUC/NIT
                                </label>
                                <input type="text" class="form-control @error('tax_id') is-invalid @enderror" 
                                       id="tax_id" name="tax_id" value="{{ old('tax_id') }}" 
                                       placeholder="Ej: 12345678901">
                                @error('tax_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact_person">
                                    <i class="fas fa-user mr-1"></i>
                                    Persona de Contacto
                                </label>
                                <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                       id="contact_person" name="contact_person" value="{{ old('contact_person') }}" 
                                       placeholder="Ej: Juan Pérez">
                                @error('contact_person')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone mr-1"></i>
                                    Teléfono
                                </label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" 
                                       placeholder="Ej: +1 234-567-8900">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="contacto@proveedor.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Dirección
                        </label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="2" 
                                  placeholder="Dirección completa del proveedor...">{{ old('address') }}</textarea>
                        @error('address')
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
                            Crear Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Proveedor -->
<div class="modal fade" id="verProveedorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-1"></i>
                    Detalles del Proveedor
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="verProveedorContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando detalles...</p>
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

<!-- Modal Editar Proveedor -->
<div class="modal fade" id="editarProveedorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h4 class="modal-title">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Proveedor
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
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
                <form method="POST" action="" id="editarProveedorForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_business_name">
                                    <i class="fas fa-building mr-1"></i>
                                    Razón Social <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('business_name') is-invalid @enderror" 
                                       id="edit_business_name" name="business_name" required>
                                @error('business_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_trading_name">
                                    <i class="fas fa-store mr-1"></i>
                                    Nombre Comercial
                                </label>
                                <input type="text" class="form-control @error('trading_name') is-invalid @enderror" 
                                       id="edit_trading_name" name="trading_name">
                                @error('trading_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tax_id">
                                    <i class="fas fa-id-card mr-1"></i>
                                    RUC/NIT
                                </label>
                                <input type="text" class="form-control @error('tax_id') is-invalid @enderror" 
                                       id="edit_tax_id" name="tax_id">
                                @error('tax_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_contact_person">
                                    <i class="fas fa-user mr-1"></i>
                                    Persona de Contacto
                                </label>
                                <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                       id="edit_contact_person" name="contact_person">
                                @error('contact_person')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_phone">
                                    <i class="fas fa-phone mr-1"></i>
                                    Teléfono
                                </label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="edit_phone" name="phone">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_email">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="edit_email" name="email">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_address">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Dirección
                        </label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="edit_address" name="address" rows="2"></textarea>
                        @error('address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <input type="hidden" name="active" value="0" id="edit_active_hidden">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="edit_active" value="1"
                                   onchange="document.getElementById('edit_active_hidden').value = this.checked ? '1' : '0'">
                            <label class="custom-control-label" for="edit_active">
                                <i class="fas fa-check-circle mr-1"></i>
                                Proveedor Activo
                            </label>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Actualizar Proveedor
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
const proveedoresBaseUrl = '{{ url("proveedores") }}';

function verProveedor(id) {
    $('#verProveedorModal').modal('show');
    $('#verProveedorContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Cargando detalles...</p>
        </div>
    `);
    
    fetch(`${proveedoresBaseUrl}/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 30%;">ID</th>
                                <td>#${data.supplier_id}</td>
                            </tr>
                            <tr>
                                <th>Razón Social</th>
                                <td>${data.business_name || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Nombre Comercial</th>
                                <td>${data.trading_name || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>RUC/NIT</th>
                                <td>${data.tax_id || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Persona de Contacto</th>
                                <td>${data.contact_person || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Teléfono</th>
                                <td>${data.phone || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>${data.email || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Dirección</th>
                                <td>${data.address || 'N/A'}</td>
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
                                <th>Materias Primas Recibidas</th>
                                <td>${data.raw_materials_count || 0}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            $('#verProveedorContent').html(content);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#verProveedorContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los datos del proveedor
                </div>
            `);
        });
}

function editarProveedor(id) {
    fetch(`${proveedoresBaseUrl}/${id}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            document.getElementById('editarProveedorForm').action = `${proveedoresBaseUrl}/${id}`;
            document.getElementById('edit_business_name').value = data.business_name || '';
            document.getElementById('edit_trading_name').value = data.trading_name || '';
            document.getElementById('edit_tax_id').value = data.tax_id || '';
            document.getElementById('edit_contact_person').value = data.contact_person || '';
            document.getElementById('edit_phone').value = data.phone || '';
            document.getElementById('edit_email').value = data.email || '';
            document.getElementById('edit_address').value = data.address || '';
            const activeCheckbox = document.getElementById('edit_active');
            const activeHidden = document.getElementById('edit_active_hidden');
            activeCheckbox.checked = data.active || false;
            activeHidden.value = data.active ? '1' : '0';
            $('#editarProveedorModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del proveedor para editar');
        });
}
</script>
@endpush
