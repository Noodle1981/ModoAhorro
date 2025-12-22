<?php

namespace App\Http\Controllers\Entity;

/**
 * Controller for Trade (Comercio) Entities
 * 
 * Handles CRUD operations specifically for commercial entities.
 * Includes business hours support (opens_at, closes_at, operating_days).
 */
class TradeEntityController extends BaseEntityController
{
    protected string $entityType = 'comercio';

    // All CRUD methods are inherited from BaseEntityController
    // Override only if trade-specific behavior is needed
}
