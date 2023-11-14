<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;

class ReactValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader('X-software-id')) {
            $react_data = Helpers::get_business_settings('react_setup');
            $react_status = isset($react_data['status']) ? $react_data['status'] : 0;
            $react_domain = isset($react_data['react_domain']) ? $react_data['react_domain'] : null;
            if ($react_status == 1 && $react_domain) {
                $url = str_ireplace('www.', '', parse_url(request()->headers->get('origin'), PHP_URL_HOST));
                if (str_ireplace('www.', '', parse_url($react_domain, PHP_URL_HOST)) == $url) {
                    return $next($request);
                }
            }

            return $next($request);

            // return response()->json([],404);
        }
        // continue request
        return $next($request);
    }
}
