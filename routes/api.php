<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MedicamentController;
use App\Http\Controllers\Api\MedecinController;


Route::prefix('medecins')->group(function () {
    // Route pour obtenir les statistiques
    Route::get('statistiques', [MedecinController::class, 'statistiques']);
    
    // Route pour obtenir les spécialités
    Route::get('specialites', [MedecinController::class, 'specialites']);
    
    // Route pour restaurer un médecin
    Route::patch('{id}/restore', [MedecinController::class, 'restore']);
    
    // Routes CRUD standard
    Route::apiResource('medecins', MedecinController::class);
});

// Route alternative plus explicite
Route::prefix('medecins')->group(function () {
    Route::get('/', [MedecinController::class, 'index']);           // GET /api/medecins
    Route::post('/', [MedecinController::class, 'store']);          // POST /api/medecins
    Route::get('{id}', [MedecinController::class, 'show']);         // GET /api/medecins/{id}
    Route::put('{id}', [MedecinController::class, 'update']);       // PUT /api/medecins/{id}
    Route::patch('{id}', [MedecinController::class, 'update']);     // PATCH /api/medecins/{id}
    Route::delete('{id}', [MedecinController::class, 'destroy']);   // DELETE /api/medecins/{id}
    
    // Routes supplémentaires
    Route::get('statistiques', [MedecinController::class, 'statistiques']);
    Route::get('specialites', [MedecinController::class, 'specialites']);
    Route::patch('{id}/restore', [MedecinController::class, 'restore']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes pour les médicaments
Route::prefix('medicaments')->group(function () {
    // routes/api.php
    Route::apiResource('medicaments', MedicamentController::class); 
    Route::get('/', [MedicamentController::class, 'index']);
    Route::post('/', [MedicamentController::class, 'store']);
    Route::get('/stats', [MedicamentController::class, 'stats']);
    Route::get('/{id}', [MedicamentController::class, 'show']);
    Route::put('/{id}', [MedicamentController::class, 'update']);
    Route::delete('/{id}', [MedicamentController::class, 'destroy']);
    Route::patch('/{id}/stock', [MedicamentController::class, 'updateStock']);
});

// Route de test
Route::get('/test', function () {
    return response()->json([
        'message' => 'API Pharmacy fonctionne correctement',
        'timestamp' => now()
    ]);
});