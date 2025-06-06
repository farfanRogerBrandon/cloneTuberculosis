<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroPacienteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ReporteController;

Route::get('/', function () {
    return 'Hello World';
    //EN LARAVEL: php artisan serve --port=8000
    //EN POSTMAN: http://localhost:8000/api
});

// Ruta para obtener todos los pacientes en JSON
Route::get('/pacientes', [RegistroPacienteController::class, 'getPacientes'])->name('pacientes.get');

// Ruta para obtener detalles de un paciente específico en JSON
Route::get('/pacientes/{id}', [RegistroPacienteController::class, 'show'])->name('pacientes.show');

// Ruta para registrar un nuevo paciente (POST)
Route::post('/pacientes', [RegistroPacienteController::class, 'store'])->name('pacientes.store');

// Login para Pacientes (Flutter)


Route::middleware('api')->group(function () {
    Route::post('/login-paciente', [AuthController::class, 'loginPaciente']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dosis-movil', [VideoController::class, 'obtenerDosis']);
    Route::post('/edit-video/{id}', [VideoController::class, 'edit']);
    Route::post('/logout-paciente', [AuthController::class, 'logoutPaciente']); 
    Route::get('/notificaciones', [NotificacionController::class, 'pendientes']);
    Route::post('/notificaciones/marcar-leidas', [NotificacionController::class, 'marcarLeidas']);

});
//Route::get('/reportes/pacientes-por-establecimiento', [ReporteController::class, 'patientsByEstablishment'])->name('reportes.pacientes-por-establecimiento');

Route::middleware('auth:sanctum')->get('/paciente-perfil', function (Request $request) {
    $paciente = $request->user();

    return response()->json([
        'nombres' => $paciente->nombres,
        'primerApellido' => $paciente->primerApellido,
        'ci' => $paciente->ci,
    ]);
});


