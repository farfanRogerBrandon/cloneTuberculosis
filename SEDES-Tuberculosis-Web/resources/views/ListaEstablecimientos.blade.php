
@extends('layout')

@section('title', 'Lista de Establecimientos')

@section('content')
<div class="page-container">
    <div class="container">
        <div class="registration-header animate__animated animate__fadeIn">
            <img src="{{ asset('images/logotuberculosis.png') }}" alt="Logo" class="registration-logo">
            <h2><i class="fas fa-hospital me-2"></i>Lista de Establecimientos</h2>
            <p class="text-muted">Lista de todos los establecimientos registrados y sus usuarios</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success-custom animate__animated animate__fadeIn">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger-custom animate__animated animate__fadeIn">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="card registration-card shadow-lg animate__animated animate__fadeInUp">
            <div class="card-body">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Abreviación</th>
                            <th>Teléfono</th>
                            <th>Provincia</th>
                            <th>Usuarios</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($establecimientos as $establecimiento)
                            @if ($establecimiento->estado == '1')
                                <tr>
                                    <td>{{ $establecimiento->nombre }}</td>
                                    <td>{{ $establecimiento->Abreviacion }}</td>
                                    <td>{{ $establecimiento->telefono ?? 'N/A' }}</td>
                                    <td>{{ $establecimiento->provincium->nombre }}</td>
                                    <td>
                                        @foreach ($establecimiento->empleados as $empleado)
                                            <span class="user-badge">{{ $empleado->nombreUsuario }} ({{ $empleado->rol }})</span><br>
                                        @endforeach
                                    </td>
                                    <td>
                                        <button class="btn btn-custom btn-sm view-credentials" 
                                                data-id="{{ $establecimiento->id }}">
                                            <i class="fas fa-eye me-1"></i>Ver Credenciales
                                        </button>
                                        <form action="{{ route('establecimiento.destroy', $establecimiento->id) }}" 
                                              method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-danger-custom btn-sm" 
                                                    onclick="return confirm('¿Estás seguro de desactivar este establecimiento?');">
                                                <i class="fas fa-trash-alt me-1"></i>Desactivar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-exclamation-circle me-2"></i>No hay establecimientos activos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar credenciales -->
<div class="modal fade" id="credentialsModal" tabindex="-1" aria-labelledby="credentialsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="credentialsModalLabel"><i class="fas fa-key me-2"></i>Credenciales</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loadingCredentials" class="text-center" style="display: none;">
                    <div class="spinner-border" role="status"></div>
                    <p class="mt-2">Cargando credenciales...</p>
                </div>
                <ul id="credentialsList" class="list-group"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-custom" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- External CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/lista-establecimientos.css') }}">

<!-- External JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/lista-establecimientos.js') }}"></script>
@endsection
