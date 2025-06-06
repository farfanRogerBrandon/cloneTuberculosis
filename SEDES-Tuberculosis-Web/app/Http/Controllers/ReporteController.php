<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Establecimiento;
use App\Models\Provincium; // Adjust to Provincium if that's the correct model name
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Generate a report of the number of active patients per establishment, optionally filtered by province or establishment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function patientsByEstablishment(Request $request)
    {
        try {
            Log::info('Generating patients by establishment report', [
                'idProvincia' => $request->query('idProvincia'),
                'idEstablecimiento' => $request->query('idEstablecimiento')
            ]);

            $query = Paciente::select(
                'establecimiento.id',
                'establecimiento.nombre as establishment_name',
                DB::raw('COUNT(paciente.id) as patient_count')
            )
                ->join('establecimiento', 'paciente.idEstablecimiento', '=', 'establecimiento.id')
                ->where('paciente.estado', '1') // Adjust to 1 if estado is an integer
                ->where('establecimiento.estado', '1'); // Adjust to 1 if estado is an integer

            // Filter by province if provided
            if ($request->has('idProvincia') && $request->query('idProvincia') !== '') {
                if (!Provincium::where('id', $request->query('idProvincia'))->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Provincia no encontrada'
                    ], 404);
                }
                $query->where('establecimiento.idProvincia', $request->query('idProvincia'));
            }

            // Filter by establishment if provided
            if ($request->has('idEstablecimiento') && $request->query('idEstablecimiento') !== '') {
                if (!Establecimiento::where('id', $request->query('idEstablecimiento'))->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Establecimiento no encontrado'
                    ], 404);
                }
                $query->where('establecimiento.id', $request->query('idEstablecimiento'));
            }

            $report = $query->groupBy('establecimiento.id', 'establecimiento.nombre')
                ->orderBy('establecimiento.nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Reporte generado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error generating patients by establishment report: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active provinces for the filter dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProvinces()
    {
        try {
            Log::info('Fetching active provinces');

            $provinces = Provincium::select('id', 'nombre')
                ->where('estado', '1') // Adjust to 1 if estado is an integer
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $provinces,
                'message' => 'Provincias obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching provinces: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las provincias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active establishments for the filter dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEstablishments()
    {
        try {
            Log::info('Fetching active establishments');

            $establishments = Establecimiento::select('id', 'nombre')
                ->where('estado', '1') // Adjust to 1 if estado is an integer
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $establishments,
                'message' => 'Establecimientos obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching establishments: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los establecimientos: ' . $e->getMessage()
            ], 500);
        }
    }
}