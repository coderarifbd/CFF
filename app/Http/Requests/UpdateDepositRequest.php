<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin can update; controller middleware restricts routes
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
            'date' => ['required','date'],
            'member_id' => ['required','exists:members,id'],
            'type' => ['required','in:subscription,extra,fine'],
            'amount' => ['required','numeric','min:0.01'],
            'payment_method' => ['required','in:cash,bank,mobile'],
            'note' => ['nullable','string'],
        ];
    }
}
