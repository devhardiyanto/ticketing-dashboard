<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\PlatformFeeConfigRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlatformFeeConfigController extends Controller
{
    protected $repo;

    public function __construct(PlatformFeeConfigRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Display the platform fee configuration page
     */
    public function index()
    {
        $config = $this->repo->getConfig();

        return Inertia::render('platform_fee/PlatformFeeIndex', [
            'config' => $config ? [
                'id' => $config->id,
                'percentage_fee' => (float) $config->percentage_fee,
                'fixed_fee' => (float) $config->fixed_fee,
                'is_active' => $config->is_active,
                'updated_at' => $config->updated_at?->toISOString(),
            ] : null,
        ]);
    }

    /**
     * Update the platform fee configuration
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'percentage_fee' => 'required|numeric|min:0|max:100',
            'fixed_fee' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $this->repo->updateConfig($validated);

        return redirect()->back()->with('success', 'Platform fee configuration updated successfully.');
    }
}
