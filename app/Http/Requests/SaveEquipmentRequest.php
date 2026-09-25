<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEquipmentRequest extends FormRequest
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
        $rules = [
            'room_id' => 'required|exists:rooms,id',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'name' => 'required|string|max:255',
            'nominal_power_w' => 'required|numeric|min:0',
            'avg_daily_use_hours' => 'nullable|numeric|min:0|max:24',
            'is_standby' => 'nullable|boolean',
            'is_inverter' => 'nullable|boolean',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'model_id' => 'nullable|exists:equipment_models,id',
            'serial_number' => 'nullable|string|max:255',
            'energy_label' => 'nullable|string|max:10',
        ];

        if ($this->isMethod('post')) {
            $rules['cantidad'] = 'integer|min:1';
        } else {
            $rules['is_active'] = 'required|boolean';
        }

        return $rules;
    }
}
