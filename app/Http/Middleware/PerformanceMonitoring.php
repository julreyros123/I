<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoring
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
        $memoryUsage = round(($endMemory - $startMemory) / 1024, 2); // Convert to KB

        // Log slow requests (> 500ms) or high memory usage (> 1MB)
        if ($executionTime > 500 || $memoryUsage > 1024) {
            Log::warning('Performance Alert', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time_ms' => $executionTime,
                'memory_usage_kb' => $memoryUsage,
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);
        }

        // Add performance headers for debugging
        $response->headers->set('X-Execution-Time', $executionTime . 'ms');
        $response->headers->set('X-Memory-Usage', $memoryUsage . 'KB');

        return $response;
    }
}