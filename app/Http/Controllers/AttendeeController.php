<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string',
            'event_id' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $search = $validated['search'] ?? null;
        $eventId = $validated['event_id'] ?? null;
        $status = $validated['status'] ?? null;

        $query = \App\Models\Core\OrderItem::query()
            ->with(['order', 'item.event'])
            ->when($eventId, function ($q, $id) {
                $q->whereHas('item', function ($q) use ($id) {
                    $q->where('event_id', $id);
                });
            })
            ->when($status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->when($search, function ($q, $s) {
                $q->where(function ($q) use ($s) {
                    $q->where('attendee_name', 'ilike', "%{$s}%")
                        ->orWhere('ticket_code', 'ilike', "%{$s}%")
                        ->orWhereHas('order', function ($q) use ($s) {
                            $q->where('guest_name', 'ilike', "%{$s}%")
                                ->orWhere('guest_email', 'ilike', "%{$s}%");
                        });
                });
            })
            ->latest();

        $attendees = $query->paginate(20)->withQueryString();

        // Fetch active events for filter
        $events = \App\Models\Core\Event::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('attendees/Index', [
            'attendees' => $attendees,
            'events' => $events,
            'filters' => $request->only(['search', 'event_id', 'status']),
        ]);
    }

    public function checkIn(Request $request, string $id)
    {
        $item = \App\Models\Core\OrderItem::with('item')->findOrFail($id);

        $item->update(['status' => 'checkedin']);

        // Broadcast event
        $eventId = $item->item->event_id ?? null;

        if ($eventId) {
            event(new \App\Events\TicketScanned(
                eventId: $eventId,
                ticketCode: $item->ticket_code,
                status: 'success',
                attendeeName: $item->attendee_name,
                item: $item->item->title ?? 'Unknown',
                scannedAt: now()->toIso8601String(),
                reason: 'Manual Check-in'
            ));
        }

        return back()->with('success', 'Attendee checked in successfully.');
    }
}
