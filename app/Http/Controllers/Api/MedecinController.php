<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class MedecinController extends Controller
{
    /**
     * Liste tous les médecins avec pagination et recherche
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Medecin::actifs();

            // Recherche par terme
            if ($request->has('search') && !empty($request->search)) {
                $query->rechercherParNom($request->search);
            }

            // Filtrer par spécialité
            if ($request->has('specialite') && !empty($request->specialite)) {
                $query->parSpecialite($request->specialite);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $medecins = $query->paginate($perPage);

            // Ajouter le nom complet à chaque médecin
            $medecins->through(function ($medecin) {
                $medecin->nom_complet = $medecin->nom_complet;
                return $medecin;
            });

            return response()->json([
                'success' => true,
                'message' => 'Médecins récupérés avec succès',
                'data' => $medecins
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des médecins',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau médecin
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                Medecin::validationRules(),
                Medecin::validationMessages()
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $medecin = Medecin::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Médecin créé avec succès',
                'data' => $medecin
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du médecin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un médecin spécifique
     */
    public function show(string $id): JsonResponse
    {
        try {
            $medecin = Medecin::findOrFail($id);
            $medecin->nom_complet = $medecin->nom_complet;

            return response()->json([
                'success' => true,
                'message' => 'Médecin récupéré avec succès',
                'data' => $medecin
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin non trouvé'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du médecin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un médecin
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $medecin = Medecin::findOrFail($id);

            $validator = Validator::make(
                $request->all(),
                Medecin::validationRules($id),
                Medecin::validationMessages()
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $medecin->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Médecin mis à jour avec succès',
                'data' => $medecin
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin non trouvé'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du médecin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un médecin (soft delete)
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $medecin = Medecin::findOrFail($id);
            
            // Soft delete - marquer comme inactif
            //$medecin->update(['actif' => false]);
            $medecin->delete();

            return response()->json([
                'success' => true,
                'message' => 'Médecin supprimé avec succès'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin non trouvé'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du médecin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurer un médecin
     */
    public function restore(string $id): JsonResponse
    {
        try {
            $medecin = Medecin::findOrFail($id);
            $medecin->update(['actif' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Médecin restauré avec succès',
                'data' => $medecin
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Médecin non trouvé'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration du médecin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir la liste des spécialités
     */
    public function specialites(): JsonResponse
    {
        try {
            $specialites = Medecin::actifs()
                ->select('specialite')
                ->distinct()
                ->orderBy('specialite')
                ->pluck('specialite');

            return response()->json([
                'success' => true,
                'message' => 'Spécialités récupérées avec succès',
                'data' => $specialites
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des spécialités',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques des médecins
     */
    public function statistiques(): JsonResponse
    {
        try {
            $stats = [
                'total_medecins' => Medecin::actifs()->count(),
                'total_specialites' => Medecin::actifs()->distinct('specialite')->count(),
                'medecins_par_specialite' => Medecin::actifs()
                    ->selectRaw('specialite, count(*) as total')
                    ->groupBy('specialite')
                    ->orderBy('total', 'desc')
                    ->get(),
                'derniers_medecins' => Medecin::actifs()
                    ->latest()
                    ->take(5)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées avec succès',
                'data' => $stats
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}