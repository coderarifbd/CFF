<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:Admin|Super Admin']);
    }

    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest('created_at');
        // Filter by action: updated | deleted
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        // Date range filter (created_at)
        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->end);
        }

        $logs = $query->paginate(20)->withQueryString();
        return view('activity_logs.index', compact('logs'));
    }
}
