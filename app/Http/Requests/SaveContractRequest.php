<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveContractRequest extends FormRequest
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
        $contract = $this->route('contract');
        $contractId = is_object($contract) ? $contract->id : $contract;

        return [
            'entity_id' => 'required|exists:entities,id',
            'proveedor_id' => 'required|exists:proveedores,id',
            'supply_number' => 'required|string|max:255',
            'meter_number' => 'nullable|string|max:255',
            'contract_number' => [
                'nullable',
                'string',
                'max:255',
                $contractId
                    ? Rule::unique('contracts', 'contract_number')->ignore($contractId)
                    : Rule::unique('contracts', 'contract_number'),
            ],
            'rate_name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'is_three_phase' => 'required|boolean',
            'contracted_power_kw_p1' => 'required|numeric|min:0',
            'contracted_power_kw_p2' => 'nullable|numeric|min:0',
            'contracted_power_kw_p3' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'contract_number.unique' => $this->isMethod('post')
                ? 'Este número de contrato ya se encuentra registrado en el sistema.'
                : 'Este número de contrato ya pertenece a otro registro.',
        ];
    }
}
