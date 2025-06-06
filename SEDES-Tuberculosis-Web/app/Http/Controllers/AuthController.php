<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Paciente;

class AuthController extends Controller
{
    public function index()
    {
        return view('LogIn.Index');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('nombreUsuario', 'password');

        if (Auth::guard('empleado')->attempt($credentials)) {
            $empleado = Auth::guard('empleado')->user();

            session([
                'empleado_id' => $empleado->id,
                'empleado_rol' => $empleado->rol,
            ]);

            return redirect()->route('patients.index');
        }

        return redirect()->back()->with('error', 'Credenciales inválidas');
    }

    public function loginPaciente(Request $request)
    {
        $request->validate([
            'ci' => 'required',
        ]);
    
        $paciente = Paciente::where('ci', $request->ci)->first();
    
        if (! $paciente) {
            return response()->json(['message' => 'Paciente no encontrado'], 404);
        }
    
        // Generar nombre personalizado del token
        $nombreToken = $this->generarNombreToken($paciente);
    
        // Crear token de Sanctum usando ese nombre
        $token = $paciente->createToken($nombreToken)->plainTextToken;
    
        // 🔥 DEVOLVER TAMBIÉN EL PACIENTE
        return response()->json([
            'access_token' => $token,
            'user' => [
                'id' => $paciente->id,
                'nombre' => $paciente->nombres,
                'apellido' => $paciente->primerApellido,
                'ci' => $paciente->ci,
                // Agrega más campos si quieres
            ]
        ]);
    }
    
    public function logout()
    {
        Auth::guard('empleado')->logout();
        session()->flush();
        return redirect()->route('home');
    }
    
    private function generarNombreToken(Paciente $paciente): string
    {
        $ciPart        = substr($paciente->ci, 0, 5);
        $nombrePart    = strtoupper(substr($paciente->nombres, 0, 2));
        $apellidoPart  = strtoupper(substr($paciente->primerApellido, 0, 2));
        $rand1         = random_int(0, 8);
        $rand2         = random_int(0, 8);

        return $ciPart . $nombrePart . $apellidoPart . $rand1 . $rand2;
    }
    public function logoutPaciente(Request $request)
    {
        // Elimina el token que se usó en la solicitud
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

}
