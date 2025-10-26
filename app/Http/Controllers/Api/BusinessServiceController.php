<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessService;
use App\Models\Business;

class BusinessServiceController extends Controller
{
    // 🔹 Listar servicios de un negocio
    public function index(Request $request, $businessId)
    {
        $business = Business::find($businessId);
        if (!$business) {
            return response()->json(['error' => 'Negocio no encontrado'], 404);
        }

        // 🔒 Solo el propietario puede ver sus servicios (opcional)
        if ($request->user()->id !== $business->user_id || $request->user()->role !== 'owner') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $services = BusinessService::where('business_id', $businessId)->get();
        return response()->json($services);
    }

    // 🔹 Crear un nuevo servicio (solo si el negocio pertenece al propietario)
    public function store(Request $request, $businessId)
    {
        $business = Business::find($businessId);

        if (!$business) {
            return response()->json(['error' => 'Negocio no encontrado'], 404);
        }

        // 🔒 Validar propiedad del negocio
        if ($request->user()->id !== $business->user_id || $request->user()->role !== 'owner') {
            return response()->json(['error' => 'No autorizado para crear servicios en este negocio'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'time_estimation' => 'required|integer|min:5',
            'aforo' => 'nullable|integer|min:1'
        ]);

        $service = BusinessService::create([
            'business_id' => $business->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'time_estimation' => $request->time_estimation,
            'aforo' => $request->aforo,
        ]);

        return response()->json([
            'message' => 'Servicio creado correctamente',
            'service' => $service
        ], 201);
    }

    // 🔹 Mostrar un servicio concreto
    public function show(Request $request, $id)
    {
        $service = BusinessService::find($id);
        if (!$service) {
            return response()->json(['error' => 'Servicio no encontrado'], 404);
        }

        // 🔒 Solo el propietario del negocio puede acceder al detalle
        if ($request->user()->id !== $service->business->user_id || $request->user()->role !== 'owner') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($service);
    }

    // 🔹 Actualizar un servicio (solo si el negocio es del propietario)
    public function update(Request $request, $id)
    {
        $service = BusinessService::find($id);
        if (!$service) {
            return response()->json(['error' => 'Servicio no encontrado'], 404);
        }

        $business = $service->business;

        // 🔒 Validar propiedad del negocio
        if ($request->user()->id !== $business->user_id || $request->user()->role !== 'owner') {
            return response()->json(['error' => 'No autorizado para actualizar este servicio'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'time_estimation' => 'sometimes|integer|min:5',
            'aforo' => 'nullable|integer|min:1'
        ]);

        $service->update($request->only(['name', 'description', 'price', 'time_estimation', 'aforo']));

        return response()->json([
            'message' => 'Servicio actualizado correctamente',
            'service' => $service
        ]);
    }

    // 🔹 Eliminar un servicio
    public function destroy(Request $request, $id)
    {
        $service = BusinessService::find($id);
        if (!$service) {
            return response()->json(['error' => 'Servicio no encontrado'], 404);
        }

        $business = $service->business;

        // 🔒 Validar propiedad
        if ($request->user()->id !== $business->user_id || $request->user()->role !== 'owner') {
            return response()->json(['error' => 'No autorizado para eliminar este servicio'], 403);
        }

        $service->delete();
        return response()->json(['message' => 'Servicio eliminado correctamente']);
    }
}
