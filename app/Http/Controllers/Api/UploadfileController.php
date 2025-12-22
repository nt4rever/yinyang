<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Uploadfile\MultipartUploadAbortRequest;
use App\Http\Requests\Uploadfile\MultipartUploadCompleteRequest;
use App\Http\Requests\Uploadfile\MultipartUploadCreateRequest;
use App\Http\Requests\Uploadfile\MultipartUploadPresignedUrlRequest;
use App\Http\Requests\Uploadfile\UploadfileStoreRequest;
use App\Services\S3Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UploadfileController extends Controller
{
    public function __construct(private S3Service $s3Service) {}

    /**
     * Get a temporary upload URL.
     */
    public function temporaryUploadUrl(): JsonResponse
    {
        return response()->json($this->s3Service->temporaryUploadUrl());
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

    /**
     * Create a multipart upload.
     */
    public function createMultipartUpload(MultipartUploadCreateRequest $request): JsonResponse
    {
        $result = $this->s3Service->createMultipartUpload($request->input('key'));

        return response()->json($result);
    }

    /**
     * Get a presigned URL for a multipart upload part.
     */
    public function multipartPresignedUrl(MultipartUploadPresignedUrlRequest $request): JsonResponse
    {
        $result = $this->s3Service->multipartPresignedUrl(
            $request->input('key'),
            $request->input('upload_id'),
            $request->integer('part_number')
        );

        return response()->json($result);
    }

    /**
     * Complete a multipart upload.
     */
    public function completeMultipartUpload(MultipartUploadCompleteRequest $request): JsonResponse
    {
        $this->s3Service->completeMultipartUpload(
            $request->input('key'),
            $request->input('upload_id'),
            $request->getParts()
        );

        return response()->json([], 201);
    }

    /**
     * Abort a multipart upload.
     */
    public function abortMultipartUpload(MultipartUploadAbortRequest $request): JsonResponse
    {
        $this->s3Service->abortMultipartUpload(
            $request->input('key'),
            $request->input('upload_id')
        );

        return response()->json([], 204);
    }
}
