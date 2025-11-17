<?php

use Illuminate\Support\Facades\Route;

// Controladores API
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Todas las rutas para la app móvil (empleados roles 3 y 4)
| Se manejan desde aquí porque Laravel 12 ya no trae routes/api.php
|--------------------------------------------------------------------------
*/

Route::prefix('api')
    ->middleware('api')
    ->group(function () {

        // 🔐 LOGIN API
        Route::post('/login', [AuthController::class, 'login']);

        // 🔐 logout (token)
        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth:sanctum');

        // 👤 Datos del usuario autenticado
        Route::get('/me', [AuthController::class, 'me'])
            ->middleware('auth:sanctum');

        // 📅 SCHEDULE DEL DÍA
        Route::get('/schedules/today', [ScheduleController::class, 'today'])
            ->middleware('auth:sanctum');

        // Schedules de la semana actual del usuario
        Route::get('/schedules/week', [ScheduleController::class, 'week'])
            ->middleware('auth:sanctum');


        // 🕒 ASISTENCIA (CHECK-IN / CHECK-OUT)
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])
            ->middleware('auth:sanctum');

        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])
            ->middleware('auth:sanctum');
    });

/*
|--------------------------------------------------------------------------
| Rutas WEB (si necesitas algo visual)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return response()->json([
        'message' => 'GeoGo API (Laravel 12) funcionando correctamente'
    ]);
});
