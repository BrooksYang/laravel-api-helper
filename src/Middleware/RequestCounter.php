<?php

namespace BrooksYang\LaravelApiHelper\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class RequestCounter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $path = $request->path();

        $prefix = config('api-helper.cache_tag_prefix');
        
        Redis::incr("{$prefix}.request_counter.{$path}");

        return $next($request);
    }
}
