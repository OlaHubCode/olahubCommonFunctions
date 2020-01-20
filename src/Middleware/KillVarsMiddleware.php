<?php

namespace OlaHub\Middlewares;

use Closure;

class KillVarsMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request 
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        gc_enable();
        $response = $next($request);
        gc_collect_cycles();
        if (!method_exists($response, 'render')) {
            return $response;
        }

        app('session')->flush();
        return $response->render();
    }

}
