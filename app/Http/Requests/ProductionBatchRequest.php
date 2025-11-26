<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionBatchRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'order_id' => 'required|integer|exists:customer_order,order_id',
            'batch_code' => 'required|string|max:50|unique:production_batch,batch_code',
            'name' => 'nullable|string|max:100',
            'target_quantity' => 'nullable|numeric|min:0',
            'produced_quantity' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:500',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['batch_code'] = 'required|string|max:50|unique:production_batch,batch_code,' . $this->route('production_batch');
            $rules['order_id'] = 'nullable|integer|exists:customer_order,order_id';
        }

        return $rules;
    }
}

