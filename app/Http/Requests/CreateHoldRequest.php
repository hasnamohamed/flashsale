<?php

namespace App\Http\Requests;

use App\Rules\StockAvailable;
use Illuminate\Foundation\Http\FormRequest;

class CreateHoldRequest extends FormRequest
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
        return [
            'product_id' => ['required','integer','exists:products,id', new StockAvailable($this->qty)],
            'qty' => 'required|integer|min:1',
        ];
    }
    public function messages()
    {
        return [
            'product_id.required' => 'Product ID is required.',
            'qty.min' => 'Quantity must be at least 1.',
        ];
    }
}
