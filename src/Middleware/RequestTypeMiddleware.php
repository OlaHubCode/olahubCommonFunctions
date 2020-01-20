<?php

namespace OlaHub\Middlewares;

use Closure;

class RequestTypeMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request 
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (strtoupper($request->method()) == 'OPTIONS' || $request->header('x-requested-with') || preg_match("~\b/images/\b~",\Illuminate\Support\Facades\URL::current())) {
            return $next($request);
        } 
        abort(404);
    }

}
