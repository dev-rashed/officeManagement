<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if (! $user) {
            return $response;
        }

        $route = $request->route();
        $routeName = $route?->getName();

        if (in_array($routeName, ['activity.log', 'activity.log.data'], true)) {
            return $response;
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'route_name' => $routeName,
            'action' => $request->method(),
            'description' => $routeName ? sprintf('Visited %s', $routeName) : sprintf('Visited %s', $request->path()),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $response;
    }
}
