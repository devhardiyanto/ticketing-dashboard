<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\BannerRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BannerController extends Controller
{
    protected $banner_repo;

    protected $event_repo;

    public function toggle(Request $request, string $id)
    {
        $banner = $this->banner_repo->find($id);
        if (! $banner) {
            return redirect()->back()->with('error', 'Banner not found.');
        }

        $newStatus = $banner->status === 'active' ? 'inactive' : 'active';
        $this->banner_repo->update($id, ['status' => $newStatus]);

        return redirect()->back()->with('success', 'Banner status updated.');
    }

    public function __construct(BannerRepositoryInterface $banner_repo, EventRepositoryInterface $event_repo)
    {
        $this->banner_repo = $banner_repo;
        $this->event_repo = $event_repo;
    }

    public function index(Request $request)
    {
        $params = $request->only(['search', 'limit']);
        $banners = $this->banner_repo->getAll($params);

        // Generate signed URLs
        $banners->getCollection()->transform(function ($banner) {
            if ($banner->image) {
                $banner->image_signed_url = $this->getSignedUrl($banner->image);
            }

            return $banner;
        });

        $events = $this->event_repo->all()->select('id', 'name')->toArray();

        return Inertia::render('banner/BannerIndex', [
            'banners' => $banners,
            'events' => $events,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_id' => 'required|exists:core_pgsql.events,id',
            'status' => 'required|in:active,inactive',
            'image' => 'required|image|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = 'banners/'.$filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
            $validated['image'] = $path;
        }

        $this->banner_repo->create($validated);

        return redirect()->back()->with('success', 'Banner created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $banner = $this->banner_repo->find($id);

        $rules = [
            'title' => 'required|string|max:255',
            'event_id' => 'required|exists:core_pgsql.events,id',
            'status' => 'required|in:active,inactive',
        ];

        if ($request->hasFile('image')) {
            $rules['image'] = 'required|image|max:5120';
        } else {
            $rules['image'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner && $banner->image) {
                $this->deleteStorageFile($banner->image);
            }

            $file = $request->file('image');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = 'banners/'.$filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
            $validated['image'] = $path;
        } else {
            // Avoid overwriting existing image with null
            unset($validated['image']);
        }

        $this->banner_repo->update($id, $validated);

        return redirect()->back()->with('success', 'Banner updated successfully.');
    }

    public function destroy(string $id)
    {
        $banner = $this->banner_repo->find($id);

        if ($banner && $banner->image) {
            $this->deleteStorageFile($banner->image);
        }

        $this->banner_repo->delete($id);

        return redirect()->back()->with('success', 'Banner deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:core_pgsql.banners,id',
        ]);

        $this->banner_repo->reorder($request->input('ids'));

        return redirect()->back()->with('success', 'Banners reordered successfully.');
    }
}
