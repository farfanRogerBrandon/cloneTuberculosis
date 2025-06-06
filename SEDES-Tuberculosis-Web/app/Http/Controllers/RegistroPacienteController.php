<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Establecimiento;
use App\Models\Historialmedico;
use App\Models\Dosi;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RegistroPacienteController extends Controller
{
    /**
     * Lista todos los pacientes activos con su establecimiento.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener el empleado logueado
        $empleado = Auth::guard('empleado')->user();

        if (!$empleado || !$empleado->establecimiento || !$empleado->establecimiento->provincium || !$empleado->establecimiento->provincium->departamento) {
            return redirect()->route('login')->with('error', 'No se pudo determinar el departamento o no estás autenticado');
        }

        // Obtener el ID del departamento
        $departamentoId = $empleado->establecimiento->provincium->departamento->id;

        // Filtrar pacientes por departamento y estado
        $pacientes = Paciente::with('establecimiento')
            ->where('estado', '1')
            ->whereHas('establecimiento.provincium.departamento', function ($query) use ($departamentoId) {
                $query->where('id', $departamentoId);
            })
            ->get();

        // Retornar la vista con los pacientes filtrados
        return view('ListaPaciente', compact('pacientes'));
    }

    /**
     * Busca pacientes activos por CI, nombres o apellidos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPacientes(Request $request)
    {
        $empleado = Auth::guard('empleado')->user();

        if (!$empleado) {
            return response()->json(['error' => 'No estás autenticado'], 401);
        }

        $searchTerm = $request->input('search', '');

        $query = Paciente::with('establecimiento')
            ->where('estado', '1');

        // Filtrado por rol
        if ($empleado->rol !== 'ADMINISTRADOR') {
            if (!$empleado->idEstablecimiento) {
                return response()->json(['error' => 'No tienes un establecimiento asignado'], 403);
            }
            $query->where('idEstablecimiento', $empleado->idEstablecimiento);
        }

        if (!empty($searchTerm)) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('ci', 'LIKE', "%{$searchTerm}%")
                ->orWhere('nombres', 'LIKE', "%{$searchTerm}%")
                ->orWhere('primerApellido', 'LIKE', "%{$searchTerm}%")
                ->orWhere('segundoApellido', 'LIKE', "%{$searchTerm}%");
            });
        }

        $pacientes = $query->get();

        return response()->json($pacientes);
    }
    /**
     * Muestra los detalles de un paciente con sus dosis.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $paciente = Paciente::with(['establecimiento', 'dosis'])->findOrFail($id);
        
        $response = [
            'id' => $paciente->id,
            'ci' => $paciente->ci,
            'nombres' => $paciente->nombres,
            'primerApellido' => $paciente->primerApellido,
            'segundoApellido' => $paciente->segundoApellido,
            'celular' => $paciente->celular,
            'sexo' => $paciente->sexo,
            'fechaNacimiento' => $paciente->fechaNacimiento->format('Y-m-d'),
            'establecimiento' => $paciente->establecimiento,
            'foto' => $paciente->foto ?? null,
            'estado' => $paciente->estado,
            'dosis' => $paciente->dosis->map(function ($dosis) {
                return [
                    'id' => $dosis->id,
                    'nroDosis' => $dosis->nroDosis,
                    'fechaGrabacion' => $dosis->fechaGrabacion ? $dosis->fechaGrabacion->format('Y-m-d') : null,
                    'estado' => $dosis->estado,
                    'rutaVideo' => $dosis->rutaVideo ? asset('storage/' . $dosis->rutaVideo) : null,
                ];
            })->toArray()
        ];
        
        return response()->json($response);
    }
    /**
     * Obtiene datos para el gráfico de dosis de un paciente.
     *
     * @param  int  $patientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDoseChartData($patientId)
    {
        try {
            $paciente = Paciente::findOrFail($patientId);
            if (!$paciente || $paciente->estado !== '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente no encontrado o inactivo'
                ], 404);
            }

            $dosis = Dosi::where('idPaciente', $patientId)
                ->where('estado', '1')
                ->orderBy('fechaGrabacion', 'asc')
                ->get(['nroDosis', 'fechaGrabacion']);

            $labels = $dosis->pluck('fechaGrabacion')->map(function ($date) {
                return $date ? Carbon::parse($date)->format('Y-m-d') : 'Sin fecha';
            })->toArray();

            $data = $dosis->pluck('nroDosis')->toArray();

            return response()->json([
                'success' => true,
                'chartData' => [
                    'labels' => $labels, 
                    'data' => $data,     
                    'patientName' => "{$paciente->nombres} {$paciente->primerApellido} {$paciente->segundoApellido}"
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos de las dosis: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Muestra el formulario para crear un nuevo paciente.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $establecimientos = Establecimiento::where('estado', '1')->get();
        return view('NuevoPaciente', compact('establecimientos'));
    }

    /**
     * Registra un nuevo paciente con las validaciones solicitadas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'ci' => [
                    'required',
                    'string',
                    'max:13',
                    'regex:/^[0-9]{7,8}(\s[A-Z]{1,2})?$/', // 7-8 dígitos, espacio opcional y hasta 2 letras
                    function ($attribute, $value, $fail) {
                        if (trim($value) !== $value) {
                            $fail('El CI no puede empezar ni terminar con espacios.');
                        }
                        if (substr_count($value, ' ') > 1) {
                            $fail('El CI solo puede tener un espacio para la extensión.');
                        }
                    },
                ],
                'nombres' => [
                    'required',
                    'string',
                    'max:70',
                    'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ]+(\s[A-Za-zÁÉÍÓÚáéíóúñÑ]+)?$/', // Solo letras, un espacio
                    function ($attribute, $value, $fail) {
                        if (trim($value) !== $value) {
                            $fail('Los nombres no pueden empezar ni terminar con espacios.');
                        }
                    },
                ],
                'primerApellido' => [
                    'required',
                    'string',
                    'max:70',
                    'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ]+(\s[A-Za-zÁÉÍÓÚáéíóúñÑ]+)?$/', // Solo letras, un espacio
                    function ($attribute, $value, $fail) {
                        if (trim($value) !== $value) {
                            $fail('El primer apellido no puede empezar ni terminar con espacios.');
                        }
                    },
                ],
                'segundoApellido' => [
                    'nullable',
                    'string',
                    'max:70',
                    'regex:/^([A-Za-zÁÉÍÓÚáéíóúñÑ]+(\s[A-Za-zÁÉÍÓÚáéíóúñÑ]+)?)?$/', // Opcional, solo letras, un espacio
                    function ($attribute, $value, $fail) {
                        if ($value && trim($value) !== $value) {
                            $fail('El segundo apellido no puede empezar ni terminar con espacios.');
                        }
                    },
                ],
                'celular' => [
                    'required',
                    'string',
                    'size:8',
                    'regex:/^[6-7][0-9]{7}$/', // Empieza con 6 o 7, 8 dígitos
                    function ($attribute, $value, $fail) {
                        if (trim($value) !== $value) {
                            $fail('El celular no puede contener espacios.');
                        }
                    },
                ],
                'genero' => 'required|in:masculino,femenino', // Selección de género
                'fechaNacimiento' => [
                    'required',
                    'date',
                    'before:today',
                    'before:' . Carbon::now()->subYear()->format('Y-m-d'), // Al menos 1 año de edad
                ],
                'establecimiento' => 'required|exists:establecimiento,id', // Debe existir en la tabla
            ], [
                'ci.regex' => 'El CI debe tener 7-8 dígitos con una extensión opcional (e.g., "1234567 LP").',
                'nombres.regex' => 'Los nombres solo pueden contener letras y un espacio.',
                'primerApellido.regex' => 'El primer apellido solo puede contener letras y un espacio.',
                'segundoApellido.regex' => 'El segundo apellido solo puede contener letras y un espacio si se proporciona.',
                'celular.size' => 'El celular debe tener exactamente 8 dígitos.',
                'celular.regex' => 'El celular debe empezar con 6 o 7 y tener 8 dígitos.',
                'fechaNacimiento.before' => 'La fecha de nacimiento no puede ser futura ni menor a 1 año.'
            ]);

            $genero = ($validatedData['genero'] === 'masculino') ? 'm' : 'f';
            $paciente = new Paciente();
            $paciente->ci = $validatedData['ci'];
            $paciente->nombres = $validatedData['nombres'];
            $paciente->primerApellido = $validatedData['primerApellido'];
            $paciente->segundoApellido = $validatedData['segundoApellido'];
            $paciente->celular = $validatedData['celular'];
            $paciente->sexo = $genero;
            $paciente->fechaNacimiento = Carbon::parse($validatedData['fechaNacimiento']);
            $paciente->nombreUsuario = Carbon::parse($validatedData['fechaNacimiento'])->format('dmY'); // Nombre de usuario basado en fecha
            $paciente->idEstablecimiento = $validatedData['establecimiento'];
            $paciente->estado = '1';
            $paciente->fechaRegistro = Carbon::now();
            $paciente->registradoPor = session('empleado_id');
            $paciente->save();

            $paciente->load('establecimiento');

           return response()->json([
                'success' => true,
                'message' => 'Paciente registrado exitosamente',
                'paciente' => $paciente
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un paciente existente con las validaciones solicitadas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $paciente = Paciente::findOrFail($id);

            $validatedData = $request->validate([
                'nombres' => [
                    'required',
                    'string',
                    'max:70',
                    'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ]+(\s[A-Za-zÁÉÍÓÚáéíóúñÑ]+)?$/', // Solo letras, un espacio
                    function ($attribute, $value, $fail) {
                        if (trim($value) !== $value) {
                            $fail('Los nombres no pueden empezar ni terminar con espacios.');
                        }
                    },
                ],
                'primerApellido' => [
                    'required',
                    'string',
                    'max:70',
                    'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ]+(\s[A-Za-zÁÉÍÓÚáéíóúñÑ]+)?$/', // Solo letras, un espacio
                    function ($attribute, $value, $fail) {
                        if (trim($value) !== $value) {
                            $fail('El primer apellido no puede empezar ni terminar con espacios.');
                        }
                    },
                ],
                'segundoApellido' => [
                    'nullable',
                    'string',
                    'max:70',
                    'regex:/^([A-Za-zÁÉÍÓÚáéíóúñÑ]+(\s[A-Za-zÁÉÍÓÚáéíóúñÑ]+)?)?$/', // Opcional
                    function ($attribute, $value, $fail) {
                        if ($value && trim($value) !== $value) {
                            $fail('El segundo apellido no puede empezar ni terminar con espacios.');
                        }
                    },
                ],
                'celular' => [
                    'required',
                    'string',
                    'size:8',
                    'regex:/^[6-7][0-9]{7}$/', // Empieza con 6 o 7, 8 dígitos
                    function ($attribute, $value, $fail) {
                        if (trim($value) !== $value) {
                            $fail('El celular no puede contener espacios.');
                        }
                    },
                ],
                'sexo' => 'required|in:m,f', // Género M o F
                'fechaNacimiento' => [
                    'required',
                    'date',
                    'before:today',
                    'before:' . Carbon::now()->subYear()->format('Y-m-d'), // Al menos 1 año
                ],
            ], [
                'nombres.regex' => 'Los nombres solo pueden contener letras y un espacio.',
                'primerApellido.regex' => 'El primer apellido solo puede contener letras y un espacio.',
                'segundoApellido.regex' => 'El segundo apellido solo puede contener letras y un espacio si se proporciona.',
                'celular.size' => 'El celular debe tener exactamente 8 dígitos.',
                'celular.regex' => 'El celular debe empezar con 6 o 7 y tener 8 dígitos.',
                'fechaNacimiento.before' => 'La fecha de nacimiento no puede ser futura ni menor a 1 año.'
            ]);

            $paciente->nombres = $validatedData['nombres'];
            $paciente->primerApellido = $validatedData['primerApellido'];
            $paciente->segundoApellido = $validatedData['segundoApellido'];
            $paciente->celular = $validatedData['celular'];
            $paciente->sexo = $validatedData['sexo'];
            $paciente->fechaNacimiento = Carbon::parse($validatedData['fechaNacimiento']);
            $paciente->nombreUsuario = Carbon::parse($validatedData['fechaNacimiento'])->format('dmY'); // Actualiza nombre de usuario
            $paciente->fechaActualizacion = Carbon::now();
            $paciente->save();

            $paciente->load('establecimiento');

            return response()->json([
                'success' => true,
                'message' => 'Paciente actualizado exitosamente',
                'paciente' => $paciente
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra una nueva dosis para un paciente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $patientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDose(Request $request, $patientId)
    {
        try {
            $paciente = Paciente::findOrFail($patientId);
            if (!$paciente || $paciente->estado !== '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente no encontrado o inactivo'
                ], 404);
            }

            $validatedData = $request->validate([
                'nroDosis' => 'required|integer|min:1',
                'fechaGrabacion' => 'required|date',
            ]);

            $assignedDate = $this->getAssignedDate($patientId, $validatedData['nroDosis']);
            $fechaGrabacion = Carbon::parse($validatedData['fechaGrabacion']);
            $today = Carbon::today();

            $estado = '1';
            if ($assignedDate) {
                if ($fechaGrabacion->isSameDay($assignedDate)) {
                    $estado = '2';
                } elseif ($fechaGrabacion->gt($assignedDate)) {
                    $estado = '3';
                }
            }

            $dosis = new Dosi();
            $dosis->idPaciente = $patientId;
            $dosis->nroDosis = $validatedData['nroDosis'];
            $dosis->rutaVideo = null;
            $dosis->fechaGrabacion = $fechaGrabacion;
            $dosis->descripcion = null;
            $dosis->estado = $estado;
            $dosis->registradoPor = session('empleado_id') ?? 1; // Valor por defecto si no hay sesión
            $dosis->fechaRegistro = Carbon::now();
            $dosis->fechaActualizacion = null;
            $dosis->save();

            Notificacion::create([
                'id_usuario' => $patientId,
                'tipo_usuario' => 'P',
                'titulo' => 'RECORDATORIO',
                'mensaje' => 'Debes completar la dosis #' . $dosis->nroDosis . ', por favor.',
                'leido_en' => null,
                'creado_en' => now(),
                'actualizado_en' => null,
                'idDosis' => $dosis->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dosis creada exitosamente',
                'dosis' => [
                    'id' => $dosis->id,
                    'nroDosis' => $dosis->nroDosis,
                    'fechaGrabacion' => $dosis->fechaGrabacion->format('Y-m-d'),
                    'estado' => $dosis->estado,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la dosis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina una dosis cambiando su estado a 0.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDose($id)
    {
        try {
            $dosis = Dosi::findOrFail($id);
            $dosis->estado = '0';
            $dosis->fechaActualizacion = Carbon::now();
            $dosis->save();

            return response()->json([
                'success' => true,
                'message' => 'Dosis eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la dosis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sube un video para una dosis específica.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $doseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDoseVideo(Request $request, $doseId)
    {
        try {
            $dosis = Dosi::findOrFail($doseId);
            
            $validatedData = $request->validate([
                'video' => 'required|file|mimes:mp4,mov,avi|max:102400', // Max 100MB
            ]);

            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $nombrePaciente = str_replace(' ', '_', trim("{$dosis->paciente->nombres}_{$dosis->paciente->primerApellido}_{$dosis->paciente->segundoApellido}"));
                $extension = $file->getClientOriginalExtension();
                $fileName = "dose_{$dosis->id}_{$nombrePaciente}_" . time() . ".{$extension}";
                
                $path = $file->storeAs('videos', $fileName, 'public');
                
                $dosis->rutaVideo = $path;
                $dosis->fechaActualizacion = Carbon::now();
                $dosis->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Video subido exitosamente',
                    'rutaVideo' => asset('storage/' . $path)
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se proporcionó un video válido'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el video: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene la fecha asignada para una dosis (método auxiliar).
     *
     * @param  int  $patientId
     * @param  int  $nroDosis
     * @return \Carbon\Carbon|null
     */
    private function getAssignedDate($patientId, $nroDosis)
    {
        return null; // Implementar lógica si es necesario
    }
    
   
    /**
 * Transfiere un paciente a un nuevo establecimiento con un documento.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id
 * @return \Illuminate\Http\JsonResponse
 */
 public function transfer(Request $request, $id)
    {
        try {
            Log::info('Starting transfer for patient ID: ' . $id, ['request' => $request->all()]);

            $validatedData = $request->validate([
                'newEstablishmentId' => 'required|exists:establecimiento,id',
                'document' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            Log::info('Validated data: ', $validatedData);

            $paciente = Paciente::findOrFail($id);
            if ($paciente->estado !== '1') {
                Log::warning('Patient inactive: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'El paciente está inactivo'
                ], 400);
            }

            $currentEstablishment = Establecimiento::findOrFail($paciente->idEstablecimiento);
            $newEstablishment = Establecimiento::findOrFail($validatedData['newEstablishmentId']);

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $nombrePaciente = str_replace(' ', '_', trim("{$paciente->nombres}_{$paciente->primerApellido}_{$paciente->segundoApellido}"));
                $extension = $file->getClientOriginalExtension();
                $fileName = "{$nombrePaciente}_" . time() . ".{$extension}";

                Log::info('Saving file: ' . $fileName);
                $path = $file->storeAs('transferencias', $fileName, 'public');

                $historial = new Historialmedico();
                $historial->idPaciente = $paciente->id;
                $historial->descripcion = "Transferencia de {$currentEstablishment->nombre} a {$newEstablishment->nombre}";
                $historial->Origen = $currentEstablishment->nombre;
                $historial->Destino = $newEstablishment->nombre;
                $historial->imagen = $path;
                $historial->idEstablecimiento = $validatedData['newEstablishmentId'];
                $historial->estado = '1';
                $historial->fechaRegistro = Carbon::now();
                $historial->registradoPor = 1;
                $historial->save();

                $paciente->idEstablecimiento = $validatedData['newEstablishmentId'];
                $paciente->fechaActualizacion = Carbon::now();
                $paciente->save();

                Log::info('Transfer completed for patient ID: ' . $id);
                return response()->json([
                    'success' => true,
                    'message' => 'Paciente transferido exitosamente',
                    'transferencia' => [
                        'id' => $historial->id,
                        'descripcion' => $historial->descripcion,
                        'Origen' => $historial->Origen,
                        'Destino' => $historial->Destino,
                        'fechaRegistro' => $historial->fechaRegistro->format('Y-m-d H:i:s'),
                        'imagen' => asset('storage/' . $path)
                    ]
                ], 200);
            }

            Log::warning('No valid document provided for patient ID: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'No se proporcionó un documento válido'
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Transfer error for patient ID: ' . $id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al transferir el paciente: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Obtiene el historial de transferencias de un paciente.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
public function getTransferHistory($id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $transferencias = Historialmedico::where('idPaciente', $id)
                ->where('estado', '1')
                ->select('id', 'descripcion', 'Origen', 'Destino', 'fechaRegistro', 'imagen')
                ->orderBy('fechaRegistro', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'transferencias' => $transferencias->map(function ($transferencia) {
                    return [
                        'id' => $transferencia->id,
                        'descripcion' => $transferencia->descripcion,
                        'Origen' => $transferencia->Origen,
                        'Destino' => $transferencia->Destino,
                        'fechaRegistro' => $transferencia->fechaRegistro->format('Y-m-d H:i:s'),
                        'imagen' => $transferencia->imagen ? asset('storage/' . $transferencia->imagen) : null
                    ];
                }),
                'paciente' => [
                    'id' => $paciente->id,
                    'nombreCompleto' => "{$paciente->nombres} {$paciente->primerApellido} {$paciente->segundoApellido}"
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial de transferencias: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Obtiene la lista de establecimientos activos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEstablishments()
    {
        try {
            $establecimientos = Establecimiento::where('estado', '1')
                ->select('id', 'nombre')
                ->get();
            
            return response()->json($establecimientos);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar establecimientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactiva un paciente cambiando su estado a 0.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->estado = '0';
            $paciente->save();

            return response()->json([
                'success' => true,
                'message' => 'Paciente desactivado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar el paciente: ' . $e->getMessage()
            ], 500);
        }
    }
}