<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\MedecinRequest;
use Exception;

class MedecinController extends Controller
{
    /**
     * Liste tous les médecins avec pagination et recherche
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Medecin::query();

            // Recherche par nom complet
            if ($request->has('search') && !empty($request->search)) {
                $query->rechercherParNom($request->search);
            }

            // Recherche par numéro ONM
            if ($request->has('numero_ordre') && !empty($request->numero_ordre)) {
                $query->parNumeroOrdre($request->numero_ordre);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom_complet');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $medecins = $query->paginate($perPage);

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
    public function store(MedecinRequest $request): JsonResponse
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
    public function update(MedecinRequest $request, string $id): JsonResponse
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
     * Supprimer un médecin
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $medecin = Medecin::findOrFail($id);
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
     * Statistiques des médecins (simplifiées)
     */
    public function statistiques(): JsonResponse
    {
        try {
            $stats = [
                'total_medecins' => Medecin::count(),
                'derniers_medecins' => Medecin::latest()->take(5)->get()
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