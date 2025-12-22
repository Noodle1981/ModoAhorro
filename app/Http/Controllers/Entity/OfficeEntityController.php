<?php

namespace App\Http\Controllers\Entity;

/**
 * Controller for Office (Oficina) Entities
 * 
 * Handles CRUD operations specifically for office entities.
 * Includes business hours support (opens_at, closes_at, operating_days).
 */
class OfficeEntityController extends BaseEntityController
{
    protected string $entityType = 'oficina';

    // All CRUD methods are inherited from BaseEntityController
    // Override only if office-specific behavior is needed
}
