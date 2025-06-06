@extends('layout')

@section('title', 'Nuevo Establecimiento')

@section('content')
<div class="page-container">
    <div class="container">
        <div class="registration-header animate__animated animate__fadeIn">
            <img src="{{ asset('images/logotuberculosis.png') }}" alt="Logo" class="registration-logo">
            <h2><i class="fas fa-plus-circle me-2"></i>Crear Nuevo Establecimiento</h2>
            <p class="text-muted">Complete el formulario para registrar un nuevo establecimiento de salud</p>
        </div>

        <div class="card registration-card shadow-lg animate__animated animate__fadeInUp">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-hospital-alt me-2"></i>Registro de Establecimiento</h3>
            </div>
            <div class="card-body">
               <form id="establecimientoForm" method="POST" action="{{ route('establecimiento.store') }}" novalidate>
                    @csrf
                    <div class="form-section">
                        <h5 class="section-title">
                            <span>Datos del Establecimiento</span>
                        </h5>
                        
                        <!-- Display server-side errors -->
                        @if ($errors->any())
                            <div class="alert alert-danger mt-3" id="errorMessage">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span id="errorText">
                                    Por favor, complete todos los campos obligatorios correctamente.
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </span>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marked-alt"></i></span>
                                    <div class="form-floating">
                                        <select name="departamento" id="departamento" class="form-select">
                                            <option value="">Seleccione un departamento</option>
                                            @foreach ($departamentos as $departamento)
                                                <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                                            @endforeach
                                            @if ($departamentos->isEmpty())
                                                <option value="">No hay departamentos disponibles</option>
                                            @endif
                                        </select>
                                        <label for="departamento">Departamento</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-location-arrow"></i></span>
                                    <div class="form-floating">
                                        <select name="provincia" id="provincia" class="form-select">
                                            <option value="">Seleccione una provincia</option>
                                        </select>
                                        <label for="provincia">Provincia</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hospital-user"></i></span>
                                    <div class="form-floating">
                                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre">
                                        <label for="nombre">Nombre del Establecimiento</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                                    <div class="form-floating">
                                        <input type="tel" name="telefono" id="telefono" class="form-control" maxlength="8" placeholder="Teléfono">
                                        <label for="telefono">Teléfono de Contacto</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn btn-custom btn-lg" id="submitBtn">
                                <i class="fas fa-save me-2"></i>Crear Establecimiento
                            </button>
                        </div>
                        
                        <div class="loading" id="loading" style="display: none;">
                            <div class="spinner-border" role="status"></div>
                            <span class="ms-2">Creando establecimiento...</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- External CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="{{ asset('css/nuevo-establecimiento.css') }}">

<!-- External JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/nuevo-establecimiento.js') }}"></script>
@endsection