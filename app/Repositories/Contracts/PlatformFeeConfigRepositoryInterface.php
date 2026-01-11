<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface PlatformFeeConfigRepositoryInterface
{
    /**
     * Get the current platform fee configuration (singleton)
     */
    public function getConfig(): ?Model;

    /**
     * Update or create the platform fee configuration
     */
    public function updateConfig(array $data): Model;
}
