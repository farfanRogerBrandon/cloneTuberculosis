@extends('layout')

@section('title', 'Lista de Pacientes')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sedes-Tuberculosis - ListaPacientes</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/Navbar.css?v=1.1') }}" rel="stylesheet">
    <link href="{{ asset('css/lista-pacientes.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Menu -->
    <div id="menu-container"></div>

    <div class="container">

        <!-- Encabezado de la Página -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-0 text-primary my-custom-title">Lista de Pacientes</h1>
                <p class="text-muted">Gestiona y monitorea los pacientes registrados</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primaryy" onclick="window.location.href='/patients/create'">
                    <i class="fas fa-plus me-2"></i>Nuevo Paciente
                </button>
            </div>
        </div>

        <!-- Barra de búsqueda -->
        <div class="search-container">
            <div class="input-group">
                <span class="input-group-text border-0">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" id="searchInput" class="form-control" placeholder="Buscar paciente por nombre o CI...">
            </div>
        </div>

        <!-- Tabla de Pacientes -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                    <th>Nombres y Apellidos</th>
                    <th>CI</th>
                    <th>Establecimiento</th>
                    <th>Fase</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                                        @php
                        $rol = session('empleado_rol');
                    @endphp
                    @if(session('empleado_rol') !== 'ENFERMERO')
                        <th>Transferir</th>
                    @endif
                    <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody id="patientsTableBody">
                    <!-- Los datos se cargarán dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Detalles del Paciente -->
    <div class="modal fade" id="patientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                           
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">Estado del Tratamiento</h6>
                                    <form id="doseForm" onsubmit="event.preventDefault(); createDose();">
                                        <div class="mb-3">
                                            <label class="form-label">Número de Dosis</label>
                                            <input type="number" id="doseNumber" class="form-control" min="1" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Fecha de Grabación</label>
                                            <input type="date" id="startDate" class="form-control" min="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-primary" onclick="createDose()">
                                                <i class="fas fa-plus me-2"></i>Crear Dosis
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Información Personal -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="card-title mb-0">Información Personal</h6>
                                        <button class="btn btn-sm btn-secondary" id="editPatientBtn" onclick="toggleEditMode()">
                                            <i class="fas fa-edit me-2"></i>Editar
                                        </button>
                                    </div>
                                    <form id="patientInfoForm">
                                        <div id="patientInfo" class="row g-3">
                                            <!-- La información se cargará dinámicamente aquí -->
                                        </div>
                                        <div class="mt-3 d-none" id="saveChangesContainer">
                                            <button type="button" class="btn btn-success" onclick="savePatientChanges()">
                                                <i class="fas fa-save me-2"></i>Guardar Cambios
                                            </button>
                                            <button type="button" class="btn btn-secondary ms-2" onclick="cancelEdit()">
                                                Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Historial de Dosis -->
<div class="card mt-4">
    <div class="card-body">
        <h6 class="card-title mb-3">Historial de Dosis</h6>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Número de Dosis</th>
                        <th>Fecha de Grabación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="doseHistoryTable">
                    <!-- Las dosis se cargarán dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

                    </div>
                </div>
            </div>
        </div>
    </div>
 <!-- Modal transferencia del paciente -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Transferir Paciente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="transferForm">
                        <div class="mb-3">
                            <label class="form-label">Paciente</label>
                            <input type="text" id="transferPatientName" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Establecimiento Actual</label>
                            <input type="text" id="transferCurrentEstablishment" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nuevo Establecimiento</label>
                            <select id="transferNewEstablishment" class="form-select" required>
                                <option value="">Seleccione un establecimiento</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento de Transferencia</label>
                            <input type="file" id="transferDocument" class="form-control" accept="image/*" required>
                            <div class="mt-2">
                                <img id="transferPreview" class="img-fluid" style="max-height: 200px; display: none;">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmTransferBtn">Transferir</button>
                </div>
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Historial de Transferencias</h6>
                            <div class="table-responsive">
                                <table class="table table-striped" id="transferHistoryTable">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Documento</th>
                                        </tr>
                                    </thead>
                                    <tbody id="transferHistoryBody">
                                        <!-- Las transferencias se cargarán dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage">¿Está seguro que desea eliminar a <span id="patientName" class="fw-bold"></span>?</p>
                    <p class="text-muted">Esta acción cambiará el estado del paciente a "Inactivo".</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div id="footer-container"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/common.js') }}"></script>
    <script src="{{ asset('js/patient-list.js') }}"></script>
    <script src="{{ asset('js/patient-actions.js') }}"></script>
    <script src="{{ asset('js/patient-transfer.js') }}"></script>
    <script src="{{ asset('js/patient-deletion.js') }}"></script>
    <script>
        const empleadoRol = @json(session('empleado_rol'));
    </script>
</body>
</html>
@endsection