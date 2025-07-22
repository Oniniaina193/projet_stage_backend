<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Récupérer seulement username et password de votre formulaire
            $credentials = $request->only(['username', 'password']);
            
            // La checkbox "Se souvenir de moi" peut être envoyée ou pas
            $remember = $request->has('remember') ? $request->boolean('remember') : false;

            $result = $this->authService->login($credentials, $remember);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Connexion réussie'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Nom d\'utilisateur ou mot de passe incorrect',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la connexion'
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->logout();

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations utilisateur'
            ], 500);
        }
    }
}