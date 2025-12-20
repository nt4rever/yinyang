<?php

namespace App\Rules;

use App\Services\S3Service;
use Aws\Exception\AwsException;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UploadfileType implements ValidationRule
{
    private const SIGNATURES = [
        'jpg' => 'ffd8ff',
        'png' => '89504e470d0a1a0a',
        'pdf' => '25504446',
    ];

    /**
     * The allowed file types.
     *
     * @var array<string>
     */
    public function __construct(private array $allowedTypes = ['*']) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $signatures = $this->getSignatures($this->allowedTypes);
            $result = resolve(S3Service::class)->isAllowedFileSignature($value, $signatures);
            if (! $result) {
                $fail(trans('validation.mimes', ['values' => implode(', ', $this->allowedTypes)]));
            }
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                $fail(trans('Resource not found.'));
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get the signatures for the allowed types.
     *
     * @param  array<string>  $types  The allowed types.
     * @return array<string> The signatures.
     */
    private function getSignatures(array $types): array
    {
        if (empty($types) || $types[0] === '*') {
            return self::SIGNATURES;
        }

        return array_map(fn ($type) => self::SIGNATURES[$type], $this->allowedTypes);
    }
}
