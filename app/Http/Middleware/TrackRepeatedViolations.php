<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackRepeatedViolations
{
    /**
     * Progressive IP blocking based on repeated rate-limit violations.
     *
     * Violation thresholds (counted within a 24-hour window):
     *   >= 3 violations → block for 5 minutes
     *   >= 5 violations → block for 30 minutes
     *   >= 7 violations → block for 3 hours
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = (string) $request->ip();
        $blockKey = 'violations:block:' . $ip;
        $blockedUntil = Cache::get($blockKey);

        if ($blockedUntil !== null) {
            $retryAfter = max(0, (int) $blockedUntil - time());
            return response('Too many failed attempts. Please try again later.', 429)
                ->header('Retry-After', (string) $retryAfter)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $response = $next($request);

        if ($response->getStatusCode() === 429) {
            $countKey = 'violations:count:' . $ip;
            $count = (int) Cache::get($countKey, 0) + 1;
            Cache::put($countKey, $count, now()->addHours(24));

            if ($count >= 7) {
                $blockSeconds = 3 * 3600;       // 3 hours
            } elseif ($count >= 5) {
                $blockSeconds = 30 * 60;         // 30 minutes
            } elseif ($count >= 3) {
                $blockSeconds = 5 * 60;          // 5 minutes
            } else {
                $blockSeconds = 0;
            }

            if ($blockSeconds > 0) {
                Cache::put($blockKey, time() + $blockSeconds, now()->addSeconds($blockSeconds));
                return response('Too many failed attempts. Please try again later.', 429)
                    ->header('Retry-After', (string) $blockSeconds)
                    ->header('Content-Type', 'text/plain; charset=UTF-8');
            }
        }

        return $response;
    }
}
