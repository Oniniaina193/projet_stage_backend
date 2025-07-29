<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MedecinRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'nom_complet' => 'required|string|max:200',
            'numero_ordre' => 'required|string|max:50', // ONM
            'telephone' => 'required|regex:/^[0-9]{10}$/',
            'adresse' => 'required|string|max:500'
        ];

        // Pour l'update, on ignore l'unicité pour l'enregistrement actuel
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $medecinId = $this->route('medecin') ?: $this->route('id');
            
            if ($medecinId) {
                $rules['numero_ordre'] = [
                    'required',
                    'string',
                    'max:50',
                    'unique:medecins,numero_ordre,' . $medecinId
                ];
                $rules['telephone'] = [
                    'required',
                    'regex:/^[0-9]{10}$/',
                    'unique:medecins,telephone,' . $medecinId
                ];
            }
        } else {
            // Pour la création
            $rules['numero_ordre'] = [
                'required',
                'string',
                'max:50',
                'unique:medecins,numero_ordre'
            ];
            $rules['telephone'] = [
                'required',
                'regex:/^[0-9]{10}$/',
                'unique:medecins,telephone'
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'nom_complet.required' => 'Le nom complet est obligatoire',
            'nom_complet.max' => 'Le nom complet ne doit pas dépasser 200 caractères',
            'numero_ordre.required' => 'Le numéro ONM est obligatoire',
            'numero_ordre.unique' => 'Ce numéro ONM existe déjà',
            'numero_ordre.max' => 'Le numéro ONM ne doit pas dépasser 50 caractères',
            'telephone.required' => 'Le numéro de téléphone est obligatoire',
            'telephone.regex' => 'Le numéro de téléphone doit contenir exactement 10 chiffres',
            'telephone.unique' => 'Ce numéro de téléphone existe déjà',
            'adresse.required' => 'L\'adresse est obligatoire',
            'adresse.max' => 'L\'adresse ne doit pas dépasser 500 caractères'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erreurs de validation',
            'errors' => $validator->errors()
        ], 422));
    }
}