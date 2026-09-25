<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'usage_type' => 'required|string|in:residencial,comercial,oficina',
            'address_street' => 'nullable|string|max:255',
            'address_postal_code' => 'nullable|string|max:20',
            'locality_id' => 'nullable|exists:localities,id',
            'square_meters' => 'nullable|numeric|min:1',
            'people_count' => 'nullable|integer|min:0',
            'construction_year' => 'nullable|integer|min:1900|max:'.date('Y'),
            'has_gas' => 'boolean',
            'has_solar' => 'boolean',
            'has_business_activity' => 'boolean',
            'business_type' => 'nullable|string|in:almacen,taller,venta',
            'description' => 'nullable|string',
            'comercio_type' => 'nullable|string|in:gastronomia,retail,oficina',
            'business_category' => 'nullable|string|max:100',
            'business_subcategory' => 'nullable|string|max:100',
            'staff_count' => 'nullable|integer|min:0',
            'visitors_count' => 'nullable|integer|min:0',
            'service_turns' => 'nullable|integer|min:1|max:3',
            'opens_at' => 'nullable|string',
            'closes_at' => 'nullable|string',
        ];
    }
}
