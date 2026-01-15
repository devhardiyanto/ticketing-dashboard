<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('system/ActivityLogIndex', []);
    }

    public function data(Request $request)
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

        $logs = $query->paginate($request->limit ?? 10);

        return response()->json($logs);
    }
}
