<?php

declare(strict_types=1);

namespace Core\Contracts;

interface JobInterface
{
    /**
     * Execute the job.
     */
    public function handle(): void;
}
