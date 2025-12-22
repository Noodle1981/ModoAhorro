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
        $budgetData = $budgetService->generateBudget($entity);
        return view('budget.index', compact('entity', 'budgetData'));
    }
}
