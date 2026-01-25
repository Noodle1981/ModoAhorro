<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Locality;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get localities by province ID.
     */
    public function getLocalitiesByProvince($provinceId)
    {
        $localities = Locality::where('province_id', $provinceId)
            ->orderBy('name')
            ->get(['id', 'name', 'postal_code']);

        return response()->json($localities);
    }
}
