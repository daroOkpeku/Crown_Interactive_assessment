<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignApproversRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->hasRole('superadmin') || $user->hasRole('sub_unit_head');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'priorities' => 'nullable|array',
            'priorities.*' => 'integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_ids.required' => 'At least one user must be selected.',
            'user_ids.array' => 'The user IDs must be an array.',
            'user_ids.min' => 'At least one user must be selected.',
            'user_ids.*.exists' => 'One or more selected users are invalid.',
            'priorities.array' => 'The priorities must be an array.',
            'priorities.*.integer' => 'Each priority must be an integer.',
            'priorities.*.min' => 'Each priority must be at least 0.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $userIds = $this->input('user_ids', []);
            $priorities = $this->input('priorities', []);
            
            if (count($priorities) > 0 && count($priorities) !== count($userIds)) {
                $validator->errors()->add('priorities', 'The number of priorities must match the number of users.');
            }
        });
    }
}
