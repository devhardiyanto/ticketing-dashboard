<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EventController extends Controller
{
    protected $organization_repo;

    public function __construct(
        EventRepositoryInterface $event_repo,
        OrganizationRepositoryInterface $organization_repo
    ) {
        $this->organization_repo = $organization_repo;
        $this->event_repo = $event_repo;
    }

    public function index(Request $request, ?string $event_id = null)
    {
        $event = null;
        if ($event_id) {
            $event = $this->event_repo->find($event_id);
            // Generate signed URL for parent event image
            if ($event && $event->image_url) {
                $event->image_signed_url = $this->getSignedUrl($event->image_url);
            }
            if ($event && $event->venue_map_url) {
                $event->venue_map_signed_url = $this->getSignedUrl($event->venue_map_url);
            }
        }

        $organizations = $this->organization_repo->all();

        return Inertia::render('event/EventIndex', [
            'parent_event' => $event,
            'organizations' => $organizations->select('id', 'name')->toArray(),
        ]);
    }

    public function data(Request $request)
    {
        $params = $request->only(['search', 'limit', 'page', 'parent_id', 'sort', 'order']);

        $events = $this->event_repo->getAll($params);
        $this->transformEvents($events);

        return response()->json($events);
    }

    private function transformEvents($events)
    {
        $events->getCollection()->transform(function ($event) {
            if ($event->image_url) {
                $event->image_signed_url = $this->getSignedUrl($event->image_url);
            }
            if ($event->venue_map_url) {
                $event->venue_map_signed_url = $this->getSignedUrl($event->venue_map_url);
            }

            return $event;
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:core_pgsql.events,slug',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'timezone' => 'nullable|string|max:50',
            'location' => 'required|string',
            'address' => 'nullable|string',
            'venue_name' => 'nullable|string|max:255',
            'venue_city' => 'nullable|string|max:255',
            'status' => 'required|string|in:draft,published,archived',
            'currency' => 'required',
            'is_parent' => 'boolean',
            'organization_id' => 'required|exists:core_pgsql.organizations,id',
            'image_url' => 'nullable|image|max:5120',
            'banner_image_url' => 'nullable|string|max:500',
            'venue_map_url' => 'nullable|image|max:5120',
            'terms' => 'nullable|string',
        ]);

        if ($request->hasFile('image_url')) {
            // Upload to S3/MinIO and store path only
            $file = $request->file('image_url');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = 'events/'.$filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
            $validated['image_url'] = $path;
        }

        if ($request->hasFile('venue_map_url')) {
            $file = $request->file('venue_map_url');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = 'events/venue-maps/'.$filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
            $validated['venue_map_url'] = $path;
        }

        $this->event_repo->create($validated);

        return redirect()->back()->with('success', 'Event created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $event = $this->event_repo->find($id);

        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:core_pgsql.events,slug,'.$id.',id',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'timezone' => 'nullable|string|max:50',
            'location' => 'required|string',
            'address' => 'nullable|string',
            'venue_name' => 'nullable|string|max:255',
            'venue_city' => 'nullable|string|max:255',
            'status' => 'required|string|in:draft,published,archived',
            'currency' => 'required',
            'is_parent' => 'boolean',
            'banner_image_url' => 'nullable|string|max:500',
            'terms' => 'nullable|string',
        ];

        // Check if image_url is a file or a string (existing path)
        if ($request->hasFile('image_url')) {
            $rules['image_url'] = 'required|image|max:5120'; // Max 5MB
        } else {
            // If it's not a file, it must be a string (existing path)
            $rules['image_url'] = 'nullable|string';
        }

        // Check if venue_map_url is a file or a string (existing path)
        if ($request->hasFile('venue_map_url')) {
            $rules['venue_map_url'] = 'nullable|image|max:5120'; // Max 5MB
        } else {
            $rules['venue_map_url'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('image_url')) {
            // Delete old image from S3/MinIO
            if ($event && $event->image_url) {
                $this->deleteStorageFile($event->image_url);
            }

            // Upload new image to S3/MinIO
            $file = $request->file('image_url');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = 'events/'.$filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
            $validated['image_url'] = $path;
        }

        if ($request->hasFile('venue_map_url')) {
            // Delete old venue map from S3/MinIO
            if ($event && $event->venue_map_url) {
                $this->deleteStorageFile($event->venue_map_url);
            }

            // Upload new venue map to S3/MinIO
            $file = $request->file('venue_map_url');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = 'events/venue-maps/'.$filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
            $validated['venue_map_url'] = $path;
        }

        // Handle description image cleanup
        $this->cleanupDescriptionImages($event->description ?? '', $validated['description'] ?? '');

        $this->event_repo->update($id, $validated);

        return redirect()->back()->with('success', 'Event updated successfully.');
    }

    public function destroy(string $id)
    {
        $event = $this->event_repo->find($id);

        // Delete event image from storage
        if ($event && $event->image_url) {
            $this->deleteStorageFile($event->image_url);
        }

        // Delete venue map from storage
        if ($event && $event->venue_map_url) {
            $this->deleteStorageFile($event->venue_map_url);
        }

        // Delete description images from storage
        if ($event && $event->description) {
            $paths = UploadController::extractImagePathsFromHtml($event->description);
            UploadController::deleteFiles($paths);
        }

        $this->event_repo->delete($id);

        return redirect()->back()->with('success', 'Event deleted successfully.');
    }

    /**
     * Check if a slug is available
     */
    public function checkSlug(Request $request)
    {
        $slug = $request->query('slug');
        $excludeId = $request->query('exclude_id');

        if (! $slug) {
            return response()->json(['available' => false, 'message' => 'Slug is required']);
        }

        $query = $this->event_repo->findBySlug($slug, $excludeId);

        return response()->json([
            'available' => ! $query,
            'slug' => $slug,
        ]);
    }

    /**
     * Cleanup images removed from description
     */
    private function cleanupDescriptionImages(string $oldDescription, string $newDescription): void
    {
        $oldPaths = UploadController::extractImagePathsFromHtml($oldDescription);
        $newPaths = UploadController::extractImagePathsFromHtml($newDescription);

        // Find paths that were in old description but not in new
        $removedPaths = array_diff($oldPaths, $newPaths);

        // Delete removed images
        UploadController::deleteFiles($removedPaths);
    }
}
