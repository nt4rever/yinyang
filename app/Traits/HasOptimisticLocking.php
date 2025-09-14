<?php

namespace App\Traits;

use App\Exceptions\OptimisticLockException;

trait HasOptimisticLocking
{
    /**
     * Ensure the lock version attribute exists.
     */
    protected function ensureLockVersionAttributeExists(): void
    {
        if (! $this->hasAttribute('lock_version')) {
            throw new \RuntimeException('Lock version attribute not found.');
        }
    }

    /**
     * Validate optimistic locking for the given data.
     *
     * @param  mixed  $data  The data containing the lock_version
     *
     * @throws OptimisticLockException
     */
    public function validateOptimisticLock(mixed $data): void
    {
        $this->ensureLockVersionAttributeExists();

        if (is_array($data)) {
            $expectedLockVersion = data_get($data, 'lock_version');
        } else {
            $expectedLockVersion = $data;
        }

        $expectedLockVersion = intval($expectedLockVersion);

        if ($this->lock_version !== $expectedLockVersion) {
            throw (new OptimisticLockException)->setModifiedBy($this->updatedBy?->name);
        }
    }

    /**
     * Increase the lock version if the model attributes have been modified.
     */
    public function increaseLockVersion(): void
    {
        $this->ensureLockVersionAttributeExists();

        if ($this->isDirty()) {
            $this->lock_version++;
        }
    }
}
