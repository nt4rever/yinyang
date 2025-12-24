<?php

namespace App\Services;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    private S3Client $client;

    public function __construct()
    {
        $this->client = Storage::disk('s3')->getClient();
    }

    /**
     * Generate a S3 pre-signed URL for temporary upload.
     *
     * @param  int  $expiresIn  The number of minutes the URL will be valid. (default: 5)
     * @return array<string, string> The S3 object key and the pre-signed URL.
     */
    public function temporaryUploadUrl(int $expiresIn = 5): array
    {
        $key = 'tmp/'.Str::random(16).'/'.Str::random(16);
        ['url' => $url] = Storage::disk('s3')->temporaryUploadUrl($key, now()->addMinutes($expiresIn));

        return [
            'key' => $key,
            'url' => $url,
        ];
    }

    /**
     * Check if the S3 object signature is allowed.
     *
     * @param  string  $key  The S3 object key.
     * @param  array<string, string>  $signatures  The signatures to check.
     * @return bool True if the S3 object signature is allowed, false otherwise.
     *
     * @throws AwsException If the request to S3 fails.
     */
    public function isAllowedFileSignature(string $key, array $signatures): bool
    {
        $bytes = $this->getObjectFirstBytes($key, 1024);
        $hex = bin2hex(substr($bytes, 0, 8));

        foreach ($signatures as $signature) {
            if (str_starts_with($hex, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the first bytes of the object.
     *
     * @param  string  $key  The S3 object key.
     * @param  int  $maxBytes  The maximum number of bytes to read. (default: 1024)
     *
     * @throws AwsException If the request to S3 fails.
     */
    private function getObjectFirstBytes(string $key, int $maxBytes = 1024): string
    {
        $result = $this->client->getObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'Range' => 'bytes=0-'.($maxBytes - 1),
        ]);

        return $result->get('Body');
    }

    /**
     * Create a multipart upload.
     *
     * @param  string  $key  The S3 object key.
     * @return array<string, string> The multipart upload ID and key.
     *
     * @throws AwsException If the request to S3 fails.
     */
    public function createMultipartUpload(string $key): array
    {
        $result = $this->client->createMultipartUpload([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $key,
        ]);

        return [
            'key' => $key,
            'upload_id' => $result->get('UploadId'),
        ];
    }

    /**
     * Get a presigned URL for a multipart upload part.
     *
     * @param  string  $key  The S3 object key.
     * @param  string  $uploadId  The multipart upload ID.
     * @param  int  $partNumber  The part number.
     * @param  int  $expiresIn  The number of minutes the URL will be valid. (default: 15)
     * @return array<string, string> The presigned URL, key, upload ID, and part number.
     */
    public function multipartPresignedUrl(string $key, string $uploadId, int $partNumber, $expiresIn = 15): array
    {
        $command = $this->client->getCommand('UploadPart', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
        ]);

        $result = $this->client->createPresignedRequest($command, now()->addMinutes($expiresIn));

        return [
            'key' => $key,
            'upload_id' => $uploadId,
            'part_number' => $partNumber,
            'url' => (string) $result->getUri(),
        ];
    }

    /**
     * Complete a multipart upload.
     *
     * @param  string  $key  The S3 object key.
     * @param  string  $uploadId  The multipart upload ID.
     *
     * @throws AwsException If the request to S3 fails.
     */
    public function completeMultipartUpload(string $key, string $uploadId, array $parts): void
    {
        $this->client->completeMultipartUpload([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'UploadId' => $uploadId,
            'MultipartUpload' => [
                'Parts' => $parts,
            ],
        ]);
    }

    /**
     * Abort a multipart upload.
     *
     * @param  string  $key  The S3 object key.
     * @param  string  $uploadId  The multipart upload ID.
     *
     * @throws AwsException If the request to S3 fails.
     */
    public function abortMultipartUpload(string $key, string $uploadId): void
    {
        $this->client->abortMultipartUpload([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'UploadId' => $uploadId,
        ]);
    }
}
