
@extends('layout')

@section('title', 'Registro de Paciente')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro de Pacientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="{{ asset('css/Paciente/crear.css') }}" rel="stylesheet">
</head>
<body>    
    <div class="page-container">
        <div class="container">
            <div class="registration-header">
                <img src="{{ asset('images/logotuberculosis.png') }}" alt="Logo" class="registration-logo">
                <h1>Registro de Pacientes</h1>
                <p class="text-muted">Complete el formulario con los datos del paciente</p>
            </div>

            <div class="card registration-card">
                <div class="card-body">
                 <form id="Formb" class="registration-form" method="POST" action="{{ route('patients.store') }}">

                        @csrf
                        <div class="form-header">
                            <i class="fas fa-user-plus"></i>
                            <h4>Registro de Paciente</h4>
                        </div>

                        <div class="form-section p-4">
                            <h5 class="section-title"><i class="fas fa-user me-2"></i>Datos Personales</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <i class="fas fa-id-card"></i>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="ci" name="ci" placeholder="CI" required>
                                            <label for="ci">Carnet de Identidad</label>
                                        </div>
                                        <div class="invalid-feedback" id="ci-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <i class="fas fa-user"></i>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombres" required>
                                            <label for="nombres">Nombres</label>
                                        </div>
                                        <div class="invalid-feedback" id="nombres-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <i class="fas fa-user-tag"></i>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="primerApellido" name="primerApellido" placeholder="Primer Apellido" required>
                                            <label for="primerApellido">Primer Apellido</label>
                                        </div>
                                        <div class="invalid-feedback" id="primerApellido-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <i class="fas fa-user-tie"></i>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="segundoApellido" name="segundoApellido" placeholder="Segundo Apellido">
                                            <label for="segundoApellido">Segundo Apellido</label>
                                        </div>
                                        <div class="invalid-feedback" id="segundoApellido-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <i class="fas fa-phone"></i>
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="celular" name="celular" placeholder="Celular" required>
                                            <label for="celular">Celular</label>
                                        </div>
                                        <div class="invalid-feedback" id="celular-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <i class="fas fa-calendar-alt"></i>
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" required>
                                            <label for="fechaNacimiento">Fecha de Nacimiento</label>
                                        </div>
                                        <div class="invalid-feedback" id="fechaNacimiento-error"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="gender-group">
                                        <label class="form-label fw-bold"><i class="fas fa-venus-mars me-2"></i>Género</label>
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="genero" id="masculino" value="masculino" required>
                                            <label class="btn btn-outline-primary" for="masculino">
                                                <i class="fas fa-mars me-2"></i>Masculino
                                            </label>
                                            <input type="radio" class="btn-check" name="genero" id="femenino" value="femenino" required>
                                            <label class="btn btn-outline-primary" for="femenino">
                                                <i class="fas fa-venus me-2"></i>Femenino
                                            </label>
                                        </div>
                                        <div class="invalid-feedback" id="genero-error" style="display: none;">Seleccione un género.</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="input-group">
                                        <i class="fas fa-hospital"></i>
                                        <div class="form-floating">
                                            <select class="form-select" id="establecimiento" name="establecimiento" required>
                                                <option value="">Seleccione establecimiento</option>
                                                @foreach($establecimientos as $establecimiento)
                                                    <option value="{{ $establecimiento->id }}">{{ $establecimiento->nombre }}</option>
                                                @endforeach
                                            </select>
                                            <label for="establecimiento">Establecimiento</label>
                                        </div>
                                        <div class="invalid-feedback" id="establecimiento-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-buttons mt-4 text-center">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Registrar Paciente
                                </button>
                            </div>
                            <div class="loading text-center mt-3" id="loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>Registrando...
                            </div>
                            <div class="error-message text-center text-danger mt-3" id="errorMessage" style="display: none;"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="success-animation">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4 class="mt-3">¡Registro Exitoso!</h4>
                    <p class="text-muted">El paciente ha sido registrado correctamente.</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        const patientsStoreUrl = "{{ route('patients.store') }}";
        const patientsIndexUrl = "{{ route('patients.index') }}";
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/Paciente/crear.js') }}"></script>
</body>
</html>
@endsection
