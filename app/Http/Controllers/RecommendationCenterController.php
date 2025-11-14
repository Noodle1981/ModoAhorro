<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Recommendations\HogarRecommendationService;
use App\Services\Recommendations\ComercioRecommendationService;
use App\Services\Recommendations\OficinaRecommendationService;

class RecommendationCenterController extends Controller
{
    /**
     * Muestra el centro de recomendaciones general o por entidad.
     */
    public function index(Request $request)
    {
        // Aquí se decidirá si mostrar recomendaciones generales o por entidad
        // Ejemplo de selección dinámica (lógica a implementar luego)
        $entityType = $request->input('entity_type');
        $recommendations = [];

        switch ($entityType) {
            case 'hogar':
                $service = new HogarRecommendationService();
                break;
            case 'comercio':
                $service = new ComercioRecommendationService();
                break;
            case 'oficina':
                $service = new OficinaRecommendationService();
                break;
            default:
                $service = null;
        }

        if ($service) {
            // $entity sería el modelo de la entidad, aquí solo es ejemplo
            $recommendations = $service->getRecommendations(null);
        }

        // Retornar vista con recomendaciones (a implementar)
        return view('recommendations.center', [
            'recommendations' => $recommendations,
            'entityType' => $entityType,
        ]);
    }
}
