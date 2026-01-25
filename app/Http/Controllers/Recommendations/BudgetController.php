<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Services\BudgetService;

class BudgetController extends Controller
{
    public function index(string $id, BudgetService $budgetService)
    {
        $entity = Entity::findOrFail($id);
        $config = config("entity_types.{$entity->type}", []);
        $budgetData = $budgetService->calculateBudgetData($entity);
        return view('entities.budget', compact('entity', 'budgetData', 'config'));
    }
}
