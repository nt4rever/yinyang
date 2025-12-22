<?php

namespace App\Http\Requests\Uploadfile;

use Illuminate\Foundation\Http\FormRequest;

class MultipartUploadCompleteRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'upload_id' => 'required|string',
            'parts' => 'required|array',
            'parts.*.part_number' => 'required|integer|min:1',
            'parts.*.etag' => 'required|string',
        ];
    }

    /**
     * Transform the parts to the format required by the S3 service.
     *
     * @return array<array-key, array{PartNumber: int, ETag: string}>
     */
    public function getParts(): array
    {
        return collect($this->input('parts'))->map(fn ($part) => [
            'PartNumber' => $part['part_number'],
            'ETag' => $part['etag'],
        ])->toArray();
    }
}
