<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization handled via controller middleware (role:Admin)
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $memberId = $this->route('member')?->id;
        $userId = optional($this->route('member'))->user_id;
        return [
            'name' => ['required','string','max:255'],
            // Make uniqueness soft-delete aware and ignore current member
            'phone' => ['required','string','max:30', Rule::unique('members','phone')->ignore($memberId)->whereNull('deleted_at')],
            'nid' => ['required','string','max:100', Rule::unique('members','nid')->ignore($memberId)->whereNull('deleted_at')],
            'address' => ['nullable','string'],
            'profile_picture' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'join_date' => ['required','date'],
            'status' => ['required','in:active,inactive,suspended'],
            'member_type' => ['required','in:admin,accountant,member'],
            'email' => ['required','email','max:255','unique:users,email,' . ($userId ?? 'NULL')],
        ];
    }
}
