<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class UpdatePersonalAccessToken implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  string  $table
     * @param  string  $id
     * @param  array  $newAttributes
     */
    public function __construct(private $table, private $id, private $newAttributes) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::table($this->table)
            ->where('id', $this->id)
            ->update($this->newAttributes);
    }
}
