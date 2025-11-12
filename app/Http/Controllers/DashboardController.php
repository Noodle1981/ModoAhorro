<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $entity = $user->entities()->first();
        $needsEntity = !$entity;
        return view('dashboard', [
            'entity' => $entity,
            'needsEntity' => $needsEntity,
        ]);
    }
}
