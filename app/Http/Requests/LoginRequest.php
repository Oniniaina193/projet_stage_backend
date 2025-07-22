<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:6'
            // Pas besoin de 'remember' dans les rules car c'est optionnel
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Le nom d\'utilisateur est requis',
            'username.min' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères',
            'username.max' => 'Le nom d\'utilisateur ne peut pas dépasser 50 caractères',
            'password.required' => 'Le mot de passe est requis',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères'
        ];
    }
}