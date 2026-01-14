<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::latest()->with('causer');

        if ($request->search) {
            $query->where('description', 'ilike', "%{$request->search}%")
                  ->orWhere('log_name', 'ilike', "%{$request->search}%");
        }

        if ($request->event) {
             $query->where('event', $request->event);
        }

        if ($request->causer_id) {
             $query->where('causer_id', $request->causer_id);
        }

        $logs = $query->paginate($request->limit ?? 10)
                      ->appends($request->all());

        // Transform for frontend if needed, or rely on auto-serialization
        // Need to handle 'causer' serialization properly if it's not standard
        // Spatie activity has subject and causer morphs.

        return Inertia::render('system/ActivityLogIndex', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'limit', 'event', 'causer_id']),
        ]);
    }
}
