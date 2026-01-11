<?php

namespace App\Repositories\Eloquent;

use App\Models\Core\PlatformFeeConfig;
use App\Repositories\Contracts\PlatformFeeConfigRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class PlatformFeeConfigRepository implements PlatformFeeConfigRepositoryInterface
{
    protected $model;

    public function __construct(PlatformFeeConfig $model)
    {
        $this->model = $model;
    }

    /**
     * Get the current platform fee configuration (singleton)
     */
    public function getConfig(): ?Model
    {
        return $this->model->first();
    }

    /**
     * Update or create the platform fee configuration
     */
    public function updateConfig(array $data): Model
    {
        $config = $this->getConfig();

        if ($config) {
            $config->update($data);
            return $config->fresh();
        }

        return $this->model->create($data);
    }
}
