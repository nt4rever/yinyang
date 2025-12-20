<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToRetrieveMetadata;
use Throwable;

class UploadfileSize implements ValidationRule
{
    /**
     * The maximum size of the file in bytes.
     *
     * @var int (default: 10MB)
     */
    public function __construct(private int $maxSize = 1024 * 1024 * 10) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $result = Storage::size($value);
            if ($result > $this->maxSize) {
                $fail(trans('validation.max.file', ['max' => $this->maxSize]));
            }
        } catch (UnableToRetrieveMetadata $e) {
            $fail(trans('Resource not found.'));
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
