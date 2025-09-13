<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptimisticLockException extends Exception
{
    /**
     * @var string|null
     */
    private $modifiedBy = null;

    public function setModifiedBy($modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        //
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        $message = filled($this->modifiedBy)
            ? trans('messages.optimistic_lock_warning', ['modified_by' => $this->modifiedBy])
            : trans('Conflict.');

        return response()->json([
            'message' => $message,
        ], 409);
    }
}
