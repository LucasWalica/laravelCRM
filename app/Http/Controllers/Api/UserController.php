<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        // Buscar usuario existente (por teléfono o email)
        $existingUser = User::where('email', $request->email)
                            ->orWhere('phone', $request->phone)
                            ->first();

        if ($existingUser) {
            // Si fue creado por un owner, completamos los datos del cliente
            $existingUser->update([
                'name' => $request->name ?? $existingUser->name,
                'surname1' => $request->surname1 ?? $existingUser->surname1,
                'surname2' => $request->surname2 ?? $existingUser->surname2,
                'password' => Hash::make($request->password),
                'created_by' => 'client',
            ]);
            $user = $existingUser;
        } else {
            // Crear nuevo cliente
            $user = User::create([
                'name' => $request->name,
                'surname1' => $request->surname1,
                'surname2' => $request->surname2,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'client',
                'created_by' => 'client',
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function createClient(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
        ]);

        $user = User::create([
            'name' => $request->name,
            'surname1' => $request->surname1,
            'surname2' => $request->surname2,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'client',
            'created_by' => 'owner',
            'password' => Hash::make(uniqid()), // password temporal
        ]);

        return response()->json([
            'message' => 'Cliente creado correctamente',
            'user' => $user
        ]);
    }

    public function createClientInternal(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
        ]);

        $user = User::create([
            'name' => $request->name,
            'surname1' => $request->surname1,
            'surname2' => $request->surname2,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'client',
            'created_by' => 'client',
            'password' => Hash::make(uniqid()), // password temporal
        ]);

        return response()->json([
            'message' => 'Cliente creado correctamente',
            'user' => $user
        ]);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'password' => 'required',
        ]);

        $user = User::when($request->email, fn($q) => $q->where('email', $request->email))
                    ->when($request->phone, fn($q) => $q->orWhere('phone', $request->phone))
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'auth' => ['Credenciales incorrectas'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}


