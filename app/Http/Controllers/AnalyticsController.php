<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected $service;

    public function __construct(AnalyticsService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $availableEvents = $this->service->getAvailableEvents($user);
        
        // We will just pass available events. 
        // The frontend will determine the default selected ID and fetch data.
        
        return Inertia::render('analytics/AnalyticsIndex', [
            'events' => $availableEvents,
            // 'currentEventId' can be passed if we want to persist state via URL, but simpler to let frontend handle defaults or simple query param
            'currentEventId' => $request->input('event_id'), 
        ]);
    }

    public function data(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
        ]);

        $eventId = $request->input('event_id');
        $user = Auth::user();

        // Verify access
        $availableEvents = $this->service->getAvailableEvents($user);
        if (!$availableEvents->contains('id', $eventId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $analyticsData = $this->service->getAnalyticsData($eventId);

        return response()->json($analyticsData);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
        ]);

        $eventId = $request->input('event_id');
        $user = Auth::user();

        // Verify access
        $availableEvents = $this->service->getAvailableEvents($user);
        if (!$availableEvents->contains('id', $eventId)) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Force refresh
        $data = $this->service->getAnalyticsData($eventId, true);
        
        // Return fresh data directly so frontend doesn't need another call
        return response()->json($data);
    }
}
