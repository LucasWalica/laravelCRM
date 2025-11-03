<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\BusinessService;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Resources\ReservationResource;

class ReservationController extends Controller
{
    // 🔹 Listar reservas del usuario autenticado (cliente o propietario)
    public function index(Request $request)
    {
        if ($request->user()->role === 'client') {
            $reservations = Reservation::with(['service.business'])
                ->where('fk_user_client', $request->user()->id)
                ->orderBy('time_start', 'desc')
                ->get();
        } else {
            // Propietario: ver todas las reservas de sus negocios
            $businessIds = Business::where('user_id', $request->user()->id)->pluck('id');
            $serviceIds = BusinessService::whereIn('business_id', $businessIds)->pluck('id');

            $reservations = Reservation::with(['client', 'service'])
                ->whereIn('fk_business_service', $serviceIds)
                ->orderBy('time_start', 'desc')
                ->get();
        }

        return ReservationResource::collection($reservations);
    }

    // 🔹 Crear reserva (por cliente o propietario)
    public function store(Request $request)
    {
        $request->validate([
            'fk_business_service' => 'required|exists:business_services,id',
            'time_start' => 'required|date|after:now',
        ]);

        $service = BusinessService::find($request->fk_business_service);
        $business = $service->business;

        // Si la reserva la crea un propietario, puede hacerlo por un cliente específico
        $clientId = $request->user()->role === 'owner'
            ? $request->input('fk_user_client')
            : $request->user()->id;

        if ($request->user()->role === 'owner' && !$clientId) {
            return response()->json(['error' => 'Debe indicar el cliente para la reserva'], 400);
        }

        // Validar aforo
        $existing = Reservation::where('fk_business_service', $service->id)
            ->where('time_start', $request->time_start)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        if ($service->aforo && $existing >= $service->aforo) {
            return response()->json(['error' => 'Aforo completo para esta hora'], 400);
        }


        $reservation = Reservation::create([
            'fk_user_client' => $clientId,
            'fk_business_service' => $service->id,
            'time_start' => $request->time_start,
            'estimated_time_end' => $request->estimated_time_end ?? null,
            'status' => 'pending',
            'aforo' => $service->aforo,
        ]);

        return response()->json([
            'message' => 'Reserva creada correctamente',
            'reservation' => $reservation,
        ], 201);
    }   

    // crear reserva para user no registrado
    public function storePublic(Request $request)
    {
        $request->validate([
            'client_email' => 'nullable|email',
            'client_phone' => 'nullable|string',
            'client_name' => 'nullable|string',
            'fk_business_service' => 'required|exists:business_services,id',
            'time_start' => 'required|date|after:now',
        ]);

        $service = BusinessService::findOrFail($request->fk_business_service);

        // Crear o recuperar usuario "cliente" basado en su email o teléfono
        $client = User::firstOrCreate(
            ['email' => $request->client_email],
            [
                'name' => $request->client_name ?? 'Cliente invitado',
                'phone' => $request->client_phone,
                'password' => bcrypt(Str::random(10)),
                'role' => 'client',
                'created_by' => 'anonymous',
            ]
        );

        // Crear token único de acceso
        $token = Str::uuid();

        $reservation = Reservation::create([
            'fk_user_client' => $client->id,
            'fk_business_service' => $service->id,
            'time_start' => $request->time_start,
            'status' => 'pending',
            'token' => $token,
            'token_expires_at' => now()->addDays(3),
        ]);

        return response()->json([
            'message' => 'Reserva creada correctamente',
            'reservation' => $reservation,
        ]);
    }

    // 🔹 Mostrar una reserva concreta
    public function show(Request $request, $id)
    {
        $reservation = Reservation::with(['client', 'service.business'])->find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        // Autorización
        $user = $request->user();
        $ownerId = $reservation->service->business->user_id;

        if ($user->role === 'client' && $reservation->fk_user_client !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($user->role === 'owner' && $user->id !== $ownerId) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return ReservationResource::collection($reservation);
    }

    // 🔹 Actualizar estado (solo propietario)
    public function update(Request $request, $id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        $user = $request->user();
        $ownerId = $reservation->service->business->user_id;

        if ($user->role !== 'owner' || $user->id !== $ownerId) {
            return response()->json(['error' => 'Solo el propietario puede actualizar reservas'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed,no_show',
        ]);

        $reservation->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Reserva actualizada correctamente',
            'reservation' => $reservation,
        ]);
    }

    public function updatePublic(Request $request, string $token)
{
    // Buscar reserva por token y comprobar que no esté expirado
    $reservation = Reservation::where('token', $token)
        ->where('token_expires_at', '>', now())
        ->first();

    if (!$reservation) {
        return response()->json(['error' => 'Token inválido o expirado'], 403);
    }

    // Validar nuevo estado
    $request->validate([
        'status' => 'required|in:confirmed,cancelled',
    ]);

    // Actualizar estado
    $reservation->update([
        'status' => $request->status,
    ]);

    return response()->json([
        'message' => 'Estado de la reserva actualizado correctamente.',
        'reservation' => $reservation,
    ]);
}

    // 🔹 Cancelar reserva (cliente o propietario)
    public function cancel(Request $request, $id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        $user = $request->user();
        $ownerId = $reservation->service->business->user_id;

        if ($user->role === 'client' && $reservation->fk_user_client !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($user->role === 'owner' && $user->id !== $ownerId) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $reservation->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Reserva cancelada correctamente']);
    }

    // 🔹 Eliminar reserva (solo propietario)
    public function destroy(Request $request, $id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        $user = $request->user();
        $ownerId = $reservation->service->business->user_id;

        if ($user->role !== 'owner' || $user->id !== $ownerId) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $reservation->delete();
        return response()->json(['message' => 'Reserva eliminada correctamente']);
    }
}