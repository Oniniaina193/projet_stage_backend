<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Obtenir les informations de l'utilisateur connecté (alias pour me())
     */
    public function user(Request $request): JsonResponse
    {
        try {
            // Vérifier si l'utilisateur est authentifié
            if (!Auth::guard('sanctum')->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié'
                ], 401);
            }

            $user = $request->user();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username ?? null,
                        'email' => $user->email ?? null,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        // Ajoutez d'autres champs selon votre modèle User
                    ]
                ],
                'message' => 'Informations utilisateur récupérées avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations utilisateur',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Vérifier si l'utilisateur est connecté (endpoint plus léger)
     */
    public function check(Request $request): JsonResponse
    {
        try {
            if (Auth::guard('sanctum')->check()) {
                $user = $request->user();
                
                return response()->json([
                    'success' => true,
                    'authenticated' => true,
                    'user_id' => $user->id,
                    'username' => $user->username ?? $user->name,
                    'message' => 'Utilisateur authentifié'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'authenticated' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'message' => 'Erreur lors de la vérification',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}