<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacion;

class NotificacionController extends Controller
{
    public function pendientes(Request $request)
    {
        $usuario = $request->user();
    
        if (!$usuario) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $nuevas = Notificacion::where('id_usuario', $usuario->id)
            ->where('tipo_usuario', 'P')
            ->whereNull('leido_en')
            ->orderByDesc('id')
            ->get();
    
     
        $leidas = Notificacion::where('id_usuario', $usuario->id)
            ->where('tipo_usuario', 'P')
            ->whereNotNull('leido_en')
            ->orderByDesc('leido_en')
            ->limit(5)
            ->get();
    
     
        $todas = $nuevas->concat($leidas)->values();
    
        return response()->json($todas);
    }
    

        public function marcarLeidas(Request $request)
        {
            $usuario = $request->user();

            if (!$usuario) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            Notificacion::where('id_usuario', $usuario->id)
                ->where('tipo_usuario', 'P')
                ->whereNull('leido_en')
                ->update(['leido_en' => now()]);

            return response()->json(['message' => 'Notificaciones marcadas como leídas']);
        }

}
