<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;

class BusinessController extends Controller
{
    // 🔹 Crear un negocio
    public function store(Request $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'coordinates' => 'nullable|string',
            'logo' => 'nullable|string',
            'images' => 'nullable|array',
            'description' => 'nullable|string',
            'schedule' => 'nullable|array',
            'address' => 'nullable|string',
            'aforo' => 'nullable|integer',
        ]);

        $business = Business::create([
            'user_id' => $request->user()->id,
            ...$validated
        ]);

        return response()->json(['message' => 'Negocio creado correctamente', 'business' => $business], 201);
    }

    // 🔹 Listar negocios del propietario autenticado
    public function index(Request $request)
    {
        $businesses = Business::where('user_id', $request->user()->id)->get();
        return response()->json($businesses);
    }

    // 🔹 Ver negocio específico
    public function show($id)
    {
        $business = Business::find($id);
        if (!$business) {
            return response()->json(['error' => 'Negocio no encontrado'], 404);
        }
        return response()->json($business);
    }

    // 🔹 Actualizar negocio
    public function update(Request $request, $id)
    {
        $business = Business::find($id);
        if (!$business) {
            return response()->json(['error' => 'Negocio no encontrado'], 404);
        }

        if ($request->user()->id !== $business->user_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:100',
            'coordinates' => 'nullable|string',
            'logo' => 'nullable|string',
            'images' => 'nullable|array',
            'description' => 'nullable|string',
            'schedule' => 'nullable|array',
            'address' => 'nullable|string',
            'aforo' => 'nullable|integer',
        ]);

        $business->update($validated);
        return response()->json(['message' => 'Negocio actualizado correctamente', 'business' => $business]);
    }
    public function destroy(Request $request, $id)
    {
        // Verificar que el usuario sea propietario
        if ($request->user()->role !== 'owner') {
            return response()->json(['error' => 'Solo los propietarios pueden eliminar negocios'], 403);
        }

        // Buscar el negocio
        $business = Business::find($id);
        if (!$business) {
            return response()->json(['error' => 'Negocio no encontrado'], 404);
        }

        // Verificar que el negocio pertenece al propietario autenticado
        if ($business->user_id !== $request->user()->id) {
            return response()->json(['error' => 'No tienes permiso para eliminar este negocio'], 403);
        }

        // Eliminar negocio
        $business->delete();

        return response()->json(['message' => 'Negocio eliminado correctamente']);
    }
}
