<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Uploadfile\UploadfileStoreRequest;
use App\Services\S3Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UploadfileController extends Controller
{
    /**
     * Get a temporary upload URL.
     */
    public function temporaryUploadUrl(S3Service $s3Service): JsonResponse
    {
        return response()->json($s3Service->generateTemporaryUploadUrl());
    }

    /**
     * Example processing uploaded file use presigned URL.
     */
    public function store(UploadfileStoreRequest $request): JsonResponse
    {
        $path = 'documents/sample.pdf';
        $key = $request->input('key');
        dispatch(fn () => Storage::move($key, $path));

        return response()->json(['path' => $path]);
    }
}
