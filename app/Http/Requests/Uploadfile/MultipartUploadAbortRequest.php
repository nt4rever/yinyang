<?php

namespace App\Http\Requests\Uploadfile;

use Illuminate\Foundation\Http\FormRequest;

class MultipartUploadAbortRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'upload_id' => 'required|string',
        ];
    }
}
