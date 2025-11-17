<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Registrar check-in para un schedule del día actual.
     */
    
    public function checkIn(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'lat'         => 'required|numeric',
            'lng'         => 'required|numeric',
        ]);

        $user = $request->user();
        $schedule = Schedule::with('branch')->findOrFail($request->schedule_id);

        // Verificar que el schedule pertenezca al usuario
        if ($schedule->user_id !== $user->id) {
            return response()->json([
                'message' => 'El horario no pertenece al usuario autenticado',
            ], 403);
        }

        // Verificar que sea para HOY
        $today = Carbon::today()->toDateString();
        $work_date = $schedule->work_date;
        // $work_date = '2025-11-17';
        if ($work_date !== $today) {
            return response()->json([
                'message' => 'El horario no corresponde al día de hoy',
            ], 422);
        }

        // ✅ Validar geolocalización
        $branch = $schedule->branch;

        if ($branch && $branch->lat && $branch->lng && $branch->radius) {
            $distance = $this->distanceInMeters(
                (float) $request->lat,
                (float) $request->lng,
                (float) $branch->lat,
                (float) $branch->lng,
            );

            if ($distance > (float) $branch->radius) {
                return response()->json([
                    'message'  => 'Estás fuera del rango permitido para marcar asistencia',
                    'distance' => round($distance, 2),
                    'radius'   => (float) $branch->radius,
                    'unit'     => 'meters',
                ], 422);
            }
        }

        // Buscar asistencia existente
        $attendance = Attendance::where('schedule_id', $schedule->id)
            ->where('user_id', $user->id)
            ->first();

        if ($attendance && $attendance->check_in_at) {
            return response()->json([
                'message' => 'El check-in ya fue registrado',
            ], 422);
        }

        if (! $attendance) {
            $attendance = new Attendance();
            $attendance->company_id  = $schedule->company_id;
            $attendance->branch_id   = $schedule->branch_id;
            $attendance->user_id     = $user->id;
            $attendance->schedule_id = $schedule->id;
        }

        $attendance->check_in_at  = Carbon::now();
        $attendance->check_in_lat = $request->lat;
        $attendance->check_in_lng = $request->lng;
        $attendance->status       = 'present';
        $attendance->save();

        return response()->json([
            'message'    => 'Check-in registrado correctamente',
            'attendance' => $attendance,
        ]);
    }


    /**
     * Registrar check-out para un schedule del día actual.
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'lat'         => 'required|numeric',
            'lng'         => 'required|numeric',
        ]);

        $user = $request->user();
        $schedule = Schedule::with('branch')->findOrFail($request->schedule_id);

        if ($schedule->user_id !== $user->id) {
            return response()->json([
                'message' => 'El horario no pertenece al usuario autenticado',
            ], 403);
        }

        $today = Carbon::today()->toDateString();
        if ($schedule->work_date !== $today) {
            return response()->json([
                'message' => 'El horario no corresponde al día de hoy',
            ], 422);
        }

        // ✅ Validar geolocalización al hacer check-out
        $branch = $schedule->branch;

        if ($branch && $branch->lat && $branch->lng && $branch->radius) {
            $distance = $this->distanceInMeters(
                (float) $request->lat,
                (float) $request->lng,
                (float) $branch->lat,
                (float) $branch->lng,
            );

            if ($distance > (float) $branch->radius) {
                return response()->json([
                    'message'  => 'Estás fuera del rango permitido para marcar salida',
                    'distance' => round($distance, 2),
                    'radius'   => (float) $branch->radius,
                    'unit'     => 'meters',
                ], 422);
            }
        }

        $attendance = Attendance::where('schedule_id', $schedule->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $attendance || ! $attendance->check_in_at) {
            return response()->json([
                'message' => 'No se ha registrado check-in para este horario',
            ], 422);
        }

        if ($attendance->check_out_at) {
            return response()->json([
                'message' => 'El check-out ya fue registrado',
            ], 422);
        }

        $attendance->check_out_at  = Carbon::now();
        $attendance->check_out_lat = $request->lat;
        $attendance->check_out_lng = $request->lng;
        $attendance->save();

        return response()->json([
            'message'    => 'Check-out registrado correctamente',
            'attendance' => $attendance,
        ]);
    }


    /**
     * Calcula la distancia en metros entre dos puntos (lat/lng) usando la fórmula de Haversine.
     */
    private function distanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        // Radio de la tierra en metros
        $earthRadius = 6371000;

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2 +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

}
