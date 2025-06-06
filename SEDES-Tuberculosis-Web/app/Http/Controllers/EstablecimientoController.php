<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Provincium;
use App\Models\Establecimiento;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EstablecimientoController extends Controller
{
    // Métodos relacionados con la visualización de datos
    public function index()
    {    
        $establecimientos = Establecimiento::with('empleados')->get();
        return view('ListaEstablecimientos', compact('establecimientos'));
    }

    public function create()
    {
        $departamentos = Departamento::all();
        return view('NuevoEstablecimiento', compact('departamentos'));
    }

    // Métodos de API para obtener datos
    public function getProvincias($idDepartamento)
    {
        $provincias = Provincium::where('idDepartamento', $idDepartamento)->get();
        if ($provincias->isEmpty()) {
            return response()->json(['message' => 'No hay provincias para este departamento'], 200);
        }
        return response()->json($provincias);
    }

    public function getEstablecimiento($idProvincia)
    {
        $establecimiento = Establecimiento::where('idProvincia', $idProvincia)->first();
        return response()->json($establecimiento);
    }

    // Métodos relacionados con la gestión de establecimientos
    public function store(Request $request)
    {
        $request->validate([
            'departamento' => 'required|exists:departamento,id',
            'provincia' => 'required|exists:provincia,id',
            'nombre' => 'required|string|max:50|unique:establecimiento,nombre',
            'telefono' => 'nullable|digits_between:7,8',
        ]);

        try {
            $abreviacion = $this->generarAbreviacion($request->nombre);
            
            $establecimiento = $this->crearEstablecimiento($request, $abreviacion);
            $this->crearEmpleadosPorDefecto($establecimiento);

            session()->flash('success', 'Establecimiento creado exitosamente');
            return redirect()->route('establecimiento.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el establecimiento: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $establecimiento = Establecimiento::findOrFail($id);
            $establecimiento->update(['estado' => '0']);
            
            Log::info('Establecimiento desactivado', ['id' => $id]);
            return redirect()->route('establecimiento.index')->with('success', 'Establecimiento desactivado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al desactivar el establecimiento', ['message' => $e->getMessage()]);
            return redirect()->route('establecimiento.index')->with('error', 'Error al desactivar el establecimiento.');
        }
    }

    // Métodos relacionados con credenciales
      public function showCredentials($id)
    {
        try {
            $establecimiento = Establecimiento::findOrFail($id);
            $empleados = Empleado::where('idEstablecimiento', $id)->get();
            $credentials = $this->formatearCredenciales($empleados);

            return response()->json([
                'success' => true,
                'credentials' => $credentials,
                'establecimiento' => $establecimiento->nombre
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener credenciales: ' . $e->getMessage()
            ], 500);
        }
    }

    // Métodos auxiliares privados
    private function generarAbreviacion($nombre)
    {
        $nombre = preg_replace('/[^A-Za-z]/', '', $nombre);
        $nombre = strtoupper($nombre);
        $abreviacion = substr($nombre, 0, 4);

        // Ensure abreviacion is exactly 4 letters
        if (strlen($abreviacion) < 4) {
            $abreviacion = str_pad($abreviacion, 4, 'X');
        }

        return $abreviacion;
    }

    private function generarCodigoEmpleadoUnico()
    {
        do {
            $codigoEmpleado = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Empleado::where('codigoEmpleado', $codigoEmpleado)->exists());

        return $codigoEmpleado;
    }

    private function generarNombreUsuarioUnico($abreviacion, $rol)
    {
        $suffix = $rol === 'ENFERMERO' ? 'E' : 'M';
        $baseUsername = $abreviacion . $suffix;
        $username = $baseUsername;
        $counter = 1;

        while (Empleado::where('nombreUsuario', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    private function generarPasswordUnica()
    {
        do {
            $password = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Empleado::where('password', password_hash($password, PASSWORD_BCRYPT))->exists());

        return $password;
    }

    private function crearEstablecimiento($request, $abreviacion)
    {
        return Establecimiento::create([
            'Abreviacion' => $abreviacion,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'idProvincia' => $request->provincia,
            'estado' => '1',
            'fechaRegistro' => now(),
            'registradoPor' => session('empleado_id'),
        ]);
    }

    private function crearEmpleadosPorDefecto($establecimiento)
    {
        $roles = [
            ['rol' => 'ENFERMERO', 'suffix' => 'E'],
            ['rol' => 'MEDICO', 'suffix' => 'M']
        ];
        
        $credenciales = []; // Arreglo para almacenar las credenciales generadas
        
        foreach ($roles as $role) {
            if (!Empleado::where('idEstablecimiento', $establecimiento->id)
                        ->where('rol', $role['rol'])
                        ->exists()) {
                
                $username = $this->generarNombreUsuarioUnico($establecimiento->Abreviacion, $role['rol']);
                $plainPassword = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $codigoEmpleado = $plainPassword;
                $encryptedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
                
                Empleado::create([
                    'codigoEmpleado' => $codigoEmpleado,
                    'nombreUsuario' => $username,
                    'password' => $encryptedPassword,
                    'idEstablecimiento' => $establecimiento->id,
                    'rol' => $role['rol'],
                    'fechaRegistro' => now(),
                    'registradoPor' => session('empleado_id'),
                ]);

                // Almacenar las credenciales en el arreglo
                $credenciales[$role['rol']] = [
                    'username' => $username,
                    'password' => $plainPassword, // Guardar la contraseña en texto plano
                    'codigoEmpleado' => $codigoEmpleado,
                ];

                Log::info("Empleado creado", [
                    'codigoEmpleado' => $codigoEmpleado,
                    'username' => $username,
                    'rol' => $role['rol'],
                    'plainPassword' => $plainPassword
                ]);
            }
        }
        
        // Retornar las credenciales generadas
        return $credenciales;
    }

    private function formatearCredenciales($empleados)
    {
        $credentials = [];
        foreach ($empleados as $empleado) {
            $credentials[$empleado->rol] = [
                'username' => $empleado->nombreUsuario,
                'codigoEmpleado' => $empleado->codigoEmpleado,
            ];
        }
        return $credentials;
    }
}