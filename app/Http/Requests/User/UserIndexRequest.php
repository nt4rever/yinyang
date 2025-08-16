<?php

namespace App\Http\Requests\User;

use App\Http\Requests\AbstractIndexRequest;

class UserIndexRequest extends AbstractIndexRequest
{
    /**
     * Get the allowed sort attributes
     */
    protected function getAllowedSortAttributes(): array
    {
        return ['id', 'name', 'email', 'created_at', 'updated_at'];
    }

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
        return array_merge(
            $this->commonRules(),
            [
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|string|max:255',
            ]
        );
    }
}
