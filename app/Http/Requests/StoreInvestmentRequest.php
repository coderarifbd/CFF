<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvestmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin only (controller middleware enforces)
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'type' => ['required','in:land,business,bank,other'],
            'amount' => ['required','numeric','min:0.01'],
            'date' => ['required','date'],
            'agreement_document' => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:5120'],
            'notes' => ['nullable','string'],
        ];
    }
}
