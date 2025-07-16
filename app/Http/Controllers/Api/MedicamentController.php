<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicament;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MedicamentController extends Controller
{
    /**
     * Afficher la liste des médicaments avec pagination et filtres
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Medicament::query();

            // recherche par nom
            if ($request->has('search') && $request->search) {
                $query->searchByName($request->search);
            }

            // recherche par statut
            if ($request->has('status') && $request->status) {
                $query->byStatus($request->status);
            }

            // Filtrage par stock faible
            if ($request->has('low_stock') && $request->low_stock) {
                $query->lowStock($request->low_stock_threshold ?? 10);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $medicaments = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $medicaments,
                'message' => 'Liste des médicaments récupérée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des médicaments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau médicament
     */
    public function store(Request $request): JsonResponse
    {
        try {

            \Log::info('=== DEBUT DEBUG ===');
            \Log::info('Données reçues:', $request->all());
        
            $validatedData = $request->validate(Medicament::validationRules());
            \Log::info('Données validées:', $validatedData);

            // Vérifier si le médicament existe déjà
            $existingMedicament = Medicament::where('nom', $validatedData['nom'])->first();
            if ($existingMedicament) {
                 \Log::warning('Médicament existe déjà');
                return response()->json([
                    'success' => false,
                    'message' => 'Un médicament avec ce nom existe déjà'
                ], 422);
            }
            
             \Log::info('Tentative création...');
            $medicament = Medicament::create($validatedData);
            \Log::info('Médicament créé:', $medicament->toArray());
        
            // Vérifier en base
             $check = Medicament::find($medicament->id_medicament);
            \Log::info('Vérif en base:', $check ? $check->toArray() : 'INTROUVABLE');
        
            \Log::info('=== FIN DEBUG ===');
            
            return response()->json([
                'success' => true,
                'data' => $medicament,
                'message' => 'Médicament créé avec succès'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du médicament',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un médicament spécifique
     */
    public function show(string $id): JsonResponse
    {
        try {
            $medicament = Medicament::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $medicament,
                'message' => 'Médicament récupéré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Médicament non trouvé'
            ], 404);
        }
    }

    /**
     * Mettre à jour un médicament
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $medicament = Medicament::findOrFail($id);
            
            $validatedData = $request->validate(Medicament::validationRules());

            // Vérifier si le nom est déjà utilisé par un autre médicament
            $existingMedicament = Medicament::where('nom', $validatedData['nom'])
                ->where('id_medicament', '!=', $id)
                ->first();
            
            if ($existingMedicament) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un autre médicament avec ce nom existe déjà'
                ], 422);
            }

            $medicament->update($validatedData);

            return response()->json([
                'success' => true,
                'data' => $medicament,
                'message' => 'Médicament mis à jour avec succès'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du médicament',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un médicament
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $medicament = Medicament::findOrFail($id);
            $medicament->delete();

            return response()->json([
                'success' => true,
                'message' => 'Médicament supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du médicament',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des médicaments
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_medicaments' => Medicament::count(),
                'sans_ordonnance' => Medicament::byStatus('sans')->count(),
                'avec_ordonnance' => Medicament::byStatus('avec')->count(),
                'stock_faible' => Medicament::lowStock(10)->count(),
                'valeur_totale_stock' => Medicament::selectRaw('SUM(prix * stock) as total')->value('total') ?? 0
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le stock d'un médicament
     */
    public function updateStock(Request $request, string $id): JsonResponse
    {
        try {
            $medicament = Medicament::findOrFail($id);
            
            $validatedData = $request->validate([
                'stock' => 'required|integer|min:0',
                'operation' => 'required|in:add,subtract,set'
            ]);

            $newStock = $medicament->stock;
            
            switch ($validatedData['operation']) {
                case 'add':
                    $newStock += $validatedData['stock'];
                    break;
                case 'subtract':
                    $newStock = max(0, $newStock - $validatedData['stock']);
                    break;
                case 'set':
                    $newStock = $validatedData['stock'];
                    break;
            }

            $medicament->update(['stock' => $newStock]);

            return response()->json([
                'success' => true,
                'data' => $medicament,
                'message' => 'Stock mis à jour avec succès'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}