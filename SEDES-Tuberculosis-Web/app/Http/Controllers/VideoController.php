<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Dosi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VideoController extends Controller{ 
    // DENTRO DEL DIRECTORIO 
    public function edit(Request $request, $id)
    {
        $paciente = $request->user();
    
        $request->validate([
            'video' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mov,video/mpeg|max:51200', // hasta 50 MB
        ]);
    
        $video = Dosi::find($id);
        if (!$video) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
    
        $hoy = now()->toDateString();  // solo la fecha en formato YYYY-MM-DD
        $ahora = now();                // instancia de Carbon con fecha y hora actuales
        $dentroHorario = false;

        if ($video->fechaGrabacion && Carbon::parse($video->fechaGrabacion)->toDateString() === $hoy) {
            $dentroHorario = true;
        }

        $nota = $dentroHorario
            ? 'Enviado a tiempo. ' . $ahora->format('H:i:s')
            : 'Enviado fuera de la fecha establecida. ' . $ahora->format('H:i:s');

        $video->estado = $dentroHorario ? 2 : 3;

    
        // LOG: Verificamos si llegó archivo
        if ($request->hasFile('video')) {
            Log::debug('📹 Se recibió un archivo de video');
    
            $archivo = $request->file('video');
    
            if (!$archivo->isValid()) {
                Log::error('❌ El archivo no es válido');
                return response()->json(['message' => 'Archivo no válido'], 422);
            }
    
            $nombre = $video->id . '_' .
                preg_replace('/\s+/', '', ($paciente->nombres ?? 'Nombre') . ($paciente->primerApellido ?? 'Apellido')) .
                '.' . $archivo->getClientOriginalExtension();
    
            try {
                // Opción más segura: guardar en storage/app/public/videos
                $ruta = $archivo->storeAs('videos', $nombre, 'public');

                Log::debug("✅ Archivo guardado como: $ruta");
    
                // Obtener URL pública
                $video->rutaVideo = Storage::url($ruta); // Devuelve /storage/videos/archivo.mp4
            } catch (\Exception $e) {
                Log::error('❌ Error al guardar el archivo: ' . $e->getMessage());
                return response()->json(['message' => 'Error al guardar el archivo', 'error' => $e->getMessage()], 500);
            }
        } else {
            Log::warning('⚠️ No se recibió archivo de video en la solicitud');
        }
    
        $video->descripcion = $nota;
        $video->fechaActualizacion = $ahora;
        $video->save();
    
        return response()->json([
            'message' => 'Video actualizado correctamente',
            'data' => $video
        ]);
    }


    public function obtenerDosis(Request $request)
    {
        $paciente = $request->user();

        $nombreEstablecimiento = $paciente->establecimiento ? $paciente->establecimiento->nombre : 'Sin establecimiento';

        // Obtener todas las dosis activas del paciente con estado 1 (pendientes) o 3 (retraso)
        $dosis = Dosi::where('idPaciente', $paciente->id)
            ->whereIn('estado', [1, 3]) // 🔥 Buscar tanto pendientes como retrasos
            ->get(['id','nroDosis', 'fechaGrabacion', 'estado']);

        $resultado = $dosis->map(function ($dosi) use ($nombreEstablecimiento) {
            return [
                'id' => $dosi->id,
                'nroDosis' => $dosi->nroDosis,
                'fechaGrabacion' => $dosi->fechaGrabacion->format("Y-m-d H:i:s"),
                'estado' => $dosi->estado, // 🔥 importante incluirlo
                'nombreEstablecimiento' => $nombreEstablecimiento,
            ];
        });

        return response()->json($resultado);
    }
   
    public function logoutPaciente(Request $request)
    {
    // Elimina el token que se usó en la solicitud
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function getVideoUrl($id)
    {
        $dosis = Dosi::findOrFail($id);

        // rutaVideo ya es como: /storage/videos/3_AnettGarcia.mp4
        $rutaRelativa = $dosis->rutaVideo;

        // Construir la URL absoluta
        $url = url($rutaRelativa); // equivale a: http://tudominio.com/storage/videos/3_AnettGarcia.mp4

        return response()->json([
            'url' => $url
        ]);
    }
 
}