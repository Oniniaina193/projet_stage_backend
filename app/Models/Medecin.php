<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Medecin extends Model
{
    use HasFactory;

    protected $table = 'medecins';

    protected $fillable = [
        'nom_complet',
        'numero_ordre', // ONM
        'telephone',
        'adresse'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope pour rechercher par nom complet
     */
    public function scopeRechercherParNom(Builder $query, string $terme): Builder
    {
        return $query->where('nom_complet', 'ILIKE', "%{$terme}%");
    }

    /**
     * Scope pour rechercher par numéro ONM
     */
    public function scopeParNumeroOrdre(Builder $query, string $numero): Builder
    {
        return $query->where('numero_ordre', 'ILIKE', "%{$numero}%");
    }

    /**
     * Relation avec les ordonnances (si applicable)
     */
    public function ordonnances()
    {
        return $this->hasMany(Ordonnance::class);
    }

    /**
     * Validation des règles simplifiées
     */
    public static function validationRules($id = null): array
    {
        return [
            'nom_complet' => 'required|string|max:200',
            'numero_ordre' => 'required|string|max:50|unique:medecins,numero_ordre' . ($id ? ",$id" : ''),
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string|max:500'
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public static function validationMessages(): array
    {
        return [
            'nom_complet.required' => 'Le nom complet est obligatoire',
            'nom_complet.max' => 'Le nom complet ne peut pas dépasser 200 caractères',
            'numero_ordre.required' => 'Le numéro ONM est obligatoire',
            'numero_ordre.unique' => 'Ce numéro ONM existe déjà',
            'numero_ordre.max' => 'Le numéro ONM ne peut pas dépasser 50 caractères',
            'telephone.required' => 'Le téléphone est obligatoire',
            'telephone.max' => 'Le téléphone ne peut pas dépasser 20 caractères',
            'adresse.required' => 'L\'adresse est obligatoire',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères'
        ];
    }
}