<?php

namespace App\Http\Middleware;

use App\Metricks\MetricsExporter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackHttpMetrics
{
    public function __construct(private MetricsExporter $exporter)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $start;

        if ($request->path() == "metrics") {
            return $response;
        }

        //если больше 500 то логи
        if ($duration > 500) {
            Log::channel('slowlog')->warning(
                "Slow request",
                [
                    'url'=>$request->fullUrl(),
                    'method'=>$request->method(),
                    'time_ms' => $duration * 1000,
                ]
            );
        }

        $method = $request->method();
        $path = $request->path();
        $status = (string)$response->getStatusCode();

        $this->exporter->incrementHttpRequest($method, $path, $status);
        $this->exporter->observeHttpDuration($method, $path, $duration);

        return $response;
    }
}
