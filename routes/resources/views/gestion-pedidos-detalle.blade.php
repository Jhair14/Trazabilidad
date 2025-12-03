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
                                <th>Acciones</th>
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
                                <td>
                                    @if($orderProduct->status == 'pendiente')
                                        <button class="btn btn-sm btn-success" 
                                                data-toggle="modal" 
                                                data-target="#approveModal{{ $orderProduct->order_product_id }}">
                                            <i class="fas fa-check"></i> Aprobar
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                data-toggle="modal" 
                                                data-target="#rejectModal{{ $orderProduct->order_product_id }}">
                                            <i class="fas fa-times"></i> Rechazar
                                        </button>
                                    @elseif($orderProduct->status == 'aprobado')
                                        <small class="text-muted">
                                            Aprobado por: {{ $orderProduct->approver->first_name ?? 'N/A' }}<br>
                                            {{ $orderProduct->approved_at ? \Carbon\Carbon::parse($orderProduct->approved_at)->format('d/m/Y H:i') : '' }}
                                        </small>
                                    @elseif($orderProduct->status == 'rechazado')
                                        <small class="text-danger">
                                            Rechazado por: {{ $orderProduct->approver->first_name ?? 'N/A' }}<br>
                                            Razón: {{ $orderProduct->rejection_reason ?? 'N/A' }}
                                        </small>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal Aprobar -->
                            <div class="modal fade" id="approveModal{{ $orderProduct->order_product_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('gestion-pedidos.approve-product', ['orderId' => $pedido->order_id, 'productId' => $orderProduct->order_product_id]) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Aprobar Producto</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Está seguro de aprobar el producto <strong>{{ $orderProduct->product->name }}</strong>?</p>
                                                <div class="form-group">
                                                    <label>Observaciones (opcional)</label>
                                                    <textarea class="form-control" name="observations" rows="3"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success">Aprobar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Rechazar -->
                            <div class="modal fade" id="rejectModal{{ $orderProduct->order_product_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('gestion-pedidos.reject-product', ['orderId' => $pedido->order_id, 'productId' => $orderProduct->order_product_id]) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Rechazar Producto</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Está seguro de rechazar el producto <strong>{{ $orderProduct->product->name }}</strong>?</p>
                                                <div class="form-group">
                                                    <label>Razón del Rechazo <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-danger">Rechazar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay productos en este pedido</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

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
            </div>
        </div>
    </div>
</div>
@endsection

