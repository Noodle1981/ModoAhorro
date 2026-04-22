<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class SaveInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La autorización de propiedad de la entidad se sigue manejando en el controlador 
        // a través de políticas, para mayor flexibilidad.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'contract_id' => 'required|exists:contracts,id',
            'invoice_number' => 'required|string|max:255',
            'tariff' => 'nullable|string|max:50',
            'invoice_date' => 'required|date',
            'issue_date' => 'nullable|date|after:start_date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_energy_consumed_kwh' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'cost_for_energy' => 'nullable|numeric|min:0',
            'cost_for_power' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'installment_number' => 'nullable|integer|min:1',
            'total_installments' => 'nullable|integer|min:1',
            'bimonthly_consumption_kwh' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Valida la lógica de fechas personalizada (Carbon).
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();
            
            $issue_date = Carbon::parse($data['issue_date'] ?? $data['invoice_date']);
            $end_date = Carbon::parse($data['end_date']);
            $start_date = Carbon::parse($data['start_date']);

            if ($issue_date->lt($end_date)) {
                $validator->errors()->add('issue_date', 'La fecha de emisión debe ser posterior al cierre del período.');
            }

            if ($issue_date->year - $start_date->year > 1) {
                $validator->errors()->add('issue_date', 'El año de la factura no puede ser más de un año posterior al período de consumo.');
            }
        });
    }

    /**
     * Prepara los datos para la inserción limpia.
     */
    protected function passedValidation()
    {
        if (empty($this->issue_date)) {
            $this->merge([
                'issue_date' => $this->invoice_date
            ]);
        }
    }
}
