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
        'nom',
        'prenom',
        'specialite',
        'numero_ordre',
        'telephone',
        'email',
        'adresse',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Accessor pour le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return "Dr. {$this->nom} {$this->prenom}";
    }

    /**
     * Scope pour les médecins actifs
     */
    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour rechercher par nom ou prénom
     */
    public function scopeRechercherParNom(Builder $query, string $terme): Builder
    {
        return $query->where(function ($q) use ($terme) {
            $q->where('nom', 'ILIKE', "%{$terme}%")
              ->orWhere('prenom', 'ILIKE', "%{$terme}%")
              ->orWhere('specialite', 'ILIKE', "%{$terme}%");
        });
    }

    /**
     * Scope pour filtrer par spécialité
     */
    public function scopeParSpecialite(Builder $query, string $specialite): Builder
    {
        return $query->where('specialite', 'ILIKE', "%{$specialite}%");
    }

    /**
     * Relation avec les ordonnances (si applicable)
     */
    public function ordonnances()
    {
        return $this->hasMany(Ordonnance::class);
    }

    /**
     * Validation des règles
     */
    public static function validationRules($id = null): array
    {
        return [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'specialite' => 'required|string|max:150',
            'numero_ordre' => 'required|string|max:50|unique:medecins,numero_ordre' . ($id ? ",$id" : ''),
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|max:150|unique:medecins,email' . ($id ? ",$id" : ''),
            'adresse' => 'required|string',
            'actif' => 'boolean'
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public static function validationMessages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'nom.max' => 'Le nom ne peut pas dépasser 100 caractères',
            'prenom.required' => 'Le prénom est obligatoire',
            'prenom.max' => 'Le prénom ne peut pas dépasser 100 caractères',
            'specialite.required' => 'La spécialité est obligatoire',
            'specialite.max' => 'La spécialité ne peut pas dépasser 150 caractères',
            'numero_ordre.required' => 'Le numéro d\'ordre est obligatoire',
            'numero_ordre.unique' => 'Ce numéro d\'ordre existe déjà',
            'numero_ordre.max' => 'Le numéro d\'ordre ne peut pas dépasser 50 caractères',
            'telephone.required' => 'Le téléphone est obligatoire',
            'telephone.max' => 'Le téléphone ne peut pas dépasser 20 caractères',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'email.unique' => 'Cet email existe déjà',
            'email.max' => 'L\'email ne peut pas dépasser 150 caractères',
            'adresse.required' => 'L\'adresse est obligatoire'
        ];
    }
}