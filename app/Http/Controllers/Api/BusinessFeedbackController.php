<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessFeedback;
use App\Models\Business;
use App\Models\Reservation;

class BusinessFeedbackController extends Controller
{
    // 🔹 Listar feedbacks (propietario: los suyos / cliente: los que hizo)
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'owner') {
            // Feedbacks de negocios del propietario
            $businessIds = Business::where('user_id', $user->id)->pluck('id');
            $feedbacks = BusinessFeedback::with(['user', 'business'])
                ->whereIn('fk_business', $businessIds)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Feedbacks del cliente
            $feedbacks = BusinessFeedback::with(['business'])
                ->where('fk_user', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json($feedbacks);
    }

    // 🔹 Crear feedback (solo cliente)
    public function store(Request $request)
    {
        $request->validate([
            'fk_business' => 'required|exists:businesses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stars' => 'required|integer|min:1|max:5',
        ]);

        $user = $request->user();
        if ($user->role !== 'client') {
            return response()->json(['error' => 'Solo los clientes pueden dejar feedback'], 403);
        }

        // Verificar si el cliente ha tenido alguna reserva completada en ese negocio
        $hasCompletedReservation = Reservation::where('fk_user_client', $user->id)
            ->whereHas('service', function ($query) use ($request) {
                $query->where('business_id', $request->fk_business);
            })
            ->where('status', 'completed')
            ->exists();

        if (!$hasCompletedReservation) {
            return response()->json(['error' => 'Solo puedes valorar negocios donde hayas completado una reserva'], 403);
        }

        // Evitar duplicar feedbacks para el mismo negocio
        $alreadyExists = BusinessFeedback::where('fk_business', $request->fk_business)
            ->where('fk_user', $user->id)
            ->exists();

        if ($alreadyExists) {
            return response()->json(['error' => 'Ya has dejado una valoración para este negocio'], 400);
        }

        $feedback = BusinessFeedback::create([
            'fk_business' => $request->fk_business,
            'fk_user' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'stars' => $request->stars,
        ]);

        return response()->json([
            'message' => 'Feedback enviado correctamente',
            'feedback' => $feedback,
        ], 201);
    }

    // 🔹 Mostrar un feedback concreto
    public function show($id)
    {
        $feedback = BusinessFeedback::with(['business', 'user'])->find($id);

        if (!$feedback) {
            return response()->json(['error' => 'Feedback no encontrado'], 404);
        }

        return response()->json($feedback);
    }

    // 🔹 Eliminar feedback (solo propietario o cliente que lo creó)
    public function destroy(Request $request, $id)
    {
        $feedback = BusinessFeedback::find($id);

        if (!$feedback) {
            return response()->json(['error' => 'Feedback no encontrado'], 404);
        }

        $user = $request->user();
        $isOwner = Business::where('id', $feedback->fk_business)
            ->where('user_id', $user->id)
            ->exists();

        if ($user->id !== $feedback->fk_user && !$isOwner) {
            return response()->json(['error' => 'No autorizado para eliminar este feedback'], 403);
        }

        $feedback->delete();

        return response()->json(['message' => 'Feedback eliminado correctamente']);
    }
}
