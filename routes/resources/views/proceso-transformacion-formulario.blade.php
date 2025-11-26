@extends('layouts.app')

@section('page_title', 'Completar Formulario de Máquina')

@section('content')
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

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    Formulario: {{ $processMachine->name }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('proceso-transformacion', $batch->batch_id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(!$canAccess)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ $errorMessage }}
                </div>
                @else
                <!-- Información de la Máquina -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Información del Lote</h5>
                        <p><strong>Lote:</strong> #{{ $batch->batch_code ?? $batch->batch_id }}</p>
                        <p><strong>Nombre:</strong> {{ $batch->name ?? 'Sin nombre' }}</p>
                        <p><strong>Cliente:</strong> {{ $batch->order->customer->business_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Información de la Máquina</h5>
                        <p><strong>Paso:</strong> {{ $processMachine->step_order }}</p>
                        <p><strong>Nombre:</strong> {{ $processMachine->name }}</p>
                        <p><strong>Proceso:</strong> {{ $processMachine->process->name ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($processMachine->machine && $processMachine->machine->image_url)
                <div class="text-center mb-4">
                    <img src="{{ $processMachine->machine->image_url }}" 
                         alt="{{ $processMachine->name }}" 
                         class="img-fluid" 
                         style="max-height: 200px; object-fit: contain;">
                </div>
                @endif

                <!-- Formulario de Variables -->
                <form method="POST" action="{{ route('proceso-transformacion.registrar', [$batch->batch_id, $processMachine->process_machine_id]) }}" id="formularioMaquina">
                    @csrf
                    
                    <h5 class="mb-3">Variables Estándar</h5>
                    
                    @if($processMachine->variables->isEmpty())
                    <div class="alert alert-warning">
                        Esta máquina no tiene variables configuradas.
                    </div>
                    @else
                    <div class="row">
                        @foreach($processMachine->variables as $variable)
                        @php
                            $varName = $variable->standardVariable->code ?? $variable->standardVariable->name;
                            $oldValue = old('entered_variables.' . $varName);
                            if (!$oldValue && $record && isset($record->entered_variables[$varName])) {
                                $oldValue = $record->entered_variables[$varName];
                            }
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="variable_{{ $variable->variable_id }}">
                                    {{ $variable->standardVariable->name ?? 'N/A' }}
                                    @if($variable->standardVariable->unit)
                                        <small class="text-muted">({{ $variable->standardVariable->unit }})</small>
                                    @endif
                                    @if($variable->mandatory)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           step="0.01" 
                                           class="form-control @error('entered_variables.' . $varName) is-invalid @enderror" 
                                           id="variable_{{ $variable->variable_id }}"
                                           name="entered_variables[{{ $varName }}]" 
                                           value="{{ $oldValue }}"
                                           min="{{ $variable->min_value }}"
                                           max="{{ $variable->max_value }}"
                                           @if($variable->mandatory) required @endif
                                           placeholder="Rango: {{ $variable->min_value }} - {{ $variable->max_value }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            Min: {{ $variable->min_value }} | Max: {{ $variable->max_value }}
                                        </span>
                                    </div>
                                </div>
                                @error('entered_variables.' . $varName)
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    @if($variable->target_value)
                                        Valor objetivo: {{ $variable->target_value }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Observaciones -->
                    <div class="form-group">
                        <label for="observations">Observaciones (opcional)</label>
                        <textarea class="form-control @error('observations') is-invalid @enderror" 
                                  id="observations" 
                                  name="observations" 
                                  rows="3" 
                                  placeholder="Observaciones sobre este proceso...">{{ old('observations', $record->observations ?? '') }}</textarea>
                        @error('observations')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group text-right">
                        <a href="{{ route('proceso-transformacion', $batch->batch_id) }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Guardar Formulario
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Validación en tiempo real
document.querySelectorAll('input[type="number"]').forEach(function(input) {
    input.addEventListener('blur', function() {
        const min = parseFloat(this.getAttribute('min'));
        const max = parseFloat(this.getAttribute('max'));
        const value = parseFloat(this.value);
        
        if (this.value && !isNaN(value)) {
            if (value < min || value > max) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        }
    });
});
</script>
@endpush

