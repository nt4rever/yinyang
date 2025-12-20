<?php

namespace App\Services;

use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    /**
     * Generate a S3 pre-signed URL for temporary upload.
     *
     * @param  int  $expiresIn  The number of minutes the URL will be valid. (default: 5)
     * @return array<string, string> The S3 object key and the pre-signed URL.
     */
    public function generateTemporaryUploadUrl(int $expiresIn = 5): array
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
        $client = Storage::disk('s3')->getClient();

        $result = $client->getObject([
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'Range' => 'bytes=0-'.($maxBytes - 1),
        ]);

        return (string) $result['Body'];
    }
}
