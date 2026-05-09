<?php

namespace App\Http\Requests\Uploadfile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MultipartUploadPresignedUrlRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'upload_id' => 'required|string',
            'part_number' => 'required|integer|min:1',
        ];
    }
}
