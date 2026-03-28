<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $totalLogs = ActivityLog::count();
        $initialLogs = ActivityLog::with('user')
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('pages.activity-log', compact('totalLogs', 'initialLogs'));
    }

    public function data(Request $request)
    {
        $columns = [
            'created_at',
            'user',
            'action',
            'route_name',
            'ip_address',
            'description',
        ];

        $query = ActivityLog::with('user');

        $recordsTotal = ActivityLog::count();

        if ($search = $request->input('search.value')) {
            $query->where(function ($query) use ($search) {
                $query->where('action', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $recordsFiltered = $query->count();

        $order = $request->input('order.0');
        if (is_array($order) && isset($columns[$order['column']])) {
            $column = $columns[$order['column']];
            $direction = $order['dir'] === 'asc' ? 'asc' : 'desc';

            if ($column === 'user') {
                $query->join('users', 'users.id', '=', 'activity_logs.user_id')
                    ->orderBy('users.name', $direction)
                    ->select('activity_logs.*');
            } else {
                $query->orderBy($column, $direction);
            }
        } else {
            $query->latest('created_at');
        }

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);

        $logs = $query->skip($start)->take($length)->get();

        $data = $logs->map(function (ActivityLog $log) {
            return [
                'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                'user' => $log->user?->name ?? __('System'),
                'action' => $log->action,
                'route_name' => $log->route_name ?: $log->url,
                'ip_address' => $log->ip_address,
                'description' => $log->description,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
