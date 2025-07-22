<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials, bool $remember = false)
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        // Rechercher l'utilisateur par nom d'utilisateur
        $user = User::where('username', $username)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'username' => ['Nom d\'utilisateur ou mot de passe incorrect.']
            ]);
        }

        // Vérifier si l'utilisateur est actif
        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'username' => ['Votre compte a été désactivé. Contactez l\'administrateur.']
            ]);
        }

        // Vérifier le mot de passe
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Nom d\'utilisateur ou mot de passe incorrect.']
            ]);
        }

        // Authentifier l'utilisateur
        Auth::login($user, $remember);

        // Mettre à jour la dernière connexion
        $user->updateLastLogin();

        // Créer un token pour l'API (Sanctum)
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'message' => 'Connexion réussie'
        ];
    }

    public function logout()
    {
        $user = Auth::user();
        
        if ($user) {
            // Révoquer tous les tokens
            $user->tokens()->delete();
        }

        Auth::logout();

        return ['message' => 'Déconnexion réussie'];
    }
}