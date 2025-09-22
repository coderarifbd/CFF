<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Member;

class StoreMemberRequest extends FormRequest
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
        // Allow reusing an existing User email if that user does not have an active (non-deleted) member linked
        $emailRule = Rule::unique('users','email');
        $passwordRule = ['required','string','min:8','confirmed'];
        if ($this->filled('email')) {
            $existingUser = User::where('email', $this->input('email'))->first();
            if ($existingUser) {
                $hasActiveMember = Member::where('user_id', $existingUser->id)->whereNull('deleted_at')->exists();
                if (! $hasActiveMember) {
                    $emailRule = Rule::unique('users','email')->ignore($existingUser->id);
                    // If linking to an existing user, password can be optional
                    $passwordRule = ['nullable','string','min:8','confirmed'];
                }
            }
        }

        return [
            'name' => ['required','string','max:255'],
            // Ignore soft-deleted members so values can be reused after delete
            'phone' => ['required','string','max:30', Rule::unique('members','phone')->whereNull('deleted_at')],
            'nid' => ['required','string','max:100', Rule::unique('members','nid')->whereNull('deleted_at')],
            'address' => ['nullable','string'],
            'profile_picture' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'join_date' => ['required','date'],
            'status' => ['required','in:active,inactive,suspended'],
            'member_type' => ['required','in:admin,accountant,member'],
            'email' => ['required','email','max:255', $emailRule],
            'password' => $passwordRule,
        ];
    }
}
