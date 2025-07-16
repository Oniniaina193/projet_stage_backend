<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    use HasFactory;

    protected $table = 'medicaments';
    protected $primaryKey = 'id_medicament';

    /**
     * Les attributs qui peuvent être assignés en masse.
     */
    protected $fillable = [
        'nom',
        'prix',
        'stock',
        'status'
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'prix' => 'decimal:2',
        'stock' => 'integer',
        'status' => 'string'
    ];

    /**
     * Validation des données
     */
    public static function validationRules()
    {
        return [
            'nom' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:avec,sans'
        ];
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour rechercher par nom
     */
    public function scopeSearchByName($query, $name)
    {
        return $query->where('nom', 'ILIKE', '%' . $name . '%');
    }

    /**
     * Scope pour les médicaments en stock faible
     */
    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('stock', '<=', $threshold);
    }
}