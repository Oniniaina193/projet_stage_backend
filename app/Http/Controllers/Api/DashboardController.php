<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicament;
use App\Models\Medecin;
use App\Models\Ordonnance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Récupère toutes les données du dashboard en une seule requête optimisée
     */
    public function getDashboardData()
    {
        try {
            // Clé de cache unique pour le dashboard
            $cacheKey = 'dashboard_data_' . auth()->id();
            $cacheDuration = 300; // 5 minutes
            
            $data = Cache::remember($cacheKey, $cacheDuration, function () {
                return $this->buildDashboardData();
            });
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'cached_at' => now(),
                'message' => 'Données du dashboard récupérées avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Construction optimisée des données dashboard
     */
    private function buildDashboardData()
    {
        // Requêtes parallèles optimisées avec seulement les champs nécessaires
        $medicaments = Medicament::select('id', 'nom', 'prix', 'stock', 'famille', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $medecins = Medecin::select('id', 'nom_complet', 'specialite', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $ordonnances = Ordonnance::select('id', 'medecin_id', 'client_nom', 'total', 'statut', 'created_at')
            ->with(['medecin:id,nom_complet']) // Eager loading optimisé
            ->orderBy('created_at', 'desc')
            ->limit(100) // Limiter pour les performances
            ->get();
        
        // Calculs optimisés des statistiques
        $stats = $this->calculateDashboardStats($medicaments, $medecins, $ordonnances);
        
        // Données pour les graphiques (limité aux plus récents)
        $recentData = $this->getRecentData($medicaments, $ordonnances);
        
        return [
            'stats' => $stats,
            'medicaments' => $medicaments->take(50), // Limiter l'affichage initial
            'medecins' => $medecins->take(50),
            'ordonnances' => $ordonnances->take(50),
            'recent_data' => $recentData,
            'familles_stats' => $this->getFamillesStats($medicaments)
        ];
    }
    
    /**
     * Calcul des statistiques principales
     */
    private function calculateDashboardStats($medicaments, $medecins, $ordonnances)
    {
        // Calculs optimisés avec collections Laravel
        $stockFaible = $medicaments->where('stock', '<', 10)->count();
        $valeursStock = $medicaments->sum(function($med) {
            return $med->prix * $med->stock;
        });
        
        $famillesCount = $medicaments->pluck('famille')
            ->filter()
            ->unique()
            ->count();
            
        return [
            'total_medicaments' => $medicaments->count(),
            'total_medecins' => $medecins->count(),
            'total_ordonnances' => $ordonnances->count(),
            'stock_faible' => $stockFaible,
            'familles_count' => $famillesCount,
            'valeur_stock_total' => round($valeursStock, 2),
            'ordonnances_du_jour' => $ordonnances->whereDate('created_at', today())->count(),
            'chiffre_affaires_mois' => $ordonnances
                ->whereMonth('created_at', now()->month)
                ->sum('total')
        ];
    }
    
    /**
     * Données récentes pour les graphiques
     */
    private function getRecentData($medicaments, $ordonnances)
    {
        return [
            'medicaments_recents' => $medicaments->take(10),
            'ordonnances_recentes' => $ordonnances->take(10),
            'evolution_stock' => $this->getStockEvolution($medicaments),
            'top_medicaments' => $medicaments->sortByDesc('stock')->take(5)
        ];
    }
    
    /**
     * Statistiques par famille
     */
    private function getFamillesStats($medicaments)
    {
        return $medicaments->groupBy('famille')
            ->map(function($items, $famille) {
                return [
                    'famille' => $famille ?: 'Non définie',
                    'count' => $items->count(),
                    'valeur_totale' => $items->sum(function($med) {
                        return $med->prix * $med->stock;
                    })
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->take(10); // Top 10 familles
    }
    
    /**
     * Évolution du stock (simulation)
     */
    private function getStockEvolution($medicaments)
    {
        return $medicaments->where('stock', '>', 0)
            ->groupBy(function($med) {
                return $med->created_at->format('Y-m-d');
            })
            ->map(function($items, $date) {
                return [
                    'date' => $date,
                    'total_stock' => $items->sum('stock')
                ];
            })
            ->sortBy('date')
            ->values()
            ->take(30); // 30 derniers jours
    }
    
    /**
     * Méthode pour rafraîchir le cache du dashboard
     */
    public function refreshDashboard()
    {
        try {
            $cacheKey = 'dashboard_data_' . auth()->id();
            Cache::forget($cacheKey);
            
            // Regénérer les données
            $data = $this->buildDashboardData();
            Cache::put($cacheKey, $data, 300);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Dashboard rafraîchi avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Statistiques légères pour le header/sidebar
     */
    public function getQuickStats()
    {
        try {
            $cacheKey = 'quick_stats_' . auth()->id();
            
            $stats = Cache::remember($cacheKey, 60, function () { // 1 minute de cache
                return [
                    'medicaments_count' => Medicament::count(),
                    'medecins_count' => Medecin::count(),
                    'ordonnances_count' => Ordonnance::count(),
                    'stock_alerts' => Medicament::where('stock', '<', 10)->count()
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
}