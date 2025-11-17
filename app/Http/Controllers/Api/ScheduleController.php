<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Devuelve los schedules de HOY para el usuario autenticado.
     */
    public function today(Request $request)
    {
        $user  = $request->user();
        $today = Carbon::today()->toDateString();

        $schedules = Schedule::with('branch')
            ->where('user_id', $user->id)
            ->where('work_date', $today)
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'date'      => $today,
            'user_id'   => $user->id,
            'schedules' => $schedules,
        ]);
    }

    public function week(Request $request)
    {
        $user = $request->user();

        // Lunes de esta semana
        $startOfWeek = Carbon::today()->startOfWeek(Carbon::MONDAY)->toDateString();
        // Domingo de esta semana
        $endOfWeek   = Carbon::today()->endOfWeek(Carbon::SUNDAY)->toDateString();

        $schedules = Schedule::with('branch')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfWeek, $endOfWeek])
            ->orderBy('work_date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'user_id'      => $user->id,
            'start_of_week'=> $startOfWeek,
            'end_of_week'  => $endOfWeek,
            'schedules'    => $schedules,
        ]);
    }
}
