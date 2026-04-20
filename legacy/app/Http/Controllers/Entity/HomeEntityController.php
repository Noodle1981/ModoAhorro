<?php

namespace App\Http\Controllers\Entity;

/**
 * Controller for Home (Hogar) Entities
 * 
 * Handles CRUD operations specifically for residential entities.
 * No business hours or working schedules - these are homes.
 */
class HomeEntityController extends BaseEntityController
{
    protected string $entityType = 'hogar';

    // All CRUD methods are inherited from BaseEntityController
    // Override only if home-specific behavior is needed
}
