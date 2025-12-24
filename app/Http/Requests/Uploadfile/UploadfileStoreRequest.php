<?php

namespace App\Http\Requests\Uploadfile;

use App\Rules\UploadfileSize;
use App\Rules\UploadfileType;
use Illuminate\Foundation\Http\FormRequest;

class UploadfileStoreRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => ['bail', 'required', 'string', new UploadfileSize(5 * 1024 * 1024), new UploadfileType(['pdf'])],
        ];
    }
}
