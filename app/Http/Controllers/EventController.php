<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
		$params = $request->only(['search', 'limit', 'page']);
		$params['parent_id'] = $event_id ?? null;

		$event = null;
		if ($event_id) {
			$event = $this->event_repo->find($event_id);
		}

		$events = $this->event_repo->getAll($params);
		$organizations = $this->organization_repo->all();

		return Inertia::render('event/EventIndex', [
			'events' => $events,
			'parent_event' => $event,
			'organizations' => $organizations->select('id', 'name')->toArray(),
			'filters' => $request->only(['search', 'limit']),
		]);
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'name' => 'required|string|max:255',
			'description' => 'nullable|string',
			'start_date' => 'required|date',
			'end_date' => 'required|date|after:start_date',
			'timezone' => 'nullable|string|max:50',
			'location' => 'required|string',
			'address' => 'nullable|string',
			'status' => 'required|string|in:draft,published,archived',
			'currency' => 'required',
			'is_parent' => 'boolean',
			'organization_id' => 'required|exists:core_pgsql.organizations,id',
			'image_url' => 'nullable|image|max:5120', // Max 5MB
		]);

		if ($request->hasFile('image_url')) {
			$path = $request->file('image_url')->store('events', 'public');
			$validated['image_url'] = asset('storage/' . $path);
		}

		$this->event_repo->create($validated);

		return redirect()->back()->with('success', 'Event created successfully.');
	}

	public function update(Request $request, string $id)
	{
		$event = $this->event_repo->find($id);

		$rules = [
			'name' => 'required|string|max:255',
			'description' => 'nullable|string',
			'start_date' => 'required|date',
			'end_date' => 'required|date|after:start_date',
			'timezone' => 'nullable|string|max:50',
			'location' => 'required|string',
			'address' => 'nullable|string',
			'status' => 'required|string|in:draft,published,archived',
			'currency' => 'required',
			'is_parent' => 'boolean',
		];

		// Check if image_url is a file or a string (existing image)
		if ($request->hasFile('image_url')) {
			$rules['image_url'] = 'required|image|max:5120'; // Max 5MB
		} else {
			// If it's not a file, it must be a string (existing URL) and not null
			$rules['image_url'] = 'required|string';
		}

		$validated = $request->validate($rules);

		if ($request->hasFile('image_url')) {
			// Delete old image if exists
			if ($event && $event->image_url) {
				$oldPath = str_replace(asset('storage/'), '', $event->image_url);
				// Handle case where asset() might not include storage/ if configured differently,
				// but based on store method: asset('storage/' . $path)
				// So we remove the full prefix.
				// Safer way: parse_url or just check if it contains storage/
				// Let's assume standard storage link structure.
				// If $event->image_url is full URL, we need to extract relative path.
				// $path was stored as 'events/filename.ext'
				// $validated['image_url'] was asset('storage/events/filename.ext')

				// Simple extraction strategy:
				$relativePath = null;
				$pattern = '/storage\/(.*)$/';
				if (preg_match($pattern, $event->image_url, $matches)) {
					$relativePath = $matches[1];
				}

				if ($relativePath && Storage::disk('public')->exists($relativePath)) {
					Storage::disk('public')->delete($relativePath);
				}
			}

			$path = $request->file('image_url')->store('events', 'public');
			$validated['image_url'] = asset('storage/' . $path);
		}

		$this->event_repo->update($id, $validated);

		return redirect()->back()->with('success', 'Event updated successfully.');
	}

	public function destroy(string $id)
	{
		$this->event_repo->delete($id);
		return redirect()->back()->with('success', 'Event deleted successfully.');
	}
}
