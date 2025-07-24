<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicament;
use App\Models\Medecin;
use App\Models\Ordonnance;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Récupérer toutes les statistiques du dashboard en une seule requête
     */
    public function stats(): JsonResponse
    {
        try {
            // Cache les stats pendant 5 minutes
            $stats = Cache::remember('dashboard_stats', 300, function () {
                return DB::select("
                    SELECT 
                        (SELECT COUNT(*) FROM medicaments) as total_medicaments,
                        (SELECT COUNT(*) FROM medicaments WHERE status = 'sans') as sans_ordonnance,
                        (SELECT COUNT(*) FROM medicaments WHERE status = 'avec') as avec_ordonnance,
                        (SELECT COUNT(*) FROM medicaments WHERE stock < 10) as stock_faible,
                        (SELECT COUNT(*) FROM ordonnances) as total_ordonnances,
                        (SELECT COUNT(*) FROM ordonnances WHERE DATE(created_at) = CURRENT_DATE) as ordonnances_jour,
                        (SELECT COUNT(*) FROM medecins) as total_medecins,
                        (SELECT COUNT(*) FROM medecins WHERE active = true) as medecins_actifs,
                        (SELECT COALESCE(SUM(prix * stock), 0) FROM medicaments) as valeur_stock_total
                ")[0];
            });

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques du dashboard récupérées avec succès'
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
     * Récupérer les activités récentes (ordonnances, nouveaux médicaments, etc.)
     */
    public function recentActivity(): JsonResponse
    {
        try {
            $activities = Cache::remember('dashboard_recent_activity', 600, function () {
                return [
                    'recent_ordonnances' => Ordonnance::with(['medecin:id_medecin,nom,prenom'])
                        ->select('id_ordonnance', 'medecin_id', 'created_at')
                        ->latest()
                        ->limit(5)
                        ->get(),
                    
                    'new_medicaments' => Medicament::select('id_medicament', 'nom', 'created_at')
                        ->latest()
                        ->limit(5)
                        ->get(),
                        
                    'low_stock_alerts' => Medicament::select('id_medicament', 'nom', 'stock')
                        ->where('stock', '<', 10)
                        ->orderBy('stock', 'asc')
                        ->limit(5)
                        ->get()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $activities,
                'message' => 'Activités récentes récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des activités',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Données pour les graphiques du dashboard
     */
    public function charts(): JsonResponse
    {
        try {
            $chartData = Cache::remember('dashboard_charts', 1800, function () {
                // Ordonnances par mois (6 derniers mois)
                $ordonnancesParMois = DB::select("
                    SELECT 
                        DATE_TRUNC('month', created_at) as mois,
                        COUNT(*) as total
                    FROM ordonnances 
                    WHERE created_at >= NOW() - INTERVAL '6 months'
                    GROUP BY DATE_TRUNC('month', created_at)
                    ORDER BY mois
                ");

                // Top 5 médicaments les plus prescrits
                $topMedicaments = DB::select("
                    SELECT 
                        m.nom,
                        COUNT(om.medicament_id) as prescriptions
                    FROM medicaments m
                    JOIN ordonnance_medicaments om ON m.id_medicament = om.medicament_id
                    GROUP BY m.id_medicament, m.nom
                    ORDER BY prescriptions DESC
                    LIMIT 5
                ");

                return [
                    'ordonnances_par_mois' => $ordonnancesParMois,
                    'top_medicaments' => $topMedicaments
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $chartData,
                'message' => 'Données des graphiques récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des graphiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}