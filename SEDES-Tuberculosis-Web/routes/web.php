<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroPacienteController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VideoController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::get('/login', function () {
    return view('authentication.login');
})->name('login');

Route::get('/signup', function () {
    return view('authentication.signup');
})->name('signup');

Route::get('/documentation', function () {
    return view('documentation'); 
})->name('documentation');

Route::post('/login', [AuthController::class, 'login'])->name('empleado.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('empleado.logout');

Route::middleware(['auth.empleado', 'check.empleado.rol:ADMINISTRADOR,MEDICO,ENFERMERO'])->group(function () {
    // Patient routes
    Route::get('/patients', [RegistroPacienteController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [RegistroPacienteController::class, 'create'])->name('patients.create');
    Route::post('/patients', [RegistroPacienteController::class, 'store'])->name('patients.store');
    Route::get('/patients/data', [RegistroPacienteController::class, 'getPacientes'])->name('patients.data');
    Route::get('/patients/{id}', [RegistroPacienteController::class, 'show'])->name('patients.show');
    Route::put('/patients/{id}', [RegistroPacienteController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{id}', [RegistroPacienteController::class, 'destroy'])->name('patients.destroy');
    Route::post('/patients/{id}/transfer', [RegistroPacienteController::class, 'transfer'])->name('patients.transfer');
    Route::get('/pacientes/{id}/transferencias', [RegistroPacienteController::class, 'getTransferHistory'])->name('pacientes.transferencias');
    //Route::get('/establishments/data', [RegistroPacienteController::class, 'getEstablishments'])->name('establishments.data');
    Route::get('/api/establecimientos', [RegistroPacienteController::class, 'getEstablishments'])->name('establishments.data');
    Route::post('/patients/{patientId}/dosis', [RegistroPacienteController::class, 'storeDose'])->name('dosis.store');
    Route::delete('/dosis/{id}', [RegistroPacienteController::class, 'deleteDose'])->name('dosis.delete');
    Route::post('/dosis/{doseId}/video', [RegistroPacienteController::class, 'uploadDoseVideo'])->name('dosis.uploadVideo');
    Route::get('/documentacion', function () {
        return view('Documentacion');
    })->name('documentacion');

    // Rutas para transferencia
    Route::post('/pacientes/{id}/transfer', [RegistroPacienteController::class, 'transfer'])->name('patients.transfer');
    Route::get('/pacientes/{id}/transferencias', [RegistroPacienteController::class, 'getTransferHistory'])->name('pacientes.transferencias');
    Route::get('/establecimientos', [RegistroPacienteController::class, 'getEstablishments'])->name('establishments.data');

    // Establishment routes
    Route::get('/establecimientos', [EstablecimientoController::class, 'index'])->name('establecimiento.index');
    Route::get('/establecimiento/create', [EstablecimientoController::class, 'create'])->name('establecimiento.create');
    Route::post('/establecimiento', [EstablecimientoController::class, 'store'])->name('establecimiento.store');
    Route::post('/establecimiento/{id}', [EstablecimientoController::class, 'destroy'])->name('establecimiento.destroy');
    Route::get('/establecimiento/{id}/credentials', [EstablecimientoController::class, 'showCredentials'])->name('establecimiento.credentials');
    Route::get('/provincias/{idDepartamento}', [EstablecimientoController::class, 'getProvincias']);
    Route::get('/establecimiento/provincia/{idProvincia}', [EstablecimientoController::class, 'getEstablecimiento']);
    Route::get('/reportes/pacientes-por-establecimiento', [ReporteController::class, 'patientsByEstablishment'])->name('reportes.pacientes-por-establecimiento');
    Route::get('/reportes/provincias', [ReporteController::class, 'getProvinces'])->name('reportes.provincias');
    Route::get('/provinces', [ReporteController::class, 'getProvinces'])->name('api.provinces');
    Route::get('/establishments', [ReporteController::class, 'getEstablishments'])->name('api.establishments');
    Route::get('/patients-by-establishment', [ReporteController::class, 'patientsByEstablishment'])->name('api.patients-by-establishment');
    Route::get('/video-url/{id}', [VideoController::class, 'getVideoUrl']);
});
