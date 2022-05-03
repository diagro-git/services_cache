<?php
namespace App\Http\Middleware;

use Closure;
use Diagro\Token\BackendApplicationToken;
use Exception;
use Illuminate\Http\Request;

/**
 * Checks if the X-BACKEND-TOKEN is precense and if it decodes.
 *
 * @package Diagro\Backend\Middleware
 */
class BackendAuthentication
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
        abort_if(! $request->hasHeader('x-backend-token'), 404, "No backend token found!");

        try {
            BackendApplicationToken::createFromToken($request->header('x-backend-token'));
        } catch(Exception $e) {
            abort(403, "Access denied for given backend token!");
        }

        return $next($request);
    }


}
