<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $role_student = Role::where('name', 'student')->first();
        if (!$role_student) {
            return $this->error('Role not found', 404);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $role_student->id, // Asignar el ID del rol al nuevo usuario
        ]);

        // Crear el token con un tiempo de expiración
        $token = $user->createToken('auth_token');
        $expiresAt = now()->addHours(1); // Define el tiempo de expiración (1 hora en este caso)

        // Actualizar la columna expires_at en la tabla personal_access_tokens
        $token->accessToken->expires_at = $expiresAt;
        $token->accessToken->save();

        $user = User::where('id', $user->id)->with('role')->first(); // Cargar la relación 'role'
        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->success([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toDateTimeString(),
            'user' => $user
        ], 'User registered successfully');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', 401);
        }
        $user = $user->load('role'); // Cargar la relación 'role'


        // Crear el token con un tiempo de expiración
        $token = $user->createToken('auth_token');
        // $expiresAt = now()->addHours(1); // Define el tiempo de expiración (1 hora en este caso)
        //30 minutos
        $expiresAt = now()->addMinutes(30); // Define el tiempo de expiración (30 minutos en este caso)

        // Actualizar la columna expires_at en la tabla personal_access_tokens
        $token->accessToken->expires_at = $expiresAt;
        $token->accessToken->save();

        return $this->success([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toDateTimeString(),
            'user' => $user
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request)
    {
        // extend user jwt token 30 minutes
        $user = $request->user();
        $user->currentAccessToken()->update([
            'expires_at' => now()->addMinutes(30),
        ]);

        $user = $request->user()->load('role'); // Cargar la relación 'role'

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->name,  
        ]);
    }

    public function refresh(Request $request)
    {
        $user = $request->user();

        // Revocar todos los tokens anteriores
        $user->tokens()->delete();

        // Crear un nuevo token con un tiempo de expiración
        $token = $user->createToken('auth_token');
        $expiresAt = now()->addHours(1); // Define el tiempo de expiración (1 hora en este caso)

        // Actualizar la columna expires_at en la tabla personal_access_tokens
        $token->accessToken->expires_at = $expiresAt;
        $token->accessToken->save();

        return $this->success([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toDateTimeString(),
        ], 'Token refreshed');
    }
}
