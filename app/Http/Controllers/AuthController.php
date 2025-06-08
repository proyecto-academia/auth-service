<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserPolicy;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;


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
        ]);

        $user->role()->associate($role_student); // Asociar el rol 'student'
        $user->save();

        // Crear el token con un tiempo de expiración
        $token = $user->createToken('auth_token');
        $expiresAt = now()->addHours(1); // Define el tiempo de expiración (1 hora en este caso)

        // Actualizar la columna expires_at en la tabla personal_access_tokens
        $token->accessToken->expires_at = $expiresAt;
        $token->accessToken->save();

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->error('User not found', 404);
        }
        $user = $user->load('role'); // Cargar la relación 'role'

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
            'token' => $request->bearerToken(),
            'expires_at' => $user->currentAccessToken()->expires_at->toDateTimeString(),
        ]);
    }


    public function checkPolicy(Request $request)
    {
        $request->validate([
            'policy' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $user = $request->user();
        $user->currentAccessToken()->update([
            'expires_at' => now()->addMinutes(30),
        ]);

        $policy = Policy::where('name', $request->policy)->first();
        if (!$policy) {
            return $this->error('Policy not found', 404);
        }

        $hasAccess = UserPolicy::where('user_id', $user->id)
            ->where('policy_id', $policy->id)
            ->where('model_id', $request->model_id)
            ->exists();

        if ($hasAccess) {
            return $this->success(['authorized' => true], 'User authorized');
        }

        // Sustituir {id} en request_url por el model_id recibido
        $remoteUrl = str_replace('{id}', $request->model_id, $policy->request_url);

        $response = Http::withToken($request->bearerToken())
            ->get($remoteUrl);


        if ($response->successful() && $response->json('data')['allowed'] === true) {
            UserPolicy::create([
                'user_id' => $user->id,
                'policy_id' => $policy->id,
                'model_id' => $request->model_id,
            ]);

            return $this->success(['authorized' => true], 'User authorized');
        }

        return $this->error('Unauthorized', 403);
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
