@extends('layouts.app')

@section('page_title', 'Detalle de Pedido - Gestión')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Detalle del Pedido: {{ $pedido->order_number }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('gestion-pedidos') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
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

                <!-- Información del Pedido -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Información del Pedido</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Nombre:</th>
                                <td>{{ $pedido->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Cliente:</th>
                                <td>{{ $pedido->customer->business_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Estado:</th>
                                <td>
                                    @if($pedido->status == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($pedido->status == 'aprobado')
                                        <span class="badge badge-success">Aprobado</span>
                                    @elseif($pedido->status == 'rechazado')
                                        <span class="badge badge-danger">Rechazado</span>
                                    @elseif($pedido->status == 'en_produccion')
                                        <span class="badge badge-info">En Producción</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($pedido->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha Creación:</th>
                                <td>{{ \Carbon\Carbon::parse($pedido->creation_date)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Fecha Entrega:</th>
                                <td>{{ $pedido->delivery_date ? \Carbon\Carbon::parse($pedido->delivery_date)->format('d/m/Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Descripción</h5>
                        <p>{{ $pedido->description ?? 'Sin descripción' }}</p>
                    </div>
                </div>

                <!-- Productos del Pedido -->
                <h5 class="mb-3">Productos del Pedido</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Unidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedido->orderProducts as $orderProduct)
                            <tr>
                                <td>
                                    <strong>{{ $orderProduct->product->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($orderProduct->product->type) }}</span>
                                </td>
                                <td>{{ number_format($orderProduct->quantity, 4) }}</td>
                                <td>{{ $orderProduct->product->unit->name ?? 'N/A' }}</td>
                                <td>
                                    @if($orderProduct->status == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($orderProduct->status == 'aprobado')
                                        <span class="badge badge-success">Aprobado</span>
                                    @elseif($orderProduct->status == 'rechazado')
                                        <span class="badge badge-danger">Rechazado</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay productos en este pedido</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Acciones de Aprobación/Rechazo -->
                @if($pedido->status == 'pendiente')
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Acciones de Aprobación</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">
                                    <strong>Nota:</strong> Al aprobar o rechazar, se aplicará la acción a todos los productos del pedido de una vez.
                                </p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button class="btn btn-success btn-lg btn-block" 
                                                data-toggle="modal" 
                                                data-target="#approveOrderModal">
                                            <i class="fas fa-check"></i> Aprobar Todo el Pedido
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button class="btn btn-danger btn-lg btn-block" 
                                                data-toggle="modal" 
                                                data-target="#rejectOrderModal">
                                            <i class="fas fa-times"></i> Rechazar Todo el Pedido
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Aprobar Pedido Completo -->
                <div class="modal fade" id="approveOrderModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('gestion-pedidos.approve-order', ['orderId' => $pedido->order_id]) }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Aprobar Pedido Completo</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro de aprobar todo el pedido <strong>{{ $pedido->name }}</strong>?</p>
                                    <p class="text-muted">Esta acción aprobará todos los productos del pedido de una vez.</p>
                                    <div class="form-group">
                                        <label>Observaciones (opcional)</label>
                                        <textarea class="form-control" name="observations" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Aprobar Todo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Rechazar Pedido Completo -->
                <div class="modal fade" id="rejectOrderModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('gestion-pedidos.reject-order', ['orderId' => $pedido->order_id]) }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Rechazar Pedido Completo</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro de rechazar todo el pedido <strong>{{ $pedido->name }}</strong>?</p>
                                    <p class="text-muted">Esta acción rechazará todos los productos del pedido de una vez.</p>
                                    <div class="form-group">
                                        <label>Razón del Rechazo <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Rechazar Todo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @elseif($pedido->status == 'aprobado')
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Pedido Aprobado</strong><br>
                    Aprobado por: {{ $pedido->approver->first_name ?? 'N/A' }}<br>
                    Fecha: {{ $pedido->approved_at ? \Carbon\Carbon::parse($pedido->approved_at)->format('d/m/Y H:i') : 'N/A' }}
                    @if($pedido->observations)
                    <br>Observaciones: {{ $pedido->observations }}
                    @endif
                </div>
                @elseif($pedido->status == 'rechazado')
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-times-circle"></i>
                    <strong>Pedido Rechazado</strong><br>
                    Rechazado por: {{ $pedido->approver->first_name ?? 'N/A' }}<br>
                    Fecha: {{ $pedido->approved_at ? \Carbon\Carbon::parse($pedido->approved_at)->format('d/m/Y H:i') : 'N/A' }}
                    @if($pedido->rejection_reason)
                    <br>Razón: {{ $pedido->rejection_reason }}
                    @endif
                </div>
                @endif

                <!-- Destinos de Entrega -->
                @if($pedido->destinations->count() > 0)
                <h5 class="mb-3 mt-4">Destinos de Entrega</h5>
                <div class="row">
                    @foreach($pedido->destinations as $destination)
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Destino {{ $loop->iteration }}</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Dirección:</strong> {{ $destination->address }}</p>
                                @if($destination->reference)
                                <p><strong>Referencia:</strong> {{ $destination->reference }}</p>
                                @endif
                                @if($destination->contact_name)
                                <p><strong>Contacto:</strong> {{ $destination->contact_name }}</p>
                                @endif
                                @if($destination->contact_phone)
                                <p><strong>Teléfono:</strong> {{ $destination->contact_phone }}</p>
                                @endif
                                @if($destination->latitude && $destination->longitude)
                                <p>
                                    <strong>Coordenadas:</strong><br>
                                    Lat: {{ $destination->latitude }}, Lng: {{ $destination->longitude }}
                                </p>
                                @endif
                                
                                <h6>Productos para este destino:</h6>
                                <ul>
                                    @foreach($destination->destinationProducts as $destProduct)
                                    <li>
                                        {{ $destProduct->orderProduct->product->name }} - 
                                        Cantidad: {{ number_format($destProduct->quantity, 4) }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Envíos creados en PlantaCruds -->
                <h5 class="mb-3 mt-4">Envíos en PlantaCruds</h5>
                @if(isset($trackings) && $trackings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Destino ID</th>
                                <th>Envío ID</th>
                                <th>Código</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trackings as $t)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $t->destination_id ?? 'N/A' }}</td>
                                <td>{{ $t->envio_id ?? 'N/A' }}</td>
                                <td>{{ $t->envio_codigo ?? 'N/A' }}</td>
                                <td>
                                    @if($t->status == 'success')
                                        <span class="badge badge-success">Creado</span>
                                    @elseif($t->status == 'failed')
                                        <span class="badge badge-danger">Error</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($t->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($t->envio_id)
                                        <a href="{{ $plantaBase }}/envios/{{ $t->envio_id }}" target="_blank" class="btn btn-sm btn-primary">Ver en PlantaCruds</a>
                                    @endif
                                    @if($t->status == 'failed' && $t->error_message)
                                        <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#trackErrorModal{{ $t->id }}">Ver error</button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="trackErrorModal{{ $t->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Error Envío #{{ $t->id }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <pre style="white-space: pre-wrap;">{{ $t->error_message }}</pre>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info">No se han creado envíos todavía para este pedido.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

